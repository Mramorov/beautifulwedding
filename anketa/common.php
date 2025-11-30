<?php

/**
 * Shared helpers for Anketa features
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('anketa_get_labels')) {
    /**
     * Return shared labels used across anketa form and email.
     *
     * @return array key => human-readable label
     */
    function anketa_get_labels()
    {
        $labels = array(
            // Данные жениха
            'groom_full_name' => 'Имя и фамилия по загранпаспорту',
            'groom_birth_surname' => 'Фамилия при рождении',
            'groom_passport' => 'Номер загранпаспорта',
            'groom_birthdate' => 'Дата рождения',
            'groom_birthplace' => 'Место (село/город), страна рождения',
            'groom_citizenship' => 'Какие гражданства имеются',
            'groom_marital_status' => 'Семейное положение',
            'groom_address' => 'Адрес регистрации',
            'groom_education' => 'Полученное образование',
            
            // Родители жениха - отец
            'groom_father_name' => 'Имя Отчество Фамилия',
            'groom_father_birth_surname' => 'Фамилия отца при рождении',
            'groom_father_birthdate' => 'Дата рождения отца',
            'groom_father_birthplace' => 'Место (село/город), страна рождения',
            
            // Родители жениха - мать
            'groom_mother_name' => 'Имя Отчество Фамилия',
            'groom_mother_birth_surname' => 'Девичья фамилия матери',
            'groom_mother_birthdate' => 'Дата рождения матери',
            'groom_mother_birthplace' => 'Место (село/город), страна рождения',
            
            // Данные невесты
            'bride_full_name' => 'Имя и фамилия по загранпаспорту',
            'bride_birth_surname' => 'Фамилия при рождении',
            'bride_passport' => 'Номер загранпаспорта',
            'bride_birthdate' => 'Дата рождения',
            'bride_birthplace' => 'Место (село/город), страна рождения',
            'bride_citizenship' => 'Какие гражданства имеются',
            'bride_marital_status' => 'Семейное положение',
            'bride_address' => 'Адрес регистрации',
            'bride_education' => 'Полученное образование',
            
            // Родители невесты - отец
            'bride_father_name' => 'Имя Отчество Фамилия',
            'bride_father_birth_surname' => 'Фамилия отца при рождении',
            'bride_father_birthdate' => 'Дата рождения отца',
            'bride_father_birthplace' => 'Место (село/город), страна рождения',
            
            // Родители невесты - мать
            'bride_mother_name' => 'Имя Отчество Фамилия',
            'bride_mother_birth_surname' => 'Девичья фамилия матери',
            'bride_mother_birthdate' => 'Дата рождения матери',
            'bride_mother_birthplace' => 'Место (село/город), страна рождения',
            
            // Договоренности
            'surname_choice' => 'После бракосочетания будут использовать фамилии:',
            'wedding_location' => 'Предпочтительное место регистрации, пожелания:',
            'translation_language' => 'Язык перевода церемонии',
            'contact_email' => 'Email для связи',
            'contact_tel' => 'Телефон для связи',
            'certificate_address' => 'Адрес для отправки свидетельства о браке',
        );

        return apply_filters('anketa_labels', $labels);
    }
}

if (!function_exists('anketa_get_label')) {
    /**
     * Get a single label by key.
     *
     * @param string $key Field name
     * @return string Label or key if not found
     */
    function anketa_get_label($key)
    {
        $labels = anketa_get_labels();
        return isset($labels[$key]) ? $labels[$key] : $key;
    }
}
