<?php

require __DIR__.'/../global/bootstrap.php';

/* ---------------------------------------------------------------
 * Slideshows
 * -------------------------------------------------------------- */

if (isset($_POST['create_slideshow'])) {
    $swiperjs_db->insert('slideshows', [
        'name' => 'Neue Slideshow',
        'slug' => 'slideshow-'.time(),
    ]);
    $new_id = $swiperjs_db->id();
    // slug depends on the row's own id, so it's set in a second step -
    // guarantees uniqueness without a race against another concurrent
    // "create" (two slideshows created in the same second would otherwise
    // collide on slug).
    $swiperjs_db->update('slideshows', ['slug' => 'slideshow-'.$new_id], ['id' => $new_id]);
    header('HX-Redirect: /admin/addons/plugin/swiperjs/slideshow-editor/?slideshow_id='.$new_id);
    exit;
}

if (isset($_POST['delete_slideshow'])) {
    $slideshow_id = (int) $_POST['delete_slideshow'];
    $swiperjs_db->delete('slides', ['slideshow_id' => $slideshow_id]);
    $swiperjs_db->delete('slideshows', ['id' => $slideshow_id]);
    header('HX-Trigger: update_swiperjs_slideshows');
    exit;
}

if (isset($_POST['save_slideshow_settings'])) {
    $slideshow_id = (int) $_POST['save_slideshow_settings'];

    $current_slug = (string) $swiperjs_db->get('slideshows', 'slug', ['id' => $slideshow_id]);
    $requested_slug = preg_replace('/[^a-z0-9-]/', '', strtolower(trim((string) ($_POST['slug'] ?? ''))));
    // Re-validated against uniqueness rather than trusted as-is - a
    // duplicate or emptied-out slug would otherwise either break the
    // shortcode lookup (two slideshows sharing one key = only the first
    // ever renders, see sjs_render_slideshow()) or make it impossible to
    // reference this slideshow by key at all. Falls back to the slideshow's
    // existing slug in either case, same "re-validate against reality"
    // idiom as plugins/former/backend/writer.php's template_set handling.
    $slug_taken = $requested_slug !== '' && $requested_slug !== $current_slug
        && $swiperjs_db->has('slideshows', ['slug' => $requested_slug]);
    $slug = ($requested_slug === '' || $slug_taken) ? $current_slug : $requested_slug;

    $effect = in_array($_POST['effect'] ?? '', ['slide', 'fade', 'cube', 'coverflow', 'flip'], true)
        ? $_POST['effect'] : 'slide';

    $layout = isset(sjs_layouts()[$_POST['layout'] ?? '']) ? $_POST['layout'] : 'overlay';

    $swiperjs_db->update('slideshows', [
        'name' => sanitizeUserInputs($_POST['name'] ?? ''),
        'slug' => $slug,
        'status' => isset($_POST['status']) ? 1 : 0,
        'effect' => $effect,
        'loop' => isset($_POST['loop']) ? 1 : 0,
        'autoplay_delay' => max(0, (int) ($_POST['autoplay_delay'] ?? 0)),
        'speed' => max(0, (int) ($_POST['speed'] ?? 500)),
        'slides_per_view_desktop' => max(1, (int) ($_POST['slides_per_view_desktop'] ?? 1)),
        'slides_per_view_tablet' => max(1, (int) ($_POST['slides_per_view_tablet'] ?? 1)),
        'slides_per_view_mobile' => max(1, (int) ($_POST['slides_per_view_mobile'] ?? 1)),
        'space_between' => max(0, (int) ($_POST['space_between'] ?? 0)),
        'show_navigation' => isset($_POST['show_navigation']) ? 1 : 0,
        'show_pagination' => isset($_POST['show_pagination']) ? 1 : 0,
        'height' => preg_replace('/[^a-zA-Z0-9%.\s]/', '', trim((string) ($_POST['height'] ?? ''))),
        // Whitelist covers both accepted shapes (see sjs_render_slideshow()
        // in global/functions.php): a bare Bootstrap color name like
        // "primary", or a literal CSS color value like "#4f46e5" /
        // "rgb(79,70,229)" - deliberately excludes ';{}<>"\'' etc. since
        // this value is interpolated directly into an inline style/var().
        'accent_color' => preg_replace('/[^a-zA-Z0-9#(),.% -]/', '', trim((string) ($_POST['accent_color'] ?? ''))),
        'css_class' => preg_replace('/[^a-zA-Z0-9_ -]/', '', trim((string) ($_POST['css_class'] ?? ''))),
        'layout' => $layout,
    ], ['id' => $slideshow_id]);

    echo '<div class="alert alert-success">'.$addon_lang['msg_saved'].'</div>';
    header('HX-Trigger: update_swiperjs_slideshows');
    exit;
}

/* ---------------------------------------------------------------
 * Slides
 * -------------------------------------------------------------- */

if (isset($_POST['add_slide'])) {
    $slideshow_id = (int) ($_POST['slideshow_id'] ?? 0);
    $source_type = $_POST['source_type'] ?? '';
    $source_id = (int) ($_POST['source_id'] ?? 0);

    $valid_types = sjs_source_types();
    if (!$slideshow_id || !isset($valid_types[$source_type]) || $source_id <= 0) {
        exit;
    }

    // Confirm the referenced record actually exists before storing the
    // reference - a stale/tampered id would otherwise show up as "missing"
    // in the slides canvas immediately after adding it.
    if (!sjs_get_source_record($source_type, $source_id)) {
        exit;
    }

    $max_order = $swiperjs_db->max('slides', 'sort_order', ['slideshow_id' => $slideshow_id]);

    $swiperjs_db->insert('slides', [
        'slideshow_id' => $slideshow_id,
        'source_type' => $source_type,
        'source_id' => $source_id,
        'sort_order' => ((int) $max_order) + 1,
    ]);
    $new_id = $swiperjs_db->id();
    $slide = $swiperjs_db->get('slides', '*', ['id' => $new_id]);

    // out-of-band removal of the "no slides yet" hint, if this was the
    // slideshow's first slide (hx-swap="beforeend" only ever appends, it
    // can't remove the now-stale empty-state hint by itself)
    echo '<p id="sjsSlideCanvasEmptyHint" hx-swap-oob="delete"></p>';
    echo sjs_render_slide_row($slide);
    exit;
}

if (isset($_POST['delete_slide'])) {
    $slide_id = (int) $_POST['delete_slide'];
    $swiperjs_db->delete('slides', ['id' => $slide_id]);
    exit;
}

if (isset($_POST['save_slides_order'])) {
    $slideshow_id = (int) $_POST['save_slides_order'];
    $order_raw = $_POST['sjs_slide_order'] ?? '';
    $order = $order_raw !== '' ? explode(',', $order_raw) : [];

    $position = 0;
    foreach ($order as $slide_id) {
        $slide_id = (int) $slide_id;
        if (!$slide_id) { continue; }
        // Scoped to this slideshow_id too, not just id - a tampered
        // sjs_slide_order couldn't otherwise be used to reassign another
        // slideshow's slide's sort_order.
        $swiperjs_db->update('slides', ['sort_order' => $position], ['id' => $slide_id, 'slideshow_id' => $slideshow_id]);
        $position++;
    }

    // Per-slide layout selects (sjs_render_slide_row()) - '' = inherit
    // the slideshow's own layout, anything not in sjs_layouts() falls back
    // to that too. Each select also triggers this same form submit on
    // change, so a layout choice is saved right away.
    $layouts = sjs_layouts();
    foreach ((array) ($_POST['sjs_slide_layout'] ?? []) as $slide_id => $slide_layout) {
        $slide_layout = isset($layouts[$slide_layout]) ? $slide_layout : '';
        $swiperjs_db->update('slides', ['layout' => $slide_layout], ['id' => (int) $slide_id, 'slideshow_id' => $slideshow_id]);
    }

    echo '<div class="alert alert-success">'.$addon_lang['msg_saved'].'</div>';
    exit;
}

/* ---------------------------------------------------------------
 * Plugin settings
 * -------------------------------------------------------------- */

if (isset($_POST['save_settings'])) {
    sjs_save_setting('theme_provides_swiper', isset($_POST['theme_provides_swiper']) ? 1 : 0);
    sjs_save_setting('theme_provides_layout_css', isset($_POST['theme_provides_layout_css']) ? 1 : 0);
    echo '<div class="alert alert-success">'.$addon_lang['msg_saved'].'</div>';
    exit;
}

exit;
