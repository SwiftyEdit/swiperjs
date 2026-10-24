<?php
/**
 * @var string $swiperjs_db_file from bootstrap.php
 * @var string $mod_root
 */
use Medoo\Medoo;
include __DIR__.'/schema.php';


/* INSTALL */

if(!is_file("$swiperjs_db_file")) {

    if(!is_dir(dirname($swiperjs_db_file))) {
        mkdir(dirname($swiperjs_db_file), 0755, true);
    }

    echo '<p class="alert alert-info">We try to generate SQLite File: '.$swiperjs_db_file.'</p>';

        $swiperjs_db = new Medoo([
            'type' => 'sqlite',
            'database' => $swiperjs_db_file
        ]);

        $tables = SwiperjsSchema::getTables();

        foreach ($tables as $table_name => $columns) {
            $col_definitions = [];
            foreach ($columns as $col_name => $col_type) {
                $col_definitions[] = "$col_name $col_type";
            }

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (" .
                implode(', ', $col_definitions) . ")";

            $swiperjs_db->query($sql)->execute();
        }

        $swiperjs_db->query("CREATE UNIQUE INDEX IF NOT EXISTS idx_slideshows_slug ON slideshows (slug)")->execute();
        $swiperjs_db->query("CREATE INDEX IF NOT EXISTS idx_slides_slideshow ON slides (slideshow_id, sort_order)")->execute();

        $swiperjs_db->insert('settings', [
            'key' => 'version',
            'value' => $addon_info['addon']['version']
        ]);

        echo '<p class="alert alert-info">Generated SQLite File: '.$swiperjs_db_file.'</p>';

        $defaultSettings = sjs_get_default_settings();

    foreach ($defaultSettings as $key => $value) {
        // has(), not a falsy check on get()'s value - see install/updater.php
        // for why (a stored '0' is itself falsy in PHP).
        if (!$swiperjs_db->has('settings', ['key' => $key])) {
            $swiperjs_db->insert('settings', [
                'key' => $key,
                'value' => (string)$value,
            ]);
        }
    }

}

// sjs_get_default_settings() lives in global/functions.php (loaded by
// bootstrap.php before this file is included either here or from
// install/updater.php) so both the initial install and every later update
// can seed newly-introduced default settings from the one place.
