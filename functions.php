<?php
require_once (dirname(__FILE__) . '/lib/chester/require.php');
require_once (dirname(__FILE__) . '/lib/chemaclass/require.php');

add_theme_support('post-thumbnails', array(
	'post',
	'page'
));

show_admin_bar(false);
