<?php

/**
 * Shared helpers for Anketa features
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('anketa_get_sections')) {
    function anketa_get_sections() {
        return [
            'Контактные данные' => [
                'contact_email' => 'Email для связи',
                'contact_tel'   => 'Телефон для связи',
            ],
            'Жених' => [
                'groom_full_name'      => 'Имя и фамилия по загранпаспорту',
                'groom_birth_surname'  => 'Фамилия при рождении',
                'groom_passport'       => 'Номер загранпаспорта',
                'groom_birthdate'      => 'Дата рождения',
                'groom_birthplace'     => 'Место (село/город), страна рождения',
                'groom_citizenship'    => 'Какие гражданства имеются',
                'groom_marital_status' => 'Семейное положение',
                'groom_address'        => 'Адрес регистрации',
                'groom_education'      => 'Полученное образование',
            ],
            'Родители жениха — отец' => [
                'groom_father_name'          => 'Имя Отчество Фамилия',
                'groom_father_birth_surname' => 'Фамилия отца при рождении',
                'groom_father_birthdate'     => 'Дата рождения отца',
                'groom_father_birthplace'    => 'Место (село/город), страна рождения',
            ],
            'Родители жениха — мать' => [
                'groom_mother_name'          => 'Имя Отчество Фамилия',
                'groom_mother_birth_surname' => 'Девичья фамилия матери',
                'groom_mother_birthdate'     => 'Дата рождения матери',
                'groom_mother_birthplace'    => 'Место (село/город), страна рождения',
            ],
            'Невеста' => [
                'bride_full_name'      => 'Имя и фамилия по загранпаспорту',
                'bride_birth_surname'  => 'Фамилия при рождении',
                'bride_passport'       => 'Номер загранпаспорта',
                'bride_birthdate'      => 'Дата рождения',
                'bride_birthplace'     => 'Место (село/город), страна рождения',
                'bride_citizenship'    => 'Какие гражданства имеются',
                'bride_marital_status' => 'Семейное положение',
                'bride_address'        => 'Адрес регистрации',
                'bride_education'      => 'Полученное образование',
            ],
            'Родители невесты — отец' => [
                'bride_father_name'          => 'Имя Отчество Фамилия',
                'bride_father_birth_surname' => 'Фамилия отца при рождении',
                'bride_father_birthdate'     => 'Дата рождения отца',
                'bride_father_birthplace'    => 'Место (село/город), страна рождения',
            ],
            'Родители невесты — мать' => [
                'bride_mother_name'          => 'Имя Отчество Фамилия',
                'bride_mother_birth_surname' => 'Девичья фамилия матери',
                'bride_mother_birthdate'     => 'Дата рождения матери',
                'bride_mother_birthplace'    => 'Место (село/город), страна рождения',
            ],
            'Договорённости' => [
                'surname_choice'       => 'После бракосочетания будут использовать фамилии:',
                'wedding_location'     => 'Предпочтительное место регистрации, пожелания:',
                'translation_language' => 'Язык перевода церемонии',
                'certificate_address'  => 'Адрес для отправки свидетельства о браке',
            ],
        ];
    }
}

if (!function_exists('anketa_get_labels')) {
    function anketa_get_labels() {
        return array_merge(...array_values(anketa_get_sections()));
    }
}

if (!function_exists('anketa_get_label')) {
    function anketa_get_label(string $key): string {
        foreach (anketa_get_sections() as $fields) {
            if (isset($fields[$key])) return $fields[$key];
        }
        return $key;
    }
}

if (!function_exists('anketa_get_file_fields')) {
    function anketa_get_file_fields() {
        return [
            'PZ' => 'Паспорт жениха',
            'PN' => 'Паспорт невесты',
            'SZ' => 'Свидетельство о рождении жениха',
            'SN' => 'Свидетельство о рождении невесты',
            'RZ' => 'Свидетельство о разводе жениха',
            'RN' => 'Свидетельство о разводе невесты',
        ];
    }
}

if (!function_exists('anketa_docs_dir')) {
    function anketa_docs_dir($hash = '') {
        $base = dirname(ABSPATH) . '/wp_svadba_docs';
        return $hash ? $base . '/' . $hash : $base;
    }
}

if (!function_exists('anketa_get_existing_files')) {
    function anketa_get_existing_files($hash) {
        if (!$hash) return [];
        $dir = anketa_docs_dir($hash);
        if (!is_dir($dir)) return [];
        $result = [];
        foreach (glob($dir . '/*') as $path) {
            if (is_file($path)) {
                $result[pathinfo($path, PATHINFO_FILENAME)] = pathinfo($path, PATHINFO_EXTENSION);
            }
        }
        return $result;
    }
}
