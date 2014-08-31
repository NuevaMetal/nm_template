<?php

/**
 * Filtro para poner estilo de bootstrap al form para nuevo comentario
 */
//add_filter( 'comment_form_default_fields', 'bootstrap3_comment_form_fields' );
function bootstrap3_comment_form_fields($fields) {
	$commenter = wp_get_current_commenter();

	$req = get_option('require_name_email');
	$aria_req = ($req ? " aria-required='true'" : '');
	$html5 = current_theme_supports('html5', 'comment-form') ? 1 : 0;

	$fields = array(
		'author' => '<div class="form-group comment-form-author">' . '<label for="author">' . __('Name') . ($req ? ' <span class="required">*</span>' : '') . '</label> ' . '<input class="form-control" id="author" name="author" type="text" value="' . esc_attr($commenter ['comment_author']) . '" size="30"' . $aria_req . ' /></div>',
		'email' => '<div class="form-group comment-form-email"><label for="email">' . __('Email') . ($req ? ' <span class="required">*</span>' : '') . '</label> ' . '<input class="form-control" id="email" name="email" ' . ($html5 ? 'type="email"' : 'type="text"') . ' value="' . esc_attr($commenter ['comment_author_email']) . '" size="30"' . $aria_req . ' /></div>',
		'url' => '<div class="form-group comment-form-url"><label for="url">' . __('Website') . '</label> ' . '<input class="form-control" id="url" name="url" ' . ($html5 ? 'type="url"' : 'type="text"') . ' value="' . esc_attr($commenter ['comment_author_url']) . '" size="30" /></div>'
	);

	return $fields;
}

/**
 * Filtro para poner todos los vídeos de youtube con un ancho de 100%
 * y limpiar las entradas antes de ser mostradas
 */
add_filter('the_content', function ($content) {
	$content = Html::ajustarVideos($content);
	$content = Html::quitarAtributos($content, [
		'iframe',
		'img',
		'a'
	]);
	return $content;
});

/**
 * Runs just before a post or page is updated.
 * Action function arguments: post or page ID.
 */
add_filter('content_save_pre', function ($content) {
	$content = Html::ajustarVideos($content);
	$content = Html::quitarAtributos($content, [
		'iframe',
		'img',
		'a'
	]);
	return $content;
});
