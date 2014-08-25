<?php

/**
 * Filtro para poner todos los vídeos de youtube con un ancho de 100%
 */
add_filter('the_content', function ($content) {
	global $post;
	$alto = (in_category('videos', $post)) ? 360 : 300;
	$pattern = '/<iframe width="[0-9]+" height="[0-9]+"/i';
	$replacement = '<iframe width="100%" height="' . $alto . '"';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
});
