# BeautifulWedding Theme README

Консолидированная документация по ключевым частям темы: мега-меню, логика пакетов и коэффициенты ценообразования.

---
## 1. Мега-меню (navigation/)
**Файлы:**
- `navigation/main-menu.php` – конфиг и рендер меню
- `navigation/mega-menu.css` – стили
- `navigation/mega-menu.js` – интерактивность (зависит от jQuery)

**Подключение ресурсов:** реализовано в `functions.php` (версионирование через `filemtime`).

**Вставка в шапку:**
```php
get_template_part('navigation/main-menu');
```

**Настройка пунктов:** редактируйте массив `$MENU_ITEMS` в `main-menu.php`. Типы:
- `link` – обычная ссылка
- `svadba_places` – выпадающий список мест (таксономия `places`)

**Минимально важные моменты:**
- Проверяйте slug терминов таксономии `places` (например: `praga`, `zamki-chehii`, `brno`, `karlovy-vary`).
- Метаполя поста `svadba`: `fromnew`, `capacity`, `zaly_mesta` (repeater).
- Экранирование используется повсеместно (`esc_html`, `esc_url`, `esc_attr`).
- При большом количестве мест можно добавить transient‑кэш в рендерер.

---
## 2. Логика фиксированных пакетов (`svadba/packets.php`)
Шорткод: `[svadba_packets]` – генерирует таблицу услуг по пакетам для текущего места.

**Расчёт:**
- `distance` (минимум `BW_MIN_DISTANCE`) определяет базовые часы авто.
- Для Праги (`distance == 2`): часы авто = `distance + packet_index + 1`.
- Базовый вычет авто: `base_auto_price * distance * BW_AUTO_DEDUCTION_COEF` (округление к шагу `BW_ROUND_STEP`).
- Фото: доплата за дорогу один раз на пакет при `distance > BW_MIN_DISTANCE`.
- Итог: `base_place_price + round((services_sum * BW_PACKET_DISCOUNT_COEF)/BW_ROUND_STEP)*BW_ROUND_STEP`.

**Доплата за дорогу:** `(distance - BW_MIN_DISTANCE) * BW_TRAVEL_RATE_PHOTO_VIDEO`.

**Отображение цен в ячейках:** активируется параметром `?show_packet_prices=1`.

---
## 3. Коэффициенты и ценообразование
Определены централизованно в `inc/services-config.php`.

| Константа | Значение | Назначение |
|----------|----------|------------|
| `BW_AUTO_DEDUCTION_COEF` | `0.7` | Вычет базового авто |
| `BW_PACKET_DISCOUNT_COEF` | `0.8` | Скидка на услуги пакета (20%) |
| `BW_TRAVEL_RATE_PHOTO_VIDEO` | `50` | Доплата за дорогу фото/видео за единицу дистанции |
| `BW_MIN_DISTANCE` | `2` | Минимальная дистанция |
| `BW_ROUND_STEP` | `10` | Шаг округления |

**Массив для JS:**
```php
$bw_pricing = [
	'auto_deduction_coef' => BW_AUTO_DEDUCTION_COEF,
	'packet_discount_coef' => BW_PACKET_DISCOUNT_COEF,
	'travel_rate_photo_video' => BW_TRAVEL_RATE_PHOTO_VIDEO,
	'min_distance' => BW_MIN_DISTANCE,
	'round_step' => BW_ROUND_STEP,
];
```
Локализация:
```php
wp_localize_script('svadba-form-script', 'bwPricing', $bw_pricing);
```

**Использование в JS:**
```js
var PHOTOGRAPHER_TRAVEL_RATE = bwPricing.travel_rate_photo_video;
var AUTO_DEDUCTION_COEF = bwPricing.auto_deduction_coef;
var MIN_DISTANCE = bwPricing.min_distance;
var ROUND_STEP = bwPricing.round_step;
```

---
## 4. Сервисные пресеты и шорткод
`[bw_services preset="..."]` использует пресеты из `inc/services-config.php`: `photo-video`, `auto`, `hair-makeup`, `flowers`, `cakes`, `dresses`, `other`.

Одиночная секция:
```text
[bw_services keys="photo" title="Фотосъёмка свадебной церемонии, прогулки по Праге"]
```

Табы на странице прайс-листа получают конфиг из `$bw_tabs_config` того же файла.

---
## 5. Чеклист после изменения коэффициентов
1. Измените значение в `services-config.php`.
2. Перезагрузите страницу калькулятора и прайс-лист.
3. Проверьте расчёт для дистанции = 2 и > 2.
4. Сравните PHP и JS результаты.
5. Обновите README при необходимости.

---
## 6. Даты
- Консолидация README: 2025-11-29
- Добавление коэффициентов: 2025-11-29

---
## 7. Расширение
- Для сезонных акций можно завести альтернативный массив коэффициентов.
- A/B‑тест: локализовать второй объект `bwPricingAlt` и переключать в JS.

---
**Конец документа.**