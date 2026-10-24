<?php
/**
 * Reached via /dispatch.php?p=swiperjs&asset=<file> (see public/dispatch.php)
 * - a bootstrap-free static-asset endpoint, same pattern as
 * plugins/tracker/endpoint.php. Serves the vendored Swiper bundle so it can
 * be cached forever by the browser (pinned version, filename never changes
 * for a given release - a version bump ships under a new filename or with
 * cache-busting left to the admin, not handled here). Also serves the
 * plugin's own assets/swiperjs-layouts.css for theme designers to view/copy
 * (linked from the help) - with a short cache instead, since that file
 * changes between plugin releases under the same name.
 */

$requested = basename((string) ($_GET['asset'] ?? ''));

// filename => [path below assets/, content type, cache header]
$allowed = [
    'swiper-bundle.min.js' => ['vendor/swiper-bundle.min.js', 'application/javascript', 'public, max-age=31536000, immutable'],
    'swiper-bundle.min.css' => ['vendor/swiper-bundle.min.css', 'text/css', 'public, max-age=31536000, immutable'],
    'swiperjs-layouts.css' => ['swiperjs-layouts.css', 'text/css', 'public, max-age=3600'],
];

if ($requested === '' || !isset($allowed[$requested])) {
    http_response_code(404);
    exit;
}

[$path, $content_type, $cache_control] = $allowed[$requested];
$file = __DIR__.'/assets/'.$path;
if (!is_file($file)) {
    http_response_code(404);
    exit;
}

header('Content-Type: '.$content_type);
header('Cache-Control: '.$cache_control);
header('Content-Length: '.filesize($file));
readfile($file);
