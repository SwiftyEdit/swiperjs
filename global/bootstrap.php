<?php
global $addon_lang;
use Medoo\Medoo;

$mod_root = SE_ROOT.'plugins/swiperjs/';
$swiperjs_db_file = $mod_root.'data/swiperjs.sqlite3';

// index.php (reached via buffer_script(), which may run multiple times per
// request - once per snippet loop iteration, once for the page body, etc.)
// declares `global $swiperjs_db;` of its own before requiring this file, for
// the same reason this is declared global here too - see plugins/former's
// and plugins/tracker's bootstrap.php for the identical reasoning.
global $swiperjs_db;

// $addon_info is only pre-set when this bootstrap is reached via the admin
// nav-tab router (acp/core/addons/edit-plugin.php). The admin-xhr reader/
// writer router and the frontend shortcode do not set it, so it is loaded
// here directly to make bootstrap.php safe in all contexts.
if(!isset($addon_info)) {
    $addon_info = json_decode(file_get_contents($mod_root.'info.json'), true);
}

require_once __DIR__.'/functions.php';

if(is_file($swiperjs_db_file)) {
    $swiperjs_db = new Medoo([
        'type' => 'sqlite',
        'database' => $swiperjs_db_file
    ]);
} else {

    // Only ever auto-create the database from a real admin-panel request
    // (backend nav tab, or the activation call in acp/core/addons/data-writer.php,
    // which loads this bootstrap indirectly the first time the Übersicht tab
    // is opened). Never from the frontend shortcode - a visitor hitting a
    // page with a [plugin=swiperjs] tag before the plugin has been opened
    // once in the ACP should just render nothing, not trigger a first-time
    // install as a side effect.
    if(SE_SECTION === 'backend') {
        include __DIR__ . '/../install/installer.php';
    }

}

if(isset($swiperjs_db)) {
    $sjs_settings = sjs_get_settings();

    if(SE_SECTION === 'backend') {
        if (version_compare($sjs_settings['version'] ?? '0', $addon_info['addon']['version'], '<')) {
            include __DIR__ . '/../install/updater.php';
        }
    }
}

// se_return_addon_translations() lives in acp/core/functions_addons.php,
// which is only autoloaded on admin pages - it's undefined from the
// frontend shortcode, where $addon_lang isn't needed anyway (index.php
// never renders any admin-facing text).
if(!is_array($addon_lang) && function_exists('se_return_addon_translations')) {
    $addon_lang = se_return_addon_translations('swiperjs');
}
