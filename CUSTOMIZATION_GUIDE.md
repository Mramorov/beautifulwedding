# Gallery Layout Toggle Memo

Current state (May 2026):
- Active layout: old masonry-style CSS layout.
- Justified Gallery files are kept in theme, but not connected.

## Files kept for future Justified Gallery
- `assets/js/vendor/jquery.justifiedGallery.min.js`
- `assets/css/vendor/justifiedGallery.min.css`
- `assets/js/justified-gallery-init.js`

## How to re-enable Justified Gallery

1. In `functions.php`, inside `beautifulwedding_enqueue_lightbox_assets()`, add these enqueues (still under the `svadba/service` condition):

```php
$jg_css_file = get_template_directory() . '/assets/css/vendor/justifiedGallery.min.css';
$jg_js_file = get_template_directory() . '/assets/js/vendor/jquery.justifiedGallery.min.js';
$jg_init_file = get_template_directory() . '/assets/js/justified-gallery-init.js';

wp_enqueue_style(
	'justified-gallery',
	get_template_directory_uri() . '/assets/css/vendor/justifiedGallery.min.css',
	array(),
	file_exists( $jg_css_file ) ? filemtime( $jg_css_file ) : wp_get_theme()->get( 'Version' )
);

wp_enqueue_script(
	'justified-gallery',
	get_template_directory_uri() . '/assets/js/vendor/jquery.justifiedGallery.min.js',
	array( 'jquery' ),
	file_exists( $jg_js_file ) ? filemtime( $jg_js_file ) : wp_get_theme()->get( 'Version' ),
	true
);

wp_enqueue_script(
	'beautifulwedding-justified-gallery-init',
	get_template_directory_uri() . '/assets/js/justified-gallery-init.js',
	array( 'jquery', 'justified-gallery' ),
	file_exists( $jg_init_file ) ? filemtime( $jg_init_file ) : wp_get_theme()->get( 'Version' ),
	true
);
```

2. In `style.css`, disable old gallery layout rules to avoid conflict with Justified Gallery:
- `.svadba-gallery-grid` block near the gallery section.
- media rules for `.svadba-gallery-grid` near `@media (max-width: 900px)` and `@media (max-width: 600px)`.

3. Clear cache (plugin/server/CDN/browser) and hard refresh page.

## How to switch back to old layout
- Remove or comment out the three Justified Gallery enqueues in `functions.php`.
- Re-enable the old `.svadba-gallery-grid` rules in `style.css`.
