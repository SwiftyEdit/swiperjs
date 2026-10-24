<?php
require __DIR__.'/../global/bootstrap.php';

// No page heading here - the "Swiper Slider" tab itself already says where
// we are, same reasoning as plugins/former/backend/start.php. Only the
// drilled-down slideshow-editor.php (not its own tab) gets a small
// breadcrumb-style heading, since that needs to say *which* slideshow you're on.
echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<div hx-get="/admin-xhr/addons/plugin/swiperjs/read/?show=slideshows_list" hx-trigger="load, update_swiperjs_slideshows from:body">';
echo 'LOADING ...';
echo '</div>';
echo '</div>';
echo '</div>';
