<?php

/**
 * Template Name: Anketa Form Page
 * Template Post Type: page
 */

// Include common labels
require_once get_template_directory() . '/anketa/common.php';

$_anketa_hash           = isset($_GET['hash']) ? sanitize_text_field($_GET['hash']) : '';
$_anketa_existing_files = anketa_get_existing_files($_anketa_hash);

if (!function_exists('_anketa_render_doc_field')) {
    function _anketa_render_doc_field($field, $label, $existing_files, $note = '')
    {
        $has = isset($existing_files[$field]);
?>
        <div class="form-group doc-upload-group" data-doc-field="<?= esc_attr($field) ?>" data-doc-label="<?= esc_attr($label) ?>">
            <label><?= esc_html($label) ?></label>
            <?php if ($note): ?><span class="doc-field-note"><?= esc_html($note) ?></span><?php endif; ?>
            <?php if ($has): ?>
                <div class="doc-exists-state">
                    <span class="doc-filename">загружен</span>
                    <button type="button" class="doc-delete-btn"
                        data-field="<?= esc_attr($field) ?>"
                        data-label="<?= esc_attr($label) ?>">Удалить</button>
                </div>
                <div class="doc-upload-state" style="display:none;">
                    <p class="doc-pending-msg">
                        <span class="doc-pending-text">Файл <strong><?= esc_html($label) ?></strong> будет удалён или заменён на новый после нажатия кнопки «Сохранить изменения»</span>
                    </p>
                    <input type="file" name="file_<?= esc_attr($field) ?>" accept=".pdf,.jpg,.jpeg,.png">
                    <input type="hidden" name="delete_files[<?= esc_attr($field) ?>]" value="1" class="doc-delete-flag">
                </div>
            <?php else: ?>
                <div class="doc-upload-state">
                    <input type="file" name="file_<?= esc_attr($field) ?>" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            <?php endif; ?>
        </div>
<?php
    }
}

// Enqueue the form styles and scripts
function enqueue_marriage_form_assets()
{
    $css_file    = get_stylesheet_directory() . '/anketa/marriage-form.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : '1.0.0';
    wp_enqueue_style('marriage-form-styles', get_stylesheet_directory_uri() . '/anketa/marriage-form.css', array(), $css_version);

    $js_file    = get_stylesheet_directory() . '/anketa/marriage-form.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : '1.0.0';
    wp_enqueue_script('marriage-form-script', get_stylesheet_directory_uri() . '/anketa/marriage-form.js', array(), $js_version, true);

    wp_localize_script('marriage-form-script', 'anketaParams', array(
        'restUrl'     => esc_url_raw(rest_url('anketa/v1/submit')),
        'nonce'       => wp_create_nonce('wp_rest'),
        'currentHash' => isset($_GET['hash']) ? sanitize_text_field($_GET['hash']) : '',
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_marriage_form_assets');

get_header('anketa');
?>

<main id="primary" class="site-main">
    <div class="marriage-form-container">
        <h1>Анкета</h1>

        <form id="marriageApplicationForm" class="marriage-form">
            <div class="anketa-grid">

                <!-- 1. Информация -->
                <div class="anketa-block anketa-block--info">
                    <p>Если у вас нет возможности заполнить анкету полностью, под рукой отсутствуют необходимые данные или документы, либо у вас возникли вопросы, вы можете заполнить её частично и отправить. Все внесённые данные будут сохранены, и вы сможете вернуться к заполнению анкеты позднее.</p>

                    <p>Для сохранения анкеты в системе достаточно заполнить обязательные поля, отмеченные символом «*», то есть указать хотя бы один способ связи с вами и имя и фамилию жениха.</p>

                    <p>После нажатия кнопки «Отослать анкету» вы получите уникальную ссылку для последующего просмотра и редактирования предоставленных данных. Пожалуйста, сохраните эту ссылку и не передавайте её третьим лицам, поскольку она предоставляет доступ к информации, содержащейся в анкете.</p>

                    <p>Отправляя анкету, вы подтверждаете достоверность указанных сведений и выражаете согласие на обработку предоставленных персональных данных в объёме, необходимом для подготовки и организации вашей свадьбы, а также для дальнейшей коммуникации по вашему запросу.</p>

                </div>

                <!-- 2. Контактные данные -->
                <div class="anketa-block anketa-block--contacts">
                    <h2>Контактные данные</h2>
                    <div class="contact-fields-row">
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
                </div>

                <!-- 3. Жених -->
                <div class="anketa-block anketa-block--groom">
                    <h2>Жених</h2>
                    <div class="form-group">
                        <label for="groom_full_name"><?php echo esc_html(anketa_get_label('groom_full_name')); ?> *</label>
                        <input type="text" id="groom_full_name" name="groom_full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="groom_birth_surname"><?php echo esc_html(anketa_get_label('groom_birth_surname')); ?></label>
                        <input type="text" id="groom_birth_surname" name="groom_birth_surname">
                    </div>
                    <div class="form-group">
                        <label for="groom_passport"><?php echo esc_html(anketa_get_label('groom_passport')); ?></label>
                        <input type="text" id="groom_passport" name="groom_passport">
                    </div>
                    <div class="form-group">
                        <label for="groom_birthdate"><?php echo esc_html(anketa_get_label('groom_birthdate')); ?></label>
                        <input type="date" id="groom_birthdate" name="groom_birthdate">
                    </div>
                    <div class="form-group">
                        <label for="groom_birthplace"><?php echo esc_html(anketa_get_label('groom_birthplace')); ?></label>
                        <input type="text" id="groom_birthplace" name="groom_birthplace">
                    </div>
                    <div class="form-group">
                        <label for="groom_citizenship"><?php echo esc_html(anketa_get_label('groom_citizenship')); ?></label>
                        <input type="text" id="groom_citizenship" name="groom_citizenship">
                    </div>
                    <div class="form-group">
                        <label for="groom_marital_status"><?php echo esc_html(anketa_get_label('groom_marital_status')); ?></label>
                        <select id="groom_marital_status" name="groom_marital_status">
                            <option value="">Выберите...</option>
                            <option value="Не был женат">Не был женат</option>
                            <option value="Разведен">Разведен</option>
                            <option value="Вдовец">Вдовец</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="groom_address"><?php echo esc_html(anketa_get_label('groom_address')); ?></label>
                        <input type="text" id="groom_address" name="groom_address">
                    </div>
                    <div class="form-group">
                        <label for="groom_education"><?php echo esc_html(anketa_get_label('groom_education')); ?></label>
                        <input type="text" id="groom_education" name="groom_education">
                    </div>
                </div>

                <!-- 4. Родители жениха -->
                <div class="anketa-block anketa-block--groom-parents">
                    <h2>Родители жениха</h2>
                    <h3>Отец</h3>
                    <div class="form-group">
                        <label for="groom_father_name"><?php echo esc_html(anketa_get_label('groom_father_name')); ?></label>
                        <input type="text" id="groom_father_name" name="groom_father_name">
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
                    <h3>Мать</h3>
                    <div class="form-group">
                        <label for="groom_mother_name"><?php echo esc_html(anketa_get_label('groom_mother_name')); ?></label>
                        <input type="text" id="groom_mother_name" name="groom_mother_name">
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

                <!-- 5. Невеста -->
                <div class="anketa-block anketa-block--bride">
                    <h2>Невеста</h2>
                    <div class="form-group">
                        <label for="bride_full_name"><?php echo esc_html(anketa_get_label('bride_full_name')); ?></label>
                        <input type="text" id="bride_full_name" name="bride_full_name">
                    </div>
                    <div class="form-group">
                        <label for="bride_birth_surname"><?php echo esc_html(anketa_get_label('bride_birth_surname')); ?></label>
                        <input type="text" id="bride_birth_surname" name="bride_birth_surname">
                    </div>
                    <div class="form-group">
                        <label for="bride_passport"><?php echo esc_html(anketa_get_label('bride_passport')); ?></label>
                        <input type="text" id="bride_passport" name="bride_passport">
                    </div>
                    <div class="form-group">
                        <label for="bride_birthdate"><?php echo esc_html(anketa_get_label('bride_birthdate')); ?></label>
                        <input type="date" id="bride_birthdate" name="bride_birthdate">
                    </div>
                    <div class="form-group">
                        <label for="bride_birthplace"><?php echo esc_html(anketa_get_label('bride_birthplace')); ?></label>
                        <input type="text" id="bride_birthplace" name="bride_birthplace">
                    </div>
                    <div class="form-group">
                        <label for="bride_citizenship"><?php echo esc_html(anketa_get_label('bride_citizenship')); ?></label>
                        <input type="text" id="bride_citizenship" name="bride_citizenship">
                    </div>
                    <div class="form-group">
                        <label for="bride_marital_status"><?php echo esc_html(anketa_get_label('bride_marital_status')); ?></label>
                        <select id="bride_marital_status" name="bride_marital_status">
                            <option value="">Выберите...</option>
                            <option value="Не была замужем">Не была замужем</option>
                            <option value="Разведена">Разведена</option>
                            <option value="Вдова">Вдова</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bride_address"><?php echo esc_html(anketa_get_label('bride_address')); ?></label>
                        <input type="text" id="bride_address" name="bride_address">
                    </div>
                    <div class="form-group">
                        <label for="bride_education"><?php echo esc_html(anketa_get_label('bride_education')); ?></label>
                        <input type="text" id="bride_education" name="bride_education">
                    </div>
                </div>

                <!-- 6. Родители невесты -->
                <div class="anketa-block anketa-block--bride-parents">
                    <h2>Родители невесты</h2>
                    <h3>Отец</h3>
                    <div class="form-group">
                        <label for="bride_father_name"><?php echo esc_html(anketa_get_label('bride_father_name')); ?></label>
                        <input type="text" id="bride_father_name" name="bride_father_name">
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
                    <h3>Мать</h3>
                    <div class="form-group">
                        <label for="bride_mother_name"><?php echo esc_html(anketa_get_label('bride_mother_name')); ?></label>
                        <input type="text" id="bride_mother_name" name="bride_mother_name">
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

                <!-- 7. Договорённости -->
                <div class="anketa-block anketa-block--agreements">
                    <h2>Брачующиеся договорились</h2>
                    <div class="form-group">
                        <label for="surname_choice"><?php echo esc_html(anketa_get_label('surname_choice')); ?></label>
                        <select id="surname_choice" name="surname_choice">
                            <option value="">Выберите...</option>
                            <option value="Невеста берёт фамилию жениха">Невеста берёт фамилию жениха</option>
                            <option value="Жених берёт фамилию невесты">Жених берёт фамилию невесты</option>
                            <option value="Оба оставляют свои фамилии">Оба оставляют свои фамилии</option>
                            <option value="Жених не меняет, невеста - двойную (на первом месте фамилия жениха)">Жених не меняет, невеста — двойную (на первом месте фамилия жениха)</option>
                            <option value="Невеста не меняет, жених - двойную (на первом месте фамилия невесты)">Невеста не меняет, жених — двойную (на первом месте фамилия невесты)</option>
                        </select>
                        <p class="small-prim"><strong>Примечание:</strong> При выборе двойной фамилии собственная фамилия супруга/супруги всегда указывается на втором месте. Двойную фамилию может принять только один из супругов, чтобы избежать зеркальных комбинаций (например, «Петров-Иванов» и «Иванова-Петрова»).</p>
                    </div>

                    <h2>Заключить брак хотели бы</h2>
                    <div class="form-group">
                        <label for="wedding_location"><?php echo esc_html(anketa_get_label('wedding_location')); ?></label>
                        <input type="text" id="wedding_location" name="wedding_location" placeholder="Например: Ратуша г. Прага, замок, ЗАГС">
                    </div>
                    <div class="form-group">
                        <label for="translation_language"><?php echo esc_html(anketa_get_label('translation_language')); ?></label>
                        <select id="translation_language" name="translation_language">
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
                        <input type="text" id="certificate_address" name="certificate_address">
                    </div>
                </div>

                <!-- 8. Документы -->
                <div class="anketa-block anketa-block--docs">
                    <h2>Документы</h2>
                    <p class="doc-section-note">Загрузите сканы или фото документов в формате PDF, JPG или PNG (не более 5 МБ каждый). Документы можно добавить сейчас или после первой отправки анкеты, вернувшись по ссылке.</p>
                    <h3>Жених</h3>
                    <?php _anketa_render_doc_field('PZ', 'Паспорт жениха', $_anketa_existing_files); ?>
                    <?php _anketa_render_doc_field('SZ', 'Свидетельство о рождении жениха', $_anketa_existing_files); ?>
                    <?php _anketa_render_doc_field('RZ', 'Свидетельство о разводе жениха', $_anketa_existing_files, 'Только для разведённых и вдовцов'); ?>
                    <h3>Невеста</h3>
                    <?php _anketa_render_doc_field('PN', 'Паспорт невесты', $_anketa_existing_files); ?>
                    <?php _anketa_render_doc_field('SN', 'Свидетельство о рождении невесты', $_anketa_existing_files); ?>
                    <?php _anketa_render_doc_field('RN', 'Свидетельство о разводе невесты', $_anketa_existing_files, 'Только для разведённых и вдов'); ?>
                </div>

            </div><!-- .anketa-grid -->

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
