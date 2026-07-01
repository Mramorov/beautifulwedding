<?php
if (!defined('ABSPATH')) exit;

// =============================================================================
// OneDrive / Microsoft Graph — конфигурация.
// Реальные значения хранятся в onedrive-config.local.php (не в git).
// Скопируй onedrive-config.local.php.example → onedrive-config.local.php и заполни.
// =============================================================================
$_onedrive_config = __DIR__ . '/onedrive-config.local.php';
if ( file_exists( $_onedrive_config ) ) {
    require_once $_onedrive_config;
}
// =============================================================================

function anketa_onedrive_redirect_uri(): string {
    return ONEDRIVE_REDIRECT_URI;
}

// REST callback: принимает code от Microsoft, сохраняет токены, редиректит в админку
add_action('rest_api_init', function() {
    register_rest_route('anketa/v1', '/onedrive-callback', [
        'methods'             => 'GET',
        'callback'            => function(WP_REST_Request $request) {
            $code  = sanitize_text_field($request->get_param('code') ?? '');
            $error = sanitize_text_field($request->get_param('error_description') ?? $request->get_param('error') ?? '');
            $admin = admin_url('admin.php?page=anketa-list');

            if ($error) {
                wp_redirect(add_query_arg('od_error', urlencode($error), $admin));
                exit;
            }

            if ($code) {
                $result = anketa_onedrive_handle_auth_code($code);
                if (is_wp_error($result)) {
                    wp_redirect(add_query_arg('od_error', urlencode($result->get_error_message()), $admin));
                    exit;
                }
            }

            wp_redirect(add_query_arg('od_connected', '1', $admin));
            exit;
        },
        'permission_callback' => '__return_true',
    ]);
});

/**
 * URL для первичной авторизации (открыть в браузере один раз).
 */
function anketa_onedrive_get_auth_url(): string {
    $params = http_build_query([
        'client_id'     => ONEDRIVE_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri'  => anketa_onedrive_redirect_uri(),
        'scope'         => 'https://graph.microsoft.com/Files.ReadWrite offline_access',
        'response_mode' => 'query',
    ]);
    return 'https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize?' . $params;
}

/**
 * Обменять code (из ?code=…) на токены и сохранить в wp_options.
 * Возвращает true или WP_Error.
 */
function anketa_onedrive_handle_auth_code(string $code): bool|WP_Error {
    $response = wp_remote_post(
        'https://login.microsoftonline.com/consumers/oauth2/v2.0/token',
        [
            'body' => [
                'client_id'     => ONEDRIVE_CLIENT_ID,
                'client_secret' => ONEDRIVE_CLIENT_SECRET,
                'code'          => $code,
                'redirect_uri'  => anketa_onedrive_redirect_uri(),
                'grant_type'    => 'authorization_code',
            ],
            'timeout' => 15,
        ]
    );

    if (is_wp_error($response)) return $response;

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['access_token'])) {
        $msg = $body['error_description'] ?? 'Нет access_token в ответе';
        return new WP_Error('onedrive_auth', $msg);
    }

    update_option('anketa_onedrive_tokens', [
        'access_token'  => $body['access_token'],
        'refresh_token' => $body['refresh_token'],
        'expires_at'    => time() + (int)($body['expires_in'] ?? 3600) - 60,
    ]);
    return true;
}

/**
 * Вернуть действующий access_token, обновив через refresh_token если истёк.
 */
function anketa_onedrive_get_access_token(): string|WP_Error {
    $tokens = get_option('anketa_onedrive_tokens');
    if (empty($tokens['refresh_token'])) {
        return new WP_Error('onedrive_no_token', 'OneDrive не авторизован. Нажмите «Подключить OneDrive».');
    }

    if (!empty($tokens['access_token']) && time() < ($tokens['expires_at'] ?? 0)) {
        return $tokens['access_token'];
    }

    // Рефреш
    $response = wp_remote_post(
        'https://login.microsoftonline.com/consumers/oauth2/v2.0/token',
        [
            'body' => [
                'client_id'     => ONEDRIVE_CLIENT_ID,
                'client_secret' => ONEDRIVE_CLIENT_SECRET,
                'refresh_token' => $tokens['refresh_token'],
                'grant_type'    => 'refresh_token',
                'scope'         => 'https://graph.microsoft.com/Files.ReadWrite offline_access',
            ],
            'timeout' => 15,
        ]
    );

    if (is_wp_error($response)) return $response;

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['access_token'])) {
        delete_option('anketa_onedrive_tokens');
        $msg = $body['error_description'] ?? 'Ошибка обновления токена';
        return new WP_Error('onedrive_refresh', $msg);
    }

    update_option('anketa_onedrive_tokens', [
        'access_token'  => $body['access_token'],
        'refresh_token' => $body['refresh_token'] ?? $tokens['refresh_token'],
        'expires_at'    => time() + (int)($body['expires_in'] ?? 3600) - 60,
    ]);
    return $body['access_token'];
}

/**
 * Загрузить локальный файл в OneDrive по пути относительно корня диска.
 * $remote_path — например "SvadbaDocs/30.01-10.30-P22/anketa.pdf"
 * Промежуточные папки создаются Graph API автоматически.
 */
function anketa_onedrive_upload_file(string $remote_path, string $local_path): bool|WP_Error {
    $token = anketa_onedrive_get_access_token();
    if (is_wp_error($token)) return $token;

    $content = file_get_contents($local_path);
    if ($content === false) {
        return new WP_Error('onedrive_read', "Не удалось прочитать файл: $local_path");
    }

    $url = 'https://graph.microsoft.com/v1.0/me/drive/root:/' . rawurlencode($remote_path) . ':/content';
    // rawurlencode кодирует /, восстановим их
    $url = str_replace('%2F', '/', $url);

    $response = wp_remote_request($url, [
        'method'  => 'PUT',
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => mime_content_type($local_path) ?: 'application/octet-stream',
        ],
        'body'    => $content,
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) return $response;

    $code = wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        $body = wp_remote_retrieve_body($response);
        $err  = json_decode($body, true);
        $msg  = $err['error']['message'] ?? "HTTP $code";
        return new WP_Error('onedrive_upload', "Ошибка загрузки $remote_path: $msg");
    }

    return true;
}
