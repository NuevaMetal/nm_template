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
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop($dateFormat);
		$post = $posts [0];

		if (!isset($post)) {
			return $this->renderPage('404');
		}
		$currentUser = Utils::getCurrentUser();

		$content = $this->render('post', [
			'post' => $post,
			'current_user' => $currentUser,
			'template_url' => get_template_directory_uri()
		]);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

}
