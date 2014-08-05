<?php
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

		if (!isset($posts [0])) {
			return $this->renderPage('404');
		}
		$current_user = wp_get_current_user();
		$content = $this->render('post', [
			'post' => $posts [0],
			'meta' => $this->_getMeta(),
			'comments' => Utils::getDisqusEmbed('nuevametalweb'),
			'edit_post' => get_edit_post_link(),
			'next_post' => get_next_post_link("%link"),
			'previous_post' => get_previous_post_link("%link")
		]);
		return $this->_renderPageBase([
			'content' => $content,
			'sidebar' => $this->_getSidebar($posts [0]->ID, $current_user->ID)
		]);
	}

	/**
	 * Devuelve la vista de los metadatos del post
	 */
	private function _getMeta() {
		return $this->render('posts/_meta', [
			'user_avatar' => get_avatar(get_the_author_meta('ID'), 36),
			'user_url' => get_the_author_meta('user_url'),
			'display_name' => get_the_author_meta('display_name'),
			'description' => get_the_author_meta('description'),
			'edit_user_link' => (get_the_author_meta('ID') == wp_get_current_user()->ID) ? get_edit_user_link() : false
		]);
	}

	/**
	 * Devuelve la vista del sidebar
	 */
	private function _getSidebar($post_id = null, $user_id = null) {
		$similares = Utils::getPostsSimilares(4);

		$current_user = wp_get_current_user();

		if ($current_user->ID) {
			$current_user->url = get_author_posts_url($current_user->ID);
		}

		return $this->render('sidebar', [
			'similares' => $similares,
			'hay_similares' => count($similares) > 0,
			'home_url' => get_home_url(),
			'is_admin' => is_admin(),
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'user_avatar' => get_avatar($current_user->ID, 120),
			'post_id' => $post_id,
			'user_id' => $user_id
		]);
	}

}
