<?php
require __DIR__.'/../global/bootstrap.php';
echo '<h1>'.$addon_lang['title_settings'].'</h1>';
echo '<div class="row"><div class="col-md-8"><div class="card">';
echo '<div class="card-body" hx-get="/admin-xhr/addons/plugin/swiperjs/read/?show=settings_form" hx-trigger="load">LOADING ...</div>';
echo '</div></div></div>';
