<?php
// Pure table schema definitions - edit here to add/modify columns.

return [
    'slideshows' => [
        'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
        'name' => "VARCHAR(255) NOT NULL DEFAULT ''",
        // The shortcode's key=<slug> value - unique, admin-chosen.
        'slug' => "VARCHAR(100) NOT NULL DEFAULT ''",
        'status' => 'INTEGER DEFAULT 1',
        'effect' => "VARCHAR(20) NOT NULL DEFAULT 'slide'",
        'loop' => 'INTEGER DEFAULT 1',
        // 0 = autoplay off.
        'autoplay_delay' => 'INTEGER DEFAULT 5000',
        'speed' => 'INTEGER DEFAULT 500',
        'slides_per_view_desktop' => 'INTEGER DEFAULT 1',
        'slides_per_view_tablet' => 'INTEGER DEFAULT 1',
        'slides_per_view_mobile' => 'INTEGER DEFAULT 1',
        'space_between' => 'INTEGER DEFAULT 0',
        'show_navigation' => 'INTEGER DEFAULT 1',
        'show_pagination' => 'INTEGER DEFAULT 1',
        // Free text (e.g. "400px", "60vh") - empty = auto height.
        'height' => "VARCHAR(20) NOT NULL DEFAULT ''",
        'css_class' => "VARCHAR(255) NOT NULL DEFAULT ''",
        // A Bootstrap theme color name (e.g. "primary", "danger") - resolved
        // to var(--bs-<name>) at render time and fed into Swiper's own
        // --swiper-theme-color, which drives both the nav arrow icon color
        // and the active pagination bullet color. Empty = Swiper's own
        // default blue. See sjs_render_slideshow() in global/functions.php.
        'accent_color' => "VARCHAR(30) NOT NULL DEFAULT ''",
        // Default slide layout (overlay | card | split | text), see
        // sjs_layouts() in global/functions.php. A slide's own layout
        // column overrides it per slide.
        'layout' => "VARCHAR(20) NOT NULL DEFAULT 'overlay'",
        'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
    ],

    // One row per slide. source_type/source_id point at a core content
    // record (se_snippets/se_pages/se_posts/se_products) - title, teaser,
    // image and link are always resolved live from that record at render
    // time (sjs_get_source_record() in global/functions.php), never cached
    // here, so a slide always reflects the current state of the record it
    // points to.
    'slides' => [
        'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
        'slideshow_id' => 'INTEGER NOT NULL',
        'sort_order' => 'INTEGER DEFAULT 0',
        // snippet | page | post | product
        'source_type' => "VARCHAR(20) NOT NULL DEFAULT ''",
        'source_id' => 'INTEGER NOT NULL DEFAULT 0',
        // '' = inherit the slideshow's own layout.
        'layout' => "VARCHAR(20) NOT NULL DEFAULT ''",
    ],

    'settings' => [
        'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
        'key' => 'TEXT NOT NULL UNIQUE',
        'value' => 'TEXT',
    ],
];
