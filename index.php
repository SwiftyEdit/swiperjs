<?php
/**
 * example: [plugin=swiperjs]key=hero-startseite[/plugin]
 * also usable from a theme template: {plugin name="swiperjs"}key=hero-startseite{/plugin}
 */
if (SE_SECTION !== 'backend') {

    // Reached via buffer_script(), which may run multiple times per request
    // (e.g. once per snippet in template-setup.php's snippet loop, then again
    // for the page content). require_once only executes bootstrap.php's body
    // - and with it the `global $swiperjs_db;` binding - on the first of
    // those calls, so later calls need their own `global` here to see it.
    global $swiperjs_db;
    require_once __DIR__.'/global/bootstrap.php';

    $sjs_key = trim((string) ($key ?? ''));

    if (isset($swiperjs_db) && $sjs_key !== '') {
        echo sjs_render_slideshow($sjs_key);
    }
}
