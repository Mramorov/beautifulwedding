# Руководство по кастомизации главной страницы

## Быстрый старт

### 1. Добавление фонового изображения в Hero Section

**Шаг 1:** Загрузите изображение
- Разместите изображение в папку `img/` темы
- Рекомендуемый размер: минимум 1920x1080px
- Формат: JPG (оптимизированный для веба)

**Шаг 2:** Отредактируйте CSS
Откройте `assets/css/front-page.css` и найдите секцию Hero Section (строка ~13):

```css
.hero-section {
  /* Раскомментируйте эти строки: */
  background-image: url('../img/hero-bg.jpg');
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
}
```

**Шаг 3:** Активируйте overlay
В файле `front-page.php` найдите hero-section и добавьте класс:
```php
<section class="hero-section full with-background">
```

### 2. Изменение текста и кнопок Hero Section

В файле `front-page.php` найдите:

```php
<h1 class="hero-title">Beautiful Wedding</h1>
<p class="hero-subtitle">Организация свадеб в Чехии</p>
<p class="hero-description">Создадим для вас незабываемую свадьбу в самом сердце Европы</p>
```

Измените текст на свой.

### 3. Изменение цветов кнопок

В `assets/css/front-page.css` найдите секцию Buttons:

```css
.btn-primary {
  background-color: var(--color-primary); /* Цвет фона */
  color: var(--color-white);             /* Цвет текста */
  border-color: var(--color-primary);    /* Цвет границы */
}
```

Можете использовать кастомные цвета:
```css
.btn-primary {
  background-color: #e91e63; /* Ярко-розовый */
  color: #ffffff;
  border-color: #e91e63;
}
```

### 4. Изменение количества отображаемых элементов

**Услуги:** В `front-page.php` найдите:
```php
$services_query = new WP_Query(array(
  'post_type' => 'service',
  'posts_per_page' => 6, // Измените на нужное число
  ...
));
```

**Свадьбы:** Аналогично:
```php
$weddings_query = new WP_Query(array(
  'post_type' => 'svadba',
  'posts_per_page' => 3, // Измените на нужное число
  ...
));
```

### 5. Добавление новой секции

**Пример: секция отзывов**

В `front-page.php` добавьте после Why Us Section:

```php
<!-- Testimonials Section -->
<section class="testimonials-section boxed">
  <h2>Отзывы клиентов</h2>
  <div class="testimonials-grid">
    <div class="testimonial-item">
      <p class="testimonial-text">"Отличная организация! Всё прошло идеально."</p>
      <p class="testimonial-author">— Анна и Иван</p>
    </div>
    <div class="testimonial-item">
      <p class="testimonial-text">"Профессиональный подход. Рекомендуем!"</p>
      <p class="testimonial-author">— Мария и Петр</p>
    </div>
  </div>
</section>
```

В `assets/css/front-page.css` добавьте:

```css
.testimonials-section {
  background-color: var(--color-bg-alt);
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin: 3rem 0;
}

.testimonial-item {
  background: var(--color-white);
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.testimonial-text {
  font-size: 1.125rem;
  font-style: italic;
  color: var(--color-text);
  margin-bottom: 1rem;
}

.testimonial-author {
  font-weight: 600;
  color: var(--color-primary);
}
```

### 6. Изменение сетки карточек

По умолчанию используется автоматическая сетка. Для фиксированных колонок:

**Для 3 колонок:**
```css
.services-grid {
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}
```

**Для 2 колонок:**
```css
.services-grid {
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
}
```

### 7. Добавление иконок Font Awesome

**Шаг 1:** Подключите Font Awesome в `functions.php`:
```php
wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
```

**Шаг 2:** Замените символы в features на иконки:
```php
<div class="feature-icon"><i class="fas fa-check-circle"></i></div>
<div class="feature-icon"><i class="fas fa-heart"></i></div>
<div class="feature-icon"><i class="fas fa-star"></i></div>
<div class="feature-icon"><i class="fas fa-concierge-bell"></i></div>
```

### 8. Изменение адаптивных breakpoints

В `assets/css/front-page.css` найдите секцию Responsive Design:

```css
@media (max-width: 768px) {
  /* Стили для планшетов */
}

@media (max-width: 480px) {
  /* Стили для мобильных */
}
```

Можете добавить дополнительные:
```css
@media (max-width: 1024px) {
  /* Стили для маленьких ноутбуков */
}
```

## Расширенные настройки

### Анимации при скролле

Добавьте в `functions.php`:
```php
if (is_front_page()) {
  wp_enqueue_script('aos', 'https://unpkg.com/aos@2.3.1/dist/aos.js', array(), '2.3.1', true);
  wp_enqueue_style('aos', 'https://unpkg.com/aos@2.3.1/dist/aos.css');
}
```

В `front-page.php` добавьте атрибуты:
```php
<div class="service-card" data-aos="fade-up" data-aos-delay="100">
```

### Слайдер в Hero Section

Рекомендуем использовать Swiper.js:
```php
wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js');
wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
```

### Ленивая загрузка изображений

WordPress 5.5+ поддерживает это автоматически через `the_post_thumbnail()`.

Для дополнительного контроля используйте:
```php
<?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
```

## Советы по производительности

1. **Оптимизируйте изображения:**
   - Используйте WebP формат
   - Сжимайте через TinyPNG или аналоги
   - Используйте правильные размеры миниатюр WordPress

2. **Кэширование:**
   - Установите плагин кэширования (WP Rocket, W3 Total Cache)
   - Настройте CDN для статических файлов

3. **Минификация:**
   - Используйте плагин для минификации CSS/JS
   - Объедините файлы стилей где возможно

4. **Lazy Loading:**
   - Включен по умолчанию для изображений в WordPress
   - Рассмотрите ленивую загрузку для видео

## Troubleshooting

**Проблема:** Стили не применяются
- Очистите кэш браузера (Ctrl+Shift+R)
- Очистите кэш WordPress плагина
- Проверьте путь к CSS файлу в functions.php

**Проблема:** Не отображаются услуги/свадьбы
- Убедитесь, что созданы записи типа 'service' и 'svadba'
- Проверьте что записи опубликованы (не в черновиках)
- Проверьте наличие миниатюр у записей

**Проблема:** Мобильная версия выглядит неправильно
- Проверьте медиа-запросы в CSS
- Убедитесь что в header.php есть мета-тег viewport

## Интеграция с плагинами

### Contact Form 7
Замените секцию contact на шорткод формы:
```php
<?php echo do_shortcode('[contact-form-7 id="123"]'); ?>
```

### Elementor
Можете создать секции через Elementor и вставить через shortcode:
```php
<?php echo do_shortcode('[elementor-template id="456"]'); ?>
```

### Yoast SEO
Плагин автоматически работает с шаблоном. Для дополнительной настройки добавьте:
```php
<?php
if (function_exists('yoast_breadcrumb')) {
  yoast_breadcrumb('<p id="breadcrumbs">','</p>');
}
?>
```

## Дополнительные ресурсы

- [WordPress Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/)
- [WP_Query Documentation](https://developer.wordpress.org/reference/classes/wp_query/)
- [CSS Grid Guide](https://css-tricks.com/snippets/css/complete-guide-grid/)
- [Responsive Web Design](https://web.dev/responsive-web-design-basics/)
