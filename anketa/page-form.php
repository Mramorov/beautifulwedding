<?php

/**
 * Template Name: Anketa Form Page
 * Template Post Type: page
 */

// Include common labels
require_once get_template_directory() . '/anketa/common.php';

// Enqueue the form styles and scripts
function enqueue_marriage_form_assets()
{
    // CSS
    $css_file = get_stylesheet_directory() . '/anketa/marriage-form.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : '1.0.0';
    wp_enqueue_style('marriage-form-styles', get_stylesheet_directory_uri() . '/anketa/marriage-form.css', array(), $css_version);

    // JavaScript
    $js_file = get_stylesheet_directory() . '/anketa/marriage-form.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : '1.0.0';
    wp_enqueue_script('marriage-form-script', get_stylesheet_directory_uri() . '/anketa/marriage-form.js', array(), $js_version, true);

    // Localize script parameters
    wp_localize_script('marriage-form-script', 'anketaParams', array(
        'restUrl' => esc_url_raw(rest_url('anketa/v1/submit')),
        'nonce' => wp_create_nonce('wp_rest'),
        'currentHash' => isset($_GET['hash']) ? sanitize_text_field($_GET['hash']) : '',
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_marriage_form_assets');

get_header('anketa');
?>

<main id="primary" class="site-main">
    <div class="marriage-form-container">
        <h1>Анкета молодожёнов</h1>

        <form id="marriageApplicationForm" class="marriage-form">
            <div class="info-message">
                <p>Если у вас нет возможности заполнить анкету полностью, под рукой отсутствуют необходимые данные или документы, или у вас возникли вопросы, вы можете заполнить её частично, отправить (сохранить) и вернуться к заполнению позже.</p>
                <p>Для того чтобы анкета сохранилась в системе, вам достаточно заполнить обязательные поля* — хотя бы одно поле для связи с вами, а также Имя и Фамилию жениха.</p>
                <p>После отправки анкеты вы получите уникальную ссылку для редактирования ваших данных в будущем. Пожалуйста, сохраните эту ссылку исключительно для личного пользования.</p>
            </div>
            <div class="form-group contact-fields-row">
                <div class="contact-field">
                    <label for="contact_email"><?php echo esc_html(anketa_get_label('contact_email')); ?></label>
                    <input type="email" id="contact_email" name="contact_email">
                </div>
                <div class="contact-field">
                    <label for="contact_tel"><?php echo esc_html(anketa_get_label('contact_tel')); ?></label>
                    <input type="tel" id="contact_tel" name="contact_tel">
                </div>
            </div>
            <p class="contact-note">* Укажите хотя бы один способ связи (email или телефон)</p>
            <!-- Жених -->
            <h2>Жених</h2>

            <div class="form-section">
                <div class="form-group">
                    <label for="groom_full_name"><?php echo esc_html(anketa_get_label('groom_full_name')); ?> *</label>
                    <input type="text" id="groom_full_name" name="groom_full_name" required>
                </div>
                <div class="form-group">
                    <label for="groom_birth_surname"><?php echo esc_html(anketa_get_label('groom_birth_surname')); ?> </label>
                    <input type="text" id="groom_birth_surname" name="groom_birth_surname">
                </div>
                <div class="form-group">
                    <label for="groom_passport"><?php echo esc_html(anketa_get_label('groom_passport')); ?></label>
                    <input type="text" id="groom_passport" name="groom_passport">
                </div>
                <div class="form-group">
                    <label for="groom_birthdate"><?php echo esc_html(anketa_get_label('groom_birthdate')); ?></label>
                    <input type="date" id="groom_birthdate" name="groom_birthdate" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="groom_birthplace"><?php echo esc_html(anketa_get_label('groom_birthplace')); ?></label>
                    <input type="text" id="groom_birthplace" name="groom_birthplace" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="groom_citizenship"><?php echo esc_html(anketa_get_label('groom_citizenship')); ?></label>
                    <input type="text" id="groom_citizenship" name="groom_citizenship" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="groom_marital_status"><?php echo esc_html(anketa_get_label('groom_marital_status')); ?></label>
                    <select id="groom_marital_status" name="groom_marital_status" data-was-required="1">
                        <option value="">Выберите...</option>
                        <option value="Не был женат">Не был женат</option>
                        <option value="Разведен">Разведен</option>
                        <option value="Вдовец">Вдовец</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="groom_address"><?php echo esc_html(anketa_get_label('groom_address')); ?></label>
                    <input type="text" id="groom_address" name="groom_address" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="groom_education"><?php echo esc_html(anketa_get_label('groom_education')); ?></label>
                    <input type="text" id="groom_education" name="groom_education">
                </div>
            </div>

            <!-- Родители жениха -->
            <h3>Родители жениха</h3>
            <div class="form-section">
                <h4>Отец</h4>
                <div class="form-group">
                    <label for="groom_father_name"><?php echo esc_html(anketa_get_label('groom_father_name')); ?></label>
                    <input type="text" id="groom_father_name" name="groom_father_name" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="groom_father_birth_surname"><?php echo esc_html(anketa_get_label('groom_father_birth_surname')); ?></label>
                    <input type="text" id="groom_father_birth_surname" name="groom_father_birth_surname">
                </div>
                <div class="form-group">
                    <label for="groom_father_birthdate"><?php echo esc_html(anketa_get_label('groom_father_birthdate')); ?></label>
                    <input type="date" id="groom_father_birthdate" name="groom_father_birthdate">
                </div>
                <div class="form-group">
                    <label for="groom_father_birthplace"><?php echo esc_html(anketa_get_label('groom_father_birthplace')); ?></label>
                    <input type="text" id="groom_father_birthplace" name="groom_father_birthplace">
                </div>

                <h4>Мать</h4>
                <div class="form-group">
                    <label for="groom_mother_name"><?php echo esc_html(anketa_get_label('groom_mother_name')); ?></label>
                    <input type="text" id="groom_mother_name" name="groom_mother_name" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="groom_mother_birth_surname"><?php echo esc_html(anketa_get_label('groom_mother_birth_surname')); ?></label>
                    <input type="text" id="groom_mother_birth_surname" name="groom_mother_birth_surname">
                </div>
                <div class="form-group">
                    <label for="groom_mother_birthdate"><?php echo esc_html(anketa_get_label('groom_mother_birthdate')); ?></label>
                    <input type="date" id="groom_mother_birthdate" name="groom_mother_birthdate">
                </div>
                <div class="form-group">
                    <label for="groom_mother_birthplace"><?php echo esc_html(anketa_get_label('groom_mother_birthplace')); ?></label>
                    <input type="text" id="groom_mother_birthplace" name="groom_mother_birthplace">
                </div>
            </div>

            <!-- Невеста -->
            <h2>Невеста</h2>
            <div class="form-section">
                <div class="form-group">
                    <label for="bride_full_name"><?php echo esc_html(anketa_get_label('bride_full_name')); ?></label>
                    <input type="text" id="bride_full_name" name="bride_full_name" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_birth_surname"><?php echo esc_html(anketa_get_label('bride_birth_surname')); ?></label>
                    <input type="text" id="bride_birth_surname" name="bride_birth_surname">
                </div>
                <div class="form-group">
                    <label for="bride_passport"><?php echo esc_html(anketa_get_label('bride_passport')); ?></label>
                    <input type="text" id="bride_passport" name="bride_passport" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_birthdate"><?php echo esc_html(anketa_get_label('bride_birthdate')); ?></label>
                    <input type="date" id="bride_birthdate" name="bride_birthdate" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_birthplace"><?php echo esc_html(anketa_get_label('bride_birthplace')); ?></label>
                    <input type="text" id="bride_birthplace" name="bride_birthplace" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_citizenship"><?php echo esc_html(anketa_get_label('bride_citizenship')); ?></label>
                    <input type="text" id="bride_citizenship" name="bride_citizenship" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_marital_status"><?php echo esc_html(anketa_get_label('bride_marital_status')); ?></label>
                    <select id="bride_marital_status" name="bride_marital_status" data-was-required="1">
                        <option value="">Выберите...</option>
                        <option value="Не была замужем">Не была замужем</option>
                        <option value="Разведена">Разведена</option>
                        <option value="Вдова">Вдова</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bride_address"><?php echo esc_html(anketa_get_label('bride_address')); ?></label>
                    <input type="text" id="bride_address" name="bride_address" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_education"><?php echo esc_html(anketa_get_label('bride_education')); ?></label>
                    <input type="text" id="bride_education" name="bride_education">
                </div>
            </div>

            <!-- Родители невесты -->
            <h3>Родители невесты</h3>
            <div class="form-section">
                <h4>Отец</h4>
                <div class="form-group">
                    <label for="bride_father_name"><?php echo esc_html(anketa_get_label('bride_father_name')); ?></label>
                    <input type="text" id="bride_father_name" name="bride_father_name" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_father_birth_surname"><?php echo esc_html(anketa_get_label('bride_father_birth_surname')); ?></label>
                    <input type="text" id="bride_father_birth_surname" name="bride_father_birth_surname">
                </div>
                <div class="form-group">
                    <label for="bride_father_birthdate"><?php echo esc_html(anketa_get_label('bride_father_birthdate')); ?></label>
                    <input type="date" id="bride_father_birthdate" name="bride_father_birthdate">
                </div>
                <div class="form-group">
                    <label for="bride_father_birthplace"><?php echo esc_html(anketa_get_label('bride_father_birthplace')); ?></label>
                    <input type="text" id="bride_father_birthplace" name="bride_father_birthplace">
                </div>

                <h4>Мать</h4>
                <div class="form-group">
                    <label for="bride_mother_name"><?php echo esc_html(anketa_get_label('bride_mother_name')); ?></label>
                    <input type="text" id="bride_mother_name" name="bride_mother_name" data-was-required="1">
                </div>
                <div class="form-group">
                    <label for="bride_mother_birth_surname"><?php echo esc_html(anketa_get_label('bride_mother_birth_surname')); ?></label>
                    <input type="text" id="bride_mother_birth_surname" name="bride_mother_birth_surname">
                </div>
                <div class="form-group">
                    <label for="bride_mother_birthdate"><?php echo esc_html(anketa_get_label('bride_mother_birthdate')); ?></label>
                    <input type="date" id="bride_mother_birthdate" name="bride_mother_birthdate">
                </div>
                <div class="form-group">
                    <label for="bride_mother_birthplace"><?php echo esc_html(anketa_get_label('bride_mother_birthplace')); ?></label>
                    <input type="text" id="bride_mother_birthplace" name="bride_mother_birthplace">
                </div>
            </div>

            <!-- Брачующиеся договорились -->
            <h2>Брачующиеся договорились</h2>
            <div class="form-section">
                <div class="form-group">
                    <label for="surname_choice"><?php echo esc_html(anketa_get_label('surname_choice')); ?></label>
                    <select id="surname_choice" name="surname_choice" data-was-required="1">
                        <option value="">Выберите...</option>
                        <option value="Невеста берёт фамилию жениха">Невеста берёт фамилию жениха</option>
                        <option value="Жених берёт фамилию невесты">Жених берёт фамилию невесты</option>
                        <option value="Оба оставляют свои фамилии">Оба оставляют свои фамилии</option>
                        <option value="Жених не меняет, невеста - двойную (на первом месте фамилия жениха)">Жених не меняет, невеста - двойную (на первом месте фамилия жениха) </option>
                        <option value="Невеста не меняет, жених - двойную (на первом месте фамилия невесты)">Невеста не меняет, жених - двойную (на первом месте фамилия невесты)</option>
                    </select>
                    <p class="small-prim">
                        <strong>Примечание:</strong> При выборе двойной фамилии собственная фамилия супруга/супруги всегда указывается на втором месте. Двойную фамилию может принять только один из супругов, чтобы избежать зеркальных комбинаций (например, «Петров-Иванов» и «Иванова-Петрова»).
                    </p>
                </div>
            </div>

            <!-- Заключить брак хотели бы -->
            <h2>Заключить брак хотели бы</h2>
            <div class="form-section">
                <div class="form-group">
                    <label for="wedding_location"><?php echo esc_html(anketa_get_label('wedding_location')); ?></label>
                    <input type="text" id="wedding_location" name="wedding_location" data-was-required="1" placeholder="Например: Ратуша г. Прага, замок, ЗАГС">
                </div>
                <div class="form-group">
                    <label for="translation_language"><?php echo esc_html(anketa_get_label('translation_language')); ?></label>
                    <select id="translation_language" name="translation_language" data-was-required="1">
                        <option value="">Выберите...</option>
                        <option value="Русский">Русский</option>
                        <option value="Украинский">Украинский</option>
                        <option value="Иврит">Иврит</option>
                        <option value="Английский">Английский</option>
                        <option value="Другой">Другой (будет оговорён отдельно)</option>
                    </select>
                    <p class="small-prim"><strong>Примечание:</strong> Язык перевода церемонии должен соответствовать стране рождения или гражданству каждого из вступающих в брак. Если у молодожёнов отсутствует общий язык, удовлетворяющий этим требованиям, необходимо присутствие двух переводчиков. Свидетели также должны соответствовать указанным требованиям в части языка перевода церемонии. Проведение церемонии на чешском языке без перевода возможно только в случае, если оба вступающих в брак, а также оба свидетеля, имеют гражданство Чешской Республики либо подтверждённое постоянное место жительства на территории Чехии. Рождённые в СССР могут выбрать русский язык.</p>
                </div>
                <div class="form-group">
                    <label for="certificate_address"><?php echo esc_html(anketa_get_label('certificate_address')); ?></label>
                    <input type="text" id="certificate_address" name="certificate_address" data-was-required="1">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-button">
                    <span class="btn-text">Отослать анкету</span>
                </button>
            </div>
        </form>

        <!-- Success Message (hidden by default) -->
        <div id="anketaSuccessMessage" class="anketa-success-message" style="display: none;">
            <div class="success-icon">✅</div>
            <h2>Анкета успешно отправлена!</h2>
            <p>Ваши данные сохранены.<br>Если потребуется внести изменения, используйте эту ссылку:</p>
            <div class="edit-link-box">
                <a href="#" id="editLinkAnchor" class="edit-link-anchor" target="_blank" rel="noopener"></a>
                <button type="button" id="copyLinkBtn" class="copy-link-btn">Скопировать</button>
            </div>
            <p class="success-note">💡 Сохраните эту ссылку — она понадобится для редактирования анкеты в будущем</p>
        </div>
    </div>
</main>

<?php get_footer();
