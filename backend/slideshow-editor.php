<?php
require __DIR__.'/../global/bootstrap.php';

$slideshow_id = (int) ($_GET['slideshow_id'] ?? 0);
$slideshow = $swiperjs_db->get('slideshows', '*', ['id' => $slideshow_id]);

if (!$slideshow) {
    echo '<div class="alert alert-danger">'.$addon_lang['msg_slideshow_not_found'].'</div>';
    return;
}

// Small breadcrumb-style heading, not a full <h1> - slideshow-editor isn't
// its own tab (reached via the "Bearbeiten" button on a row in the
// "Übersicht" tab's list), so this exists only to say *which* slideshow is
// open, with a way back to that list. Same pattern as
// plugins/former/backend/form-editor.php.
echo '<nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0">';
echo '<li class="breadcrumb-item"><a href="/admin/addons/plugin/swiperjs/start/">'.htmlspecialchars($addon_lang['title_slideshows_list']).'</a></li>';
echo '<li class="breadcrumb-item active" aria-current="page">'.$addon_lang['title_slideshow_editor'].' – '.htmlspecialchars($slideshow['name']).'</li>';
echo '</ol></nav>';

echo '<div class="row">';

/* -------------------------------------------------------------------
 * Left column: slideshow settings (name, slug/shortcode, Swiper options)
 * ---------------------------------------------------------------- */
echo '<div class="col-md-4">';
echo '<div class="card">';
echo '<div class="card-header">'.$addon_lang['title_slideshow_settings'].'</div>';
echo '<div class="card-body" hx-get="/admin-xhr/addons/plugin/swiperjs/read/?show=slideshow_settings&slideshow_id='.$slideshow_id.'" hx-trigger="load, update_swiperjs_slideshow_settings from:body">LOADING ...</div>';
echo '</div>';
echo '</div>';

/* -------------------------------------------------------------------
 * Right column: add-slide live search + drag-sortable slides canvas
 * ---------------------------------------------------------------- */
echo '<div class="col-md-8">';

echo '<div class="card mb-3">';
echo '<div class="card-header">'.$addon_lang['title_add_slide'].'</div>';
echo '<div class="card-body">';

echo '<div class="row g-2 mb-2">';
echo '<div class="col-md-4">';
// "custom-select" on every <select> in this plugin's backend: the admin
// theme's select.form-select{background:...} shorthand wipes Bootstrap's
// own arrow icon, and core's select.custom-select rule (same class as
// acp/templates/bs-form-select.tpl) is what puts it back.
echo '<select class="form-select form-select-sm custom-select" id="sjsSourceType" name="type">';
foreach (sjs_source_types() as $type_key => $label) {
    echo '<option value="'.htmlspecialchars($type_key).'">'.htmlspecialchars($label).'</option>';
}
echo '</select>';
echo '</div>';
echo '<div class="col-md-8">';
echo '<input type="text" class="form-control form-control-sm" name="q" placeholder="'.htmlspecialchars($addon_lang['placeholder_source_search']).'"
    hx-get="/admin-xhr/addons/plugin/swiperjs/read/?show=source_search&slideshow_id='.$slideshow_id.'"
    hx-include="#sjsSourceType" hx-trigger="keyup changed delay:400ms, change" hx-target="#sjsSourceResults">';
echo '</div>';
echo '</div>';

echo '<div id="sjsSourceResults" class="list-group"></div>';

echo '</div>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">'.$addon_lang['title_slides'].'</div>';
echo '<div class="card-body">';

echo '<form hx-post="/admin-xhr/addons/plugin/swiperjs/write/" hx-target="#sjsSlidesResponse" hx-swap="innerHTML">';
echo '<div id="sjsSlidesResponse"></div>';

/*
 * Same reasoning as plugins/former/backend/form-editor.php's identical
 * <style> block: the admin theme's .sortable_target::after rule always
 * renders "DROP IMAGES HERE" (meant for the image picker, the other
 * consumer of this shared Sortable component) - wrong wording here too,
 * since slides aren't added by dragging into this canvas from outside,
 * only reordered via the search results above. Suppressed with an
 * id+class selector, which outweighs the theme's plain-class selector on
 * specificity - no !important, no core file touched.
 */
echo '<style>
#sjsSlideCanvas.sortable_target::after { content: none; }
#sjsSlideCanvas.sortable_target > .draggable {
    margin-bottom: 8px;
    border: 1px solid var(--bs-border-color);
    border-left: 2px solid var(--bs-primary);
    border-radius: var(--bs-border-radius, .375rem);
}
#sjsSlideCanvas.sortable_target > .draggable:last-child { margin-bottom: 0; }
</style>';

/*
 * The .sortable_target class must be present in the *initial*
 * server-rendered HTML, not injected later via an hx-get swap - see the
 * detailed comment in plugins/former/backend/form-editor.php for why
 * (the admin theme's SortableJS wiring only scans descendants of the newly
 * loaded content root on htmx:load, so a container swapped in via
 * hx-swap="innerHTML" never gets to re-scan its own class list). Rendering
 * the canvas synchronously here, not via a follow-up hx-get, sidesteps
 * that. New slides added later via "add_slide" append into this
 * already-initialized Sortable container via hx-swap="beforeend", which
 * Sortable and the MutationObserver-based order-tracking below both pick
 * up automatically without needing re-initialization.
 */
$slides = $swiperjs_db->select('slides', '*', ['slideshow_id' => $slideshow_id, 'ORDER' => ['sort_order' => 'ASC']]);
echo '<div id="sjsSlideCanvas" class="sortable_target list-group mb-3">';
if (!$slides) {
    echo '<p class="text-muted mb-0" id="sjsSlideCanvasEmptyHint">'.$addon_lang['msg_no_slides'].'</p>';
} else {
    foreach ($slides as $slide) {
        echo sjs_render_slide_row($slide);
    }
}
echo '</div>';

echo '<input type="hidden" name="save_slides_order" value="'.$slideshow_id.'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '<input type="hidden" name="sjs_slide_order" id="sjsSlideOrder" value="">';
echo '<button type="submit" class="btn btn-primary">'.$addon_lang['btn_save_order'].'</button>';
echo '</form>';

echo '</div>';
echo '</div>';

echo '</div>'; // col-md-8
echo '</div>'; // row

/*
 * Own order tracking, same MutationObserver idiom as
 * plugins/former/backend/form-editor.php's #fmrFieldOrder (see that file's
 * comment for why this doesn't reuse the admin theme's shared picker_0[]
 * mechanism): a single hidden input (#sjsSlideOrder) holding a
 * comma-joined list of slide ids in DOM order, recomputed on every
 * mutation of #sjsSlideCanvas (drag-reorder, or a slide appended by
 * "add_slide"). backend/writer.php reads sjs_slide_order instead of
 * picker_0.
 */
echo '<script>
(function() {
    var canvas = document.getElementById("sjsSlideCanvas");
    var orderInput = document.getElementById("sjsSlideOrder");
    if (!canvas || !orderInput) { return; }

    function syncOrder() {
        var ids = [];
        canvas.querySelectorAll(":scope > .draggable[data-id]").forEach(function(el) {
            ids.push(el.getAttribute("data-id"));
        });
        orderInput.value = ids.join(",");
    }

    new MutationObserver(syncOrder).observe(canvas, { childList: true, subtree: true });
    syncOrder();
})();
</script>';
