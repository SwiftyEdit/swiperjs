<?php

require __DIR__.'/../global/bootstrap.php';

/* ---------------------------------------------------------------
 * Slideshows list
 * -------------------------------------------------------------- */
if (isset($_GET['show']) && $_GET['show'] === 'slideshows_list') {

    $slideshows = $swiperjs_db->select('slideshows', '*', ['ORDER' => ['id' => 'DESC']]);

    echo '<div class="card p-3">';
    echo '<div class="d-flex justify-content-between align-items-center mb-3">';
    echo '<div></div>';
    echo '<button type="button" class="btn btn-primary btn-sm"
        hx-post="/admin-xhr/addons/plugin/swiperjs/write/"
        hx-vals=\'{"create_slideshow":"1","csrf_token":"'.$_SESSION['token'].'"}\'>'.$addon_lang['btn_new_slideshow'].'</button>';
    echo '</div>';

    if (!$slideshows) {
        echo '<p class="text-muted">'.$addon_lang['msg_no_slideshows'].'</p>';
    } else {
        echo '<table class="table table-sm table-striped table-hover align-middle">';
        echo '<thead><tr>';
        echo '<th>'.$addon_lang['th_name'].'</th>';
        echo '<th>'.$addon_lang['th_shortcode'].'</th>';
        echo '<th>'.$addon_lang['th_slides'].'</th>';
        echo '<th class="text-end">'.$addon_lang['th_actions'].'</th>';
        echo '</tr></thead><tbody>';

        foreach ($slideshows as $slideshow) {
            $count = $swiperjs_db->count('slides', ['slideshow_id' => $slideshow['id']]);
            $shortcode = '[plugin=swiperjs]key='.$slideshow['slug'].'[/plugin]';

            echo '<tr>';
            echo '<td>'.htmlspecialchars($slideshow['name']).($slideshow['status'] ? '' : ' <span class="badge text-bg-secondary">'.$addon_lang['label_inactive'].'</span>').'</td>';
            echo '<td><input type="text" class="form-control form-control-sm" readonly value="'.htmlspecialchars($shortcode).'" onclick="this.select()" style="width:280px"></td>';
            echo '<td>'.$count.'</td>';
            echo '<td class="text-end">';
            echo '<a class="btn btn-sm btn-default" href="/admin/addons/plugin/swiperjs/slideshow-editor/?slideshow_id='.$slideshow['id'].'">'.$addon_lang['btn_edit'].'</a> ';
            echo '<button type="button" class="btn btn-sm btn-default text-danger"
                hx-post="/admin-xhr/addons/plugin/swiperjs/write/"
                hx-vals=\'{"delete_slideshow":"'.$slideshow['id'].'","csrf_token":"'.$_SESSION['token'].'"}\'
                hx-confirm="'.htmlspecialchars($addon_lang['msg_confirm_delete_slideshow']).'"
                hx-target="closest tr" hx-swap="outerHTML swap:0s">'.$addon_lang['btn_delete'].'</button>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    echo '</div>';
    exit;
}

/* ---------------------------------------------------------------
 * Slideshow settings sub-form (name, slug/shortcode, Swiper options)
 * -------------------------------------------------------------- */
if (isset($_GET['show']) && $_GET['show'] === 'slideshow_settings') {

    $slideshow_id = (int) ($_GET['slideshow_id'] ?? 0);
    $slideshow = $swiperjs_db->get('slideshows', '*', ['id' => $slideshow_id]);
    if (!$slideshow) { exit; }

    $shortcode = '[plugin=swiperjs]key='.$slideshow['slug'].'[/plugin]';
    $effects = ['slide', 'fade', 'cube', 'coverflow', 'flip'];

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_shortcode'].'</label>';
    echo '<input type="text" class="form-control form-control-sm" readonly value="'.htmlspecialchars($shortcode).'" onclick="this.select()"></div>';

    echo '<div id="sjsSettingsResponse"></div>';
    echo '<form hx-post="/admin-xhr/addons/plugin/swiperjs/write/" hx-target="#sjsSettingsResponse" hx-swap="innerHTML">';
    echo '<input type="hidden" name="save_slideshow_settings" value="'.$slideshow_id.'">';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_name'].'</label>';
    echo '<input type="text" class="form-control" name="name" value="'.htmlspecialchars($slideshow['name']).'"></div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_slug'].'</label>';
    echo '<input type="text" class="form-control" name="slug" value="'.htmlspecialchars($slideshow['slug']).'">';
    echo '<div class="form-text">'.$addon_lang['hint_slug'].'</div></div>';

    echo '<div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="status" value="1" id="sjs-status" '.($slideshow['status'] ? 'checked' : '').'>';
    echo '<label class="form-check-label" for="sjs-status">'.$addon_lang['label_status'].'</label></div>';

    echo '<hr>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_layout'].'</label>';
    echo '<select class="form-select custom-select" name="layout">';
    foreach (sjs_layouts() as $layout_key => $layout_label) {
        $selected = ($slideshow['layout'] ?? 'overlay') === $layout_key ? 'selected' : '';
        echo '<option value="'.$layout_key.'" '.$selected.'>'.htmlspecialchars($layout_label).'</option>';
    }
    echo '</select>';
    echo '<div class="form-text">'.$addon_lang['hint_layout'].'</div></div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_effect'].'</label>';
    echo '<select class="form-select custom-select" name="effect">';
    foreach ($effects as $eff) {
        $selected = $slideshow['effect'] === $eff ? 'selected' : '';
        $eff_label = $addon_lang['option_effect_'.$eff] ?? ucfirst($eff);
        echo '<option value="'.$eff.'" '.$selected.'>'.htmlspecialchars($eff_label).'</option>';
    }
    echo '</select></div>';

    echo '<div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="loop" value="1" id="sjs-loop" '.($slideshow['loop'] ? 'checked' : '').'>';
    echo '<label class="form-check-label" for="sjs-loop">'.$addon_lang['label_loop'].'</label></div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_autoplay_delay'].'</label>';
    echo '<input type="number" min="0" step="500" class="form-control" name="autoplay_delay" value="'.(int) $slideshow['autoplay_delay'].'">';
    echo '<div class="form-text">'.$addon_lang['hint_autoplay_delay'].'</div></div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_speed'].'</label>';
    echo '<input type="number" min="0" step="100" class="form-control" name="speed" value="'.(int) $slideshow['speed'].'"></div>';

    echo '<div class="row">';
    echo '<div class="col-4 mb-3"><label class="form-label small">'.$addon_lang['label_slides_per_view_mobile'].'</label>';
    echo '<input type="number" min="1" class="form-control" name="slides_per_view_mobile" value="'.(int) $slideshow['slides_per_view_mobile'].'"></div>';
    echo '<div class="col-4 mb-3"><label class="form-label small">'.$addon_lang['label_slides_per_view_tablet'].'</label>';
    echo '<input type="number" min="1" class="form-control" name="slides_per_view_tablet" value="'.(int) $slideshow['slides_per_view_tablet'].'"></div>';
    echo '<div class="col-4 mb-3"><label class="form-label small">'.$addon_lang['label_slides_per_view_desktop'].'</label>';
    echo '<input type="number" min="1" class="form-control" name="slides_per_view_desktop" value="'.(int) $slideshow['slides_per_view_desktop'].'"></div>';
    echo '</div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_space_between'].'</label>';
    echo '<input type="number" min="0" class="form-control" name="space_between" value="'.(int) $slideshow['space_between'].'"></div>';

    echo '<div class="mb-2 form-check"><input type="checkbox" class="form-check-input" name="show_navigation" value="1" id="sjs-nav" '.($slideshow['show_navigation'] ? 'checked' : '').'>';
    echo '<label class="form-check-label" for="sjs-nav">'.$addon_lang['label_show_navigation'].'</label></div>';

    echo '<div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="show_pagination" value="1" id="sjs-pag" '.($slideshow['show_pagination'] ? 'checked' : '').'>';
    echo '<label class="form-check-label" for="sjs-pag">'.$addon_lang['label_show_pagination'].'</label></div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_height'].'</label>';
    echo '<input type="text" class="form-control" name="height" value="'.htmlspecialchars($slideshow['height'] ?? '').'" placeholder="400px">';
    echo '<div class="form-text">'.$addon_lang['hint_height'].'</div></div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_accent_color'].'</label>';
    echo '<input type="text" class="form-control" name="accent_color" value="'.htmlspecialchars($slideshow['accent_color'] ?? '').'" placeholder="primary">';
    echo '<div class="form-text">'.$addon_lang['hint_accent_color'].'</div></div>';

    echo '<div class="mb-3"><label class="form-label">'.$addon_lang['label_css_class'].'</label>';
    echo '<input type="text" class="form-control" name="css_class" value="'.htmlspecialchars($slideshow['css_class'] ?? '').'"></div>';

    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '<button type="submit" class="btn btn-primary">'.$addon_lang['btn_save'].'</button>';
    echo '</form>';
    exit;
}

/* ---------------------------------------------------------------
 * Live search for the "add slide" picker (snippet/page/post/product)
 * -------------------------------------------------------------- */
if (isset($_GET['show']) && $_GET['show'] === 'source_search') {

    $slideshow_id = (int) ($_GET['slideshow_id'] ?? 0);
    $type = $_GET['type'] ?? '';
    $q = trim((string) ($_GET['q'] ?? ''));

    $valid_types = sjs_source_types();
    if (!$slideshow_id || !isset($valid_types[$type])) {
        exit;
    }

    $results = sjs_search_source($type, $q);

    if (!$results) {
        echo '<p class="text-muted small mb-0 p-2">'.$addon_lang['msg_no_search_results'].'</p>';
        exit;
    }

    foreach ($results as $r) {
        echo '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
            hx-post="/admin-xhr/addons/plugin/swiperjs/write/"
            hx-vals=\'{"add_slide":"1","slideshow_id":"'.$slideshow_id.'","source_type":"'.htmlspecialchars($type, ENT_QUOTES).'","source_id":"'.(int) $r['id'].'","csrf_token":"'.$_SESSION['token'].'"}\'
            hx-target="#sjsSlideCanvas" hx-swap="beforeend">';
        echo '<span>'.htmlspecialchars($r['title']).'</span>';
        echo '<span class="badge text-bg-primary">+</span>';
        echo '</button>';
    }
    exit;
}

/* ---------------------------------------------------------------
 * Plugin-level settings (theme already provides Swiper.js? its own slide
 * layout CSS?) - both checkboxes deliberately mean "checked = the theme
 * brings it, the plugin leaves it out".
 * -------------------------------------------------------------- */
if (isset($_GET['show']) && $_GET['show'] === 'settings_form') {

    $settings = sjs_get_settings();

    echo '<div id="response"></div>';
    echo '<form hx-post="/admin-xhr/addons/plugin/swiperjs/write/" hx-target="#response" hx-swap="innerHTML">';

    echo '<div class="mb-3 form-check">';
    echo '<input type="checkbox" class="form-check-input" name="theme_provides_swiper" value="1" id="sjs-theme-provides" '.(!empty($settings['theme_provides_swiper']) ? 'checked' : '').'>';
    echo '<label class="form-check-label" for="sjs-theme-provides">'.$addon_lang['label_theme_provides_swiper'].'</label>';
    echo '<div class="form-text">'.$addon_lang['hint_theme_provides_swiper'].'</div>';
    echo '</div>';

    echo '<div class="mb-3 form-check">';
    echo '<input type="checkbox" class="form-check-input" name="theme_provides_layout_css" value="1" id="sjs-theme-provides-css" '.(!empty($settings['theme_provides_layout_css']) ? 'checked' : '').'>';
    echo '<label class="form-check-label" for="sjs-theme-provides-css">'.$addon_lang['label_theme_provides_layout_css'].'</label>';
    echo '<div class="form-text">'.$addon_lang['hint_theme_provides_layout_css'].'</div>';
    echo '</div>';

    echo '<input type="hidden" name="save_settings" value="1">';
    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '<button type="submit" class="btn btn-primary">'.$addon_lang['btn_save'].'</button>';
    echo '</form>';

    // Opens docs/<lang>/theme-design.md in core's help window (build
    // 26-0225+). Outside the <form> on purpose: se_print_plugin_docs_link()
    // renders a <button> without type="button", which would otherwise
    // submit the settings form.
    // The CSS file link deliberately lives here and not in the help page
    // itself: core's help window intercepts every non-http(s) link and
    // tries to load it as another doc.
    echo '<hr>';
    echo '<div class="d-flex flex-wrap gap-3">';
    echo se_print_plugin_docs_link('swiperjs', 'theme-design.md', '<i class="bi bi-question-circle me-1"></i>'.$addon_lang['btn_docs_theme_design'], null, 'btn btn-link p-0');
    echo '<a class="btn btn-link p-0" href="/dispatch.php?p=swiperjs&asset=swiperjs-layouts.css" target="_blank" rel="noopener"><i class="bi bi-filetype-css me-1"></i>'.$addon_lang['btn_open_layout_css'].'</a>';
    echo '</div>';
    exit;
}

exit;
