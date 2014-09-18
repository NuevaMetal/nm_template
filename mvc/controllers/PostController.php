<?php

// namespace Controllers\PostController;
// use Controllers\BaseController;
require_once 'BaseController.php';

/**
 * Controlador de los post de la web
 *
 * @author chemaclass
 *
 */
class PostController extends BaseController {

	/**
	 * single.php
	 */
	public function getPost() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();
		$post = $posts [0];

		if (!isset($post)) {
			return $this->renderPage('404');
		}
		$content = $this->render('post', [
			'post' => $post,
			'template_url' => get_template_directory_uri(),
			'current_user' => Utils::getCurrentUser()
		]);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

}
