<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/anketa/common.php';

if (!function_exists('anketa_generate_pdf')) {
    function anketa_generate_pdf(string $hash): string {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}anketa WHERE hash = %s", $hash),
            ARRAY_A
        );
        if (!$row) {
            throw new \RuntimeException("Анкета не найдена: $hash");
        }

        $sections  = anketa_get_sections();
        $generated = wp_date('d.m.Y H:i');

        // HTML шаблон
        $html  = '<style>';
        $html .= 'body { font-family: dejavusans, sans-serif; font-size: 11pt; color: #222; }';
        $html .= 'h1 { font-size: 15pt; margin: 0 0 4px; }';
        $html .= '.meta { font-size: 9pt; color: #666; margin-bottom: 16px; }';
        $html .= 'h2 { font-size: 11pt; background: #e8edf3; padding: 4px 8px; margin: 14px 0 4px; border-left: 3px solid #5580a0; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }';
        $html .= 'td { padding: 3px 6px; border: 1px solid #ddd; vertical-align: top; font-size: 10pt; }';
        $html .= 'td.lbl { width: 45%; background: #f8f8f8; color: #555; }';
        $html .= 'td.empty { background: #fff6f6; color: #b30000; font-style: italic; }';
        $html .= '</style>';

        $html .= '<h1>Анкета молодожёнов</h1>';
        $html .= '<p class="meta">Hash: ' . esc_html($hash) . ' &nbsp;|&nbsp; Сформировано: ' . esc_html($generated) . '</p>';

        foreach ($sections as $section_title => $fields) {
            $html .= '<h2>' . esc_html($section_title) . '</h2>';
            $html .= '<table>';
            foreach ($fields as $key => $label) {
                $val      = isset($row[$key]) ? trim($row[$key]) : '';
                $val_html = $val !== ''
                    ? nl2br(htmlspecialchars($val, ENT_QUOTES))
                    : '<td class="empty">Не заполнено</td>';
                $html .= '<tr>';
                $html .= '<td class="lbl">' . htmlspecialchars($label, ENT_QUOTES) . '</td>';
                if ($val !== '') {
                    $html .= '<td>' . $val_html . '</td>';
                } else {
                    $html .= '<td class="empty">Не заполнено</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        $docs_dir  = anketa_docs_dir($hash);
        $out_path  = $docs_dir . '/anketa.pdf';

        // Временная папка для mPDF (за пределами webroot)
        $tmp_dir = $docs_dir . '/mpdf_tmp';
        if (!is_dir($tmp_dir)) {
            wp_mkdir_p($tmp_dir);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'margin_left'   => 15,
            'margin_right'  => 15,
            'tempDir'       => $tmp_dir,
            'default_font'  => 'dejavusans',
        ]);

        $mpdf->SetTitle('Анкета молодожёнов');
        $mpdf->WriteHTML($html);

        if (!is_dir($docs_dir)) {
            wp_mkdir_p($docs_dir);
        }

        $mpdf->Output($out_path, \Mpdf\Output\Destination::FILE);

        return $out_path;
    }
}
