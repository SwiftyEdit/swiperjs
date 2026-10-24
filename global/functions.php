<?php
/**
 * Core helpers for the swiperjs plugin. Three groups of functions live here:
 *
 * 1. Settings (sjs_get_settings/sjs_save_setting/sjs_get_default_settings/
 *    sjs_updateOrCreateTable) - same shape as plugins/tracker's tr_*()
 *    equivalents.
 * 2. Content resolution (sjs_source_types/sjs_search_source/
 *    sjs_get_source_record/sjs_extract_image_url/sjs_*_link) - reads
 *    snippets/pages/posts/products from core, normalizing all four into one
 *    ['title','teaser','image','link'] shape a slide can render.
 * 3. Rendering (sjs_render_slideshow/sjs_render_slide_card - frontend;
 *    sjs_render_slide_row - admin canvas row).
 */

/* -------------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------------- */

function sjs_get_settings(): array {
    global $swiperjs_db;

    if (!isset($swiperjs_db)) {
        return [];
    }
    $result = $swiperjs_db->select('settings', ['key', 'value']);
    return array_column($result, 'value', 'key');
}

function sjs_save_setting(string $key, $value): bool {
    global $swiperjs_db;

    $exists = $swiperjs_db->has('settings', ['key' => $key]);

    if ($exists) {
        $result = $swiperjs_db->update('settings', [
            'value' => (string) $value
        ], ['key' => $key]);
        return $result->rowCount() > 0;
    }

    return (bool) $swiperjs_db->insert('settings', [
        'key' => $key,
        'value' => (string) $value
    ]);
}

function sjs_get_default_settings(): array {
    return [
        // Off by default: a fresh install can't assume the active theme
        // already loads Swiper, so the plugin serves its own vendored
        // bundle until an admin confirms the theme already provides it
        // (Addons > Swiper Slider > Einstellungen).
        'theme_provides_swiper' => 0,
        // Off by default, same polarity as theme_provides_swiper (checked =
        // "the theme brings it, plugin stays out"): the plugin's own
        // slide-layout CSS (sjs_layout_css()) is needed for any of the
        // layouts to look right, unless the theme styles the .swiperjs-*
        // classes itself.
        'theme_provides_layout_css' => 0,
    ];
}

/**
 * Ensure table exists and matches current schema.
 */
function sjs_updateOrCreateTable(string $table_name, array $expected_columns): void {
    global $swiperjs_db;

    $tables = $swiperjs_db->query("
    SELECT name FROM sqlite_master
    WHERE type='table' AND name = '$table_name' ")->fetchAll();

    if (empty($tables)) {
        $col_definitions = [];
        foreach ($expected_columns as $col_name => $col_type) {
            $col_definitions[] = "$col_name $col_type";
        }
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (" . implode(', ', $col_definitions) . ")";
        $swiperjs_db->query($sql);
        echo "Created table $table_name<br>";
        return;
    }

    $tableInfo = $swiperjs_db->query("PRAGMA table_info($table_name)")->fetchAll();
    $existing_columns = array_column($tableInfo, 'name');

    foreach ($expected_columns as $col_name => $col_type) {
        if (!in_array($col_name, $existing_columns, true)) {
            $sql = "ALTER TABLE $table_name ADD COLUMN $col_name $col_type";
            $result = $swiperjs_db->query($sql);
            if ($result !== false) {
                echo "Added column $col_name to $table_name<br>";
            }
        }
    }
}

/* -------------------------------------------------------------------
 * Content resolution - normalizes a slide's source_type/source_id into
 * ['title','teaser','image','link'], reading core content directly. Uses
 * a core helper where one exists (se_get_content()/se_get_post_data()/
 * se_get_product_data()); falls back to a direct Medoo query against
 * se_snippets for the by-id snippet lookup, since se_get_snippet() is
 * name-based only - same precedent as plugins/bs-playground,
 * plugins/paperboy and the payment plugins querying core tables directly
 * where no dedicated helper exists.
 * ---------------------------------------------------------------- */

function sjs_source_types(): array {
    global $addon_lang;
    return [
        'snippet' => $addon_lang['option_source_snippet'] ?? 'Snippet',
        'page' => $addon_lang['option_source_page'] ?? 'Seite',
        'post' => $addon_lang['option_source_post'] ?? 'Blog-Eintrag',
        'product' => $addon_lang['option_source_product'] ?? 'Produkt',
    ];
}

/**
 * @return array<int, array{id:int,title:string}>
 */
function sjs_search_source(string $type, string $q, int $limit = 20): array {
    global $db_content, $db_posts;

    $q = trim($q);

    switch ($type) {
        case 'snippet':
            $where = $q !== '' ? ['snippet_title[~]' => $q] : [];
            $rows = $db_content->select('se_snippets', ['snippet_id', 'snippet_title', 'snippet_name'],
                $where + ['ORDER' => ['snippet_id' => 'DESC'], 'LIMIT' => $limit]);
            return array_map(fn($r) => [
                'id' => (int) $r['snippet_id'],
                'title' => $r['snippet_title'] !== '' ? $r['snippet_title'] : $r['snippet_name'],
            ], $rows);

        case 'page':
            $where = $q !== '' ? ['page_title[~]' => $q] : [];
            $rows = $db_content->select('se_pages', ['page_id', 'page_title'],
                $where + ['ORDER' => ['page_id' => 'DESC'], 'LIMIT' => $limit]);
            return array_map(fn($r) => [
                'id' => (int) $r['page_id'],
                'title' => $r['page_title'] !== '' ? $r['page_title'] : '(ohne Titel)',
            ], $rows);

        case 'post':
            $where = $q !== '' ? ['post_title[~]' => $q] : [];
            $rows = $db_posts->select('se_posts', ['post_id', 'post_title'],
                $where + ['ORDER' => ['post_id' => 'DESC'], 'LIMIT' => $limit]);
            return array_map(fn($r) => [
                'id' => (int) $r['post_id'],
                'title' => $r['post_title'] !== '' ? $r['post_title'] : '(ohne Titel)',
            ], $rows);

        case 'product':
            $where = $q !== '' ? ['title[~]' => $q] : [];
            $rows = $db_posts->select('se_products', ['id', 'title'],
                $where + ['ORDER' => ['id' => 'DESC'], 'LIMIT' => $limit]);
            return array_map(fn($r) => [
                'id' => (int) $r['id'],
                'title' => $r['title'] !== '' ? $r['title'] : '(ohne Titel)',
            ], $rows);
    }

    return [];
}

/**
 * @return array{title:string,teaser:string,image:?string,link:?string}|null
 */
function sjs_get_source_record(string $type, int $id): ?array {
    global $db_content, $db_posts;

    if ($id <= 0) {
        return null;
    }

    switch ($type) {
        case 'snippet':
            $row = $db_content->get('se_snippets', '*', ['snippet_id' => $id]);
            if (!$row) { return null; }
            return [
                'title' => $row['snippet_title'] !== '' ? $row['snippet_title'] : $row['snippet_name'],
                'teaser' => $row['snippet_teaser'] !== '' ? $row['snippet_teaser'] : $row['snippet_content'],
                'image' => sjs_extract_image_url($row['snippet_images'] ?? ''),
                'link' => sjs_snippet_link($row),
            ];

        case 'page':
            // se_get_content() default mode 'p' looks up by page_id - the
            // right way to fetch a page by id (app/functions/func_get_content.php).
            // It returns a 2-element [$pageData, $navigationData] tuple, not
            // the page row itself - $pageData is always index 0, confirmed
            // against the actual function body (it ends with
            // `return [$page_contents, $se_nav];`). Missing that "[0]" left
            // every field access silently reading from the wrong array
            // shape (page_title/page_content etc. all just unset, never a
            // fatal error) - a "Seite" slide would render as if the page
            // didn't exist, while snippet/post/product worked fine since
            // their helpers return the row directly.
            $row = function_exists('se_get_content') ? (se_get_content($id)[0] ?? null) : $db_content->get('se_pages', '*', ['page_id' => $id]);
            if (!$row) { return null; }

            // page_thumbnail/page_meta_description are optional fields that,
            // in practice, are almost never filled in for an ordinary
            // content page (unlike post_teaser/post_images or a product's
            // teaser/images, which its own editor prompts for directly) -
            // falling back to the page's own body content, rather than
            // just showing an empty slide, is what makes "Seite" a usable
            // slide source for a typical page.
            $page_content = (string) ($row['page_content'] ?? '');

            $teaser = (string) ($row['page_meta_description'] ?? '');
            if ($teaser === '') {
                // sjs_render_slide_card() already strip_tags()+truncates
                // whatever ends up here, so handing it the raw body is safe.
                $teaser = $page_content;
            }

            $image = sjs_extract_image_url($row['page_thumbnail'] ?? '');
            if ($image === null) {
                $first_image = sjs_first_image_from_html($page_content);
                if ($first_image !== null) {
                    $image = sjs_extract_image_url($first_image);
                }
            }

            return [
                'title' => $row['page_title'] ?? '',
                'teaser' => $teaser,
                'image' => $image,
                'link' => sjs_page_link($row),
            ];

        case 'post':
            $row = function_exists('se_get_post_data') ? se_get_post_data($id) : $db_posts->get('se_posts', '*', ['post_id' => $id]);
            if (!$row) { return null; }
            return [
                'title' => $row['post_title'] ?? '',
                'teaser' => $row['post_teaser'] ?? '',
                'image' => sjs_extract_image_url($row['post_images'] ?? ''),
                'link' => sjs_post_link($row),
            ];

        case 'product':
            $row = function_exists('se_get_product_data') ? se_get_product_data($id) : $db_posts->get('se_products', '*', ['id' => $id]);
            if (!$row) { return null; }
            return [
                'title' => $row['title'] ?? '',
                'teaser' => $row['teaser'] ?? '',
                'image' => sjs_extract_image_url($row['images'] ?? ''),
                'link' => sjs_product_link($row),
            ];
    }

    return null;
}

/**
 * se_pages.page_thumbnail is a plain single filename; se_posts.post_images/
 * se_products.images/se_snippets.snippet_images are a "<->"-delimited list
 * (see acp/core/blog/data-writer.php) - basename() normalizes both shapes
 * down to a bare filename regardless of any "../images/" or
 * "../content/images/" prefix, matching what core's own display handlers
 * do via str_replace() (app/handlers/posts-display.php etc.), just without
 * needing to know every possible prefix up front.
 */
function sjs_extract_image_url(?string $raw): ?string {
    $raw = (string) $raw;
    if ($raw === '') { return null; }

    if (str_contains($raw, '<->')) {
        $parts = array_values(array_filter(explode('<->', $raw), fn($p) => trim($p) !== ''));
        $raw = $parts[0] ?? '';
    }

    $raw = trim($raw);
    if ($raw === '') { return null; }

    return '/images/'.rawurlencode(basename($raw));
}

/**
 * First <img src="..."> found in a page's raw HTML body - the fallback
 * image source for a page with no page_thumbnail set (see
 * sjs_get_source_record()'s 'page' case). The returned src is handed
 * straight to sjs_extract_image_url() by the caller, which only ever keeps
 * its basename anyway, so no further normalization is needed here.
 */
function sjs_first_image_from_html(string $html): ?string {
    if ($html === '' || !preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
        return null;
    }
    return $m[1];
}

function sjs_page_link(array $page): ?string {
    if (!empty($page['page_canonical_url'])) {
        return $page['page_canonical_url'];
    }
    $permalink = trim((string) ($page['page_permalink'] ?? ''), '/');
    return $permalink !== '' ? '/'.$permalink.'/' : null;
}

function sjs_post_link(array $post): ?string {
    return !empty($post['post_canonical_url']) ? $post['post_canonical_url'] : null;
}

function sjs_product_link(array $product): ?string {
    return !empty($product['product_canonical_url']) ? $product['product_canonical_url'] : null;
}

function sjs_snippet_link(array $snippet): ?string {
    $permalink = trim((string) ($snippet['snippet_permalink'] ?? ''));
    if ($permalink === '') { return null; }
    return preg_match('#^https?://#i', $permalink) ? $permalink : '/'.ltrim($permalink, '/');
}

function sjs_truncate(string $str, int $len): string {
    if (mb_strlen($str) <= $len) { return $str; }
    return rtrim(mb_substr($str, 0, $len))."\xe2\x80\xa6";
}

/**
 * A slideshow's "accent_color" field accepts two shapes, so it works for
 * both Bootstrap-based themes and anything else (e.g. Tailwind):
 * - a bare Bootstrap color name ("primary", "danger", ...) -> resolved to
 *   var(--bs-<name>), so it automatically tracks whatever that theme
 *   variable is actually set to;
 * - a literal CSS color value ("#4f46e5", "rgb(79,70,229)", ...) -> used
 *   as-is, no Bootstrap dependency at all.
 * Distinguished by the simplest reliable signal: a real CSS color value
 * always contains "#" or "(", a bare theme-color name never does.
 * Returns null for an empty field (caller then leaves Swiper's own default
 * --swiper-theme-color, set on :root by the vendored CSS, untouched).
 */
function sjs_resolve_accent_color(string $accent): ?string {
    $accent = trim($accent);
    if ($accent === '') { return null; }

    if (str_contains($accent, '#') || str_contains($accent, '(')) {
        return $accent;
    }

    return 'var(--bs-'.$accent.')';
}

/* -------------------------------------------------------------------
 * Frontend rendering
 * ---------------------------------------------------------------- */

/**
 * Renders one slideshow by its shortcode key (the slug an admin picked in
 * the editor). Returns '' for an unknown/inactive key or a slideshow with
 * no (resolvable) slides, so a stale [plugin=swiperjs] tag never leaves
 * broken markup on the page.
 */
function sjs_render_slideshow(string $key): string {
    global $swiperjs_db;

    if (!isset($swiperjs_db) || $key === '') {
        return '';
    }

    $slideshow = $swiperjs_db->get('slideshows', '*', ['slug' => $key, 'status' => 1]);
    if (!$slideshow) {
        return '';
    }

    $slides = $swiperjs_db->select('slides', '*', ['slideshow_id' => $slideshow['id'], 'ORDER' => ['sort_order' => 'ASC']]);

    $cards = '';
    foreach ($slides as $slide) {
        $record = sjs_get_source_record($slide['source_type'], (int) $slide['source_id']);
        if (!$record) {
            // The referenced snippet/page/post/product was deleted after
            // being added as a slide - skip it silently rather than
            // showing an empty/broken slide to a visitor.
            continue;
        }
        $cards .= sjs_render_slide_card($record, sjs_resolve_layout((string) ($slide['layout'] ?? ''), (string) ($slideshow['layout'] ?? '')));
    }

    if ($cards === '') {
        return '';
    }

    $settings = sjs_get_settings();
    $html = sjs_render_assets_once(empty($settings['theme_provides_swiper']), empty($settings['theme_provides_layout_css']));

    $uid = 'sjs-'.((int) $slideshow['id']).'-'.substr(md5(uniqid('', true)), 0, 8);

    $wrap_class = 'swiperjs-wrap';
    if (!empty($slideshow['css_class'])) {
        $wrap_class .= ' '.$slideshow['css_class'];
    }

    $style_rules = [];
    if (!empty($slideshow['height'])) {
        $style_rules[] = 'height:'.$slideshow['height'];
    }
    $accent = sjs_resolve_accent_color((string) ($slideshow['accent_color'] ?? ''));
    if ($accent !== null) {
        // --swiper-theme-color is Swiper's own single accent variable - its
        // CSS already reads it as the fallback for both the nav arrow icon
        // color (.swiper-button-next,.swiper-button-prev{color:var(--swiper-
        // navigation-color,var(--swiper-theme-color))}, the SVG icon fill is
        // currentColor) and the active pagination bullet's background
        // (.swiper-pagination-bullet-active{background:var(--swiper-
        // pagination-color,var(--swiper-theme-color))}), so setting it once
        // here recolors both consistently with zero extra markup.
        $style_rules[] = '--swiper-theme-color:'.$accent;
    }
    $style = $style_rules ? ' style="'.htmlspecialchars(implode(';', $style_rules), ENT_QUOTES).'"' : '';

    $html .= '<div class="'.htmlspecialchars($wrap_class, ENT_QUOTES).'">';
    $html .= '<div class="swiper" id="'.$uid.'"'.$style.'>';
    $html .= '<div class="swiper-wrapper">'.$cards.'</div>';

    if (!empty($slideshow['show_pagination'])) {
        $html .= '<div class="swiper-pagination"></div>';
    }
    if (!empty($slideshow['show_navigation'])) {
        $html .= '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
    }

    $html .= '</div>'; // .swiper
    $html .= '</div>'; // .swiperjs-wrap

    $autoplay_delay = (int) $slideshow['autoplay_delay'];
    $autoplay = $autoplay_delay > 0
        ? '{delay:'.$autoplay_delay.',disableOnInteraction:false}'
        : 'false';

    $effect = preg_replace('/[^a-z]/', '', (string) $slideshow['effect']) ?: 'slide';
    // $uid (used as the DOM id / CSS selector) contains hyphens, which are
    // valid there but not in a JS function name - a distinct identifier
    // built from the same unique parts, with hyphens swapped for
    // underscores, avoids the "Unexpected token '-'" syntax error a
    // hyphenated function name would otherwise cause.
    $init_fn = 'sjsInit_'.str_replace('-', '_', $uid);

    // Swiper's own defaults for pagination.el/navigation.nextEl/
    // navigation.prevEl are all null - passing just `true` (or
    // {clickable:true} with no `el`) never resolves our own
    // .swiper-pagination/.swiper-button-next/.swiper-button-prev divs at
    // all, so nothing gets wired up: no click handlers, and for navigation
    // specifically no icon SVG either (that injection only runs once an
    // element was actually resolved). Explicit selectors below fix this;
    // Swiper's own uniqueNavElements default (true) already scopes a plain
    // class selector back down to the single match inside this particular
    // .swiper instance, so multiple slideshows on one page don't clash.
    $pagination_js = !empty($slideshow['show_pagination']) ? '{el:".swiper-pagination",clickable:true}' : 'false';
    $navigation_js = !empty($slideshow['show_navigation']) ? '{nextEl:".swiper-button-next",prevEl:".swiper-button-prev"}' : 'false';

    $html .= '<script>
(function(){
    function '.$init_fn.'() {
        if (typeof Swiper === "undefined") { return; }
        new Swiper("#'.$uid.'", {
            effect: "'.$effect.'",
            loop: '.((int) $slideshow['loop'] ? 'true' : 'false').',
            speed: '.max(0, (int) $slideshow['speed']).',
            spaceBetween: '.max(0, (int) $slideshow['space_between']).',
            autoplay: '.$autoplay.',
            pagination: '.$pagination_js.',
            navigation: '.$navigation_js.',
            slidesPerView: '.max(1, (int) $slideshow['slides_per_view_mobile']).',
            breakpoints: {
                768: { slidesPerView: '.max(1, (int) $slideshow['slides_per_view_tablet']).' },
                1200: { slidesPerView: '.max(1, (int) $slideshow['slides_per_view_desktop']).' }
            }
        });
    }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", '.$init_fn.');
    } else {
        '.$init_fn.'();
    }
})();
</script>';

    return $html;
}

/**
 * One slide's markup. The structure is the same for every layout - only
 * the swiperjs-layout--<key> class on the slide changes, plus the "text"
 * layout leaving out the image - so a theme styling these classes itself
 * (theme_provides_layout_css on) only has one markup shape to know. The markup is
 * documented in docs/<lang>/theme-design.md; keep both in sync.
 */
function sjs_render_slide_card(array $record, string $layout = 'overlay'): string {
    $show_image = $layout !== 'text' && !empty($record['image']);

    $slide_class = 'swiper-slide swiperjs-slide swiperjs-layout--'.$layout;
    if (!$show_image) {
        // Lets every layout fall back to a plain in-flow caption when
        // there's no image to sit next to/on top of.
        $slide_class .= ' swiperjs-slide--no-image';
    }
    $html = '<div class="'.$slide_class.'">';

    // The link (if any) doubles as the slide's single inner flex container,
    // so the layout CSS can treat linked and unlinked slides the same.
    $has_link = !empty($record['link']);
    $html .= $has_link
        ? '<a href="'.htmlspecialchars($record['link'], ENT_QUOTES).'" class="swiperjs-slide-inner swiperjs-slide-link">'
        : '<div class="swiperjs-slide-inner">';

    if ($show_image) {
        $html .= '<img src="'.htmlspecialchars($record['image'], ENT_QUOTES).'" alt="'.htmlspecialchars($record['title'] ?? '', ENT_QUOTES).'" loading="lazy" class="swiperjs-slide-image">';
    }

    $title = trim((string) ($record['title'] ?? ''));
    // Post/product teaser text is stored HTML-entity-encoded (e.g. a saved
    // "&lt;p&gt;Text&lt;/p&gt;", not a real <p> tag) - strip_tags() alone
    // does nothing to entity-encoded text, so without decoding first, the
    // literal "&lt;p&gt;...&lt;/p&gt;" would show up as visible text once
    // htmlspecialchars() re-escapes it for output below. Decoding first
    // turns that back into a real tag so strip_tags() can actually remove
    // it; harmless on page/snippet content, which is already raw HTML with
    // no entity-encoding to undo.
    $teaser = trim(strip_tags(html_entity_decode((string) ($record['teaser'] ?? ''), ENT_QUOTES, 'UTF-8')));

    if ($title !== '' || $teaser !== '') {
        $html .= '<div class="swiperjs-slide-caption">';
        if ($title !== '') {
            $html .= '<h3 class="swiperjs-slide-title">'.htmlspecialchars($title, ENT_QUOTES).'</h3>';
        }
        if ($teaser !== '') {
            $html .= '<p class="swiperjs-slide-text">'.htmlspecialchars(sjs_truncate($teaser, 160), ENT_QUOTES).'</p>';
        }
        $html .= '</div>';
    }

    $html .= $has_link ? '</a>' : '</div>';
    $html .= '</div>';
    return $html;
}

/* -------------------------------------------------------------------
 * Slide layouts
 * ---------------------------------------------------------------- */

function sjs_layouts(): array {
    global $addon_lang;
    return [
        'overlay' => $addon_lang['option_layout_overlay'] ?? 'Overlay',
        'card' => $addon_lang['option_layout_card'] ?? 'Karte',
        'split' => $addon_lang['option_layout_split'] ?? 'Geteilt',
        'text' => $addon_lang['option_layout_text'] ?? 'Nur Text',
    ];
}

/**
 * A slide's own layout wins over its slideshow's, '' (= inherit) or an
 * unknown key falls through to the next, 'overlay' is the last resort -
 * the result is always a valid sjs_layouts() key, safe to put in a class.
 */
function sjs_resolve_layout(string $slide_layout, string $slideshow_layout): string {
    $layouts = sjs_layouts();
    foreach ([$slide_layout, $slideshow_layout] as $layout) {
        if (isset($layouts[$layout])) {
            return $layout;
        }
    }
    return 'overlay';
}

/**
 * The plugin's own slide CSS, minified for the inline <style> block.
 * assets/swiperjs-layouts.css is the single source - the same file is also
 * served as-is via endpoint.php (linked from docs/<lang>/theme-design.md)
 * for theme designers to copy, so the help can never drift from what the
 * frontend prints. The notes on the less obvious rules live there as CSS
 * comments.
 */
function sjs_layout_css(): string {
    $file = __DIR__.'/../assets/swiperjs-layouts.css';
    if (!is_file($file)) {
        return '';
    }
    $css = preg_replace('~/\*.*?\*/~s', '', file_get_contents($file));
    return trim(preg_replace('/\s+/', ' ', $css));
}

/**
 * Prints the vendored Swiper bundle (only if the theme doesn't already
 * provide it) plus the plugin's own slide-layout stylesheet (only if not
 * turned off under Einstellungen), each at most once per request -
 * buffer_script() can run index.php once per slideshow placed on the same
 * page, and these `static` guards (real function-level statics, unaffected
 * by the buffer_script() stack-frame quirk documented in
 * global/bootstrap.php - functions are defined once, globally, regardless
 * of which include chain reaches them) make sure the <link>/<script>/
 * <style> tags aren't duplicated for a second/third slideshow.
 */
function sjs_render_assets_once(bool $needs_vendor, bool $needs_css): string {
    static $printed_vendor = false;
    static $printed_css = false;

    $html = '';

    if ($needs_vendor && !$printed_vendor) {
        $html .= '<link rel="stylesheet" href="/dispatch.php?p=swiperjs&asset=swiper-bundle.min.css">';
        $html .= '<script src="/dispatch.php?p=swiperjs&asset=swiper-bundle.min.js"></script>';
        $printed_vendor = true;
    }

    if ($needs_css && !$printed_css) {
        // All layouts at once, not just the ones used by this slideshow -
        // a later slideshow on the same page may use another one, and the
        // whole block is only about 2 KB once minified. Inline rather than
        // a <link> to endpoint.php: no extra request, and the vendor CSS
        // above is the only part worth caching separately.
        $html .= '<style>'.sjs_layout_css().'</style>';
        $printed_css = true;
    }

    return $html;
}

/* -------------------------------------------------------------------
 * Admin canvas row (backend/slideshow-editor.php)
 * ---------------------------------------------------------------- */

function sjs_render_slide_row(array $slide): string {
    global $addon_lang;

    $id = (int) $slide['id'];
    $record = sjs_get_source_record($slide['source_type'], (int) $slide['source_id']);
    $types = sjs_source_types();
    $type_label = $types[$slide['source_type']] ?? $slide['source_type'];

    $html = '<div class="list-group-item draggable d-flex align-items-center gap-3" data-id="'.$id.'">';
    // Marker input, same reasoning as plugins/former's fmr_row_marker - the
    // admin theme's shared picker JS auto-adds its own picker_N[] hidden
    // input + a client-side-only trash button to any .draggable row that
    // has none yet, meant for its generic image-picker use case, not this
    // row's own server-backed delete button below.
    $html .= '<input type="hidden" name="sjs_row_marker['.$id.']" value="1">';
    $html .= '<i class="bi bi-grip-vertical text-muted" aria-hidden="true"></i>';

    if ($record && !empty($record['image'])) {
        $html .= '<img src="'.htmlspecialchars($record['image'], ENT_QUOTES).'" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:4px;flex:none;">';
    }

    $html .= '<div class="flex-grow-1">';
    $html .= '<span class="badge text-bg-secondary me-2">'.htmlspecialchars($type_label, ENT_QUOTES).'</span>';
    $html .= $record
        ? htmlspecialchars($record['title'], ENT_QUOTES)
        : '<span class="text-danger">'.($addon_lang['msg_source_missing'] ?? 'Datensatz nicht gefunden').'</span>';
    $html .= '</div>';

    // Per-slide layout override, '' = inherit the slideshow's own layout.
    // Sits inside the slides <form> (backend/slideshow-editor.php), and an
    // hx-post on a non-GET element includes its enclosing form - so a change
    // submits the same save_slides_order request as the "save" button
    // (order + every row's layout), saving the choice right away.
    $current_layout = (string) ($slide['layout'] ?? '');
    $html .= '<select class="form-select form-select-sm custom-select w-auto" name="sjs_slide_layout['.$id.']"
        aria-label="'.htmlspecialchars($addon_lang['label_slide_layout'] ?? 'Layout', ENT_QUOTES).'"
        hx-post="/admin-xhr/addons/plugin/swiperjs/write/" hx-trigger="change"
        hx-target="#sjsSlidesResponse" hx-swap="innerHTML">';
    $html .= '<option value="">'.htmlspecialchars($addon_lang['option_layout_inherit'] ?? 'Wie Slideshow', ENT_QUOTES).'</option>';
    foreach (sjs_layouts() as $layout_key => $layout_label) {
        $selected = $current_layout === $layout_key ? ' selected' : '';
        $html .= '<option value="'.$layout_key.'"'.$selected.'>'.htmlspecialchars($layout_label, ENT_QUOTES).'</option>';
    }
    $html .= '</select>';

    $html .= '<button type="button" class="btn btn-sm btn-default text-danger"
        hx-post="/admin-xhr/addons/plugin/swiperjs/write/"
        hx-vals=\'{"delete_slide":"'.$id.'","csrf_token":"'.$_SESSION['token'].'"}\'
        hx-target="closest .draggable" hx-swap="outerHTML swap:0s"
        hx-confirm="'.htmlspecialchars($addon_lang['msg_confirm_delete_slide'] ?? 'Folie wirklich entfernen?', ENT_QUOTES).'">&times;</button>';

    $html .= '</div>';
    return $html;
}
