<?php

include __DIR__.'/schema.php';

    // update
    echo '<p class="alert alert-info">We try to update version: '.$addon_info['addon']['version'].'</p>';

    $tables = SwiperjsSchema::getTables();

    // 1. Update/Create all tables with current schema
    foreach ($tables as $table_name => $columns) {
        sjs_updateOrCreateTable($table_name, $columns);
    }

    // 2. Backfill any settings introduced after initial install (e.g. an
    // existing 1.0.0 install upgrading past a version that adds a new
    // default) without touching settings the admin already changed.
    $defaultSettings = sjs_get_default_settings();
    foreach ($defaultSettings as $key => $value) {
        // has(), not a falsy check on get()'s returned value - a stored
        // value of '0' (e.g. theme_provides_swiper's own default) is itself
        // falsy in PHP, so `!get(...)` wrongly treats an existing row as
        // missing and tries to insert a duplicate, which fatals on the
        // settings.key UNIQUE constraint and aborts the whole update.
        if (!$swiperjs_db->has('settings', ['key' => $key])) {
            $swiperjs_db->insert('settings', [
                'key' => $key,
                'value' => (string) $value,
            ]);
        }
    }

    // 3. Update version
    $swiperjs_db->update('settings', [
        'value' => $addon_info['addon']['version']
    ], [
        'key' => 'version'
    ]);

    echo '<p class="alert alert-info">Updated to version: '.$addon_info['addon']['version'].'</p>';
