<?php

require_once ('start.php');

require_once (dirname(__FILE__) . '/lib/chester/require.php');
require_once (dirname(__FILE__) . '/lib/chemaclass/require.php');

foreach (glob(dirname(__FILE__) . '/mvc/models/*.php') as $filename) {
	require_once $filename;
}

foreach (glob(dirname(__FILE__) . '/config/*.php') as $filename) {
	require_once $filename;
}

/**
 * Instalar las tablas
 */
add_action('after_switch_theme', function () {
	foreach (glob(dirname(__FILE__) . '/config/*.php') as $filename) {
		$filename;
	}
});

add_theme_support('post-thumbnails', array(
	'post',
	'page'
));

show_admin_bar(false);
