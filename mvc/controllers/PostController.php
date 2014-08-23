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
		$dateFormat = 'l, d F Y';
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop($dateFormat);
		$post = $posts [0];

		if (!isset($post)) {
			return $this->renderPage('404');
		}
		$current_user = Utils::getCurrentUser();

		$author_id = get_the_author_meta('ID');
		$edit_user_link = ($author_id == $current_user->ID) ? get_edit_user_link() : false;

		$comment_form = $this->_getComentForm($post ['ID']);
		$comments = $this->_getComments($post ['ID']);

		$user_rol = Utils::getRoleByUserId($author_id);
		$user_rol = I18n::trans($user_rol);

		$argsContent = [
			'has_comments' => count($comments) > 0 ? true : false,
			'comment_form' => $comment_form,
			'comments' => $comments,
			'current_user' => $current_user,
			'display_name' => get_the_author_meta('display_name'),
			'description' => get_the_author_meta('description'),
			'edit_post' => get_edit_post_link(),
			'edit_user_link' => $edit_user_link,
			'me_gusta' => Utils::getSiUserGustaPost($post ['ID'], $current_user->ID),
			//'next_post' => get_next_post_link("%link"),
			'post' => $post,
			//'previous_post' => get_previous_post_link("%link"),
			'user_avatar' => get_avatar($author_id, 36),
			'user_posts_url' => get_author_posts_url($author_id),
			'user_url' => get_the_author_meta('url'),
			'user_rol' => ucfirst($user_rol),
			'template_url' => get_template_directory_uri(),
		];

		$content = $this->render('post', $argsContent);
		return $this->_renderPageBase([
			'content' => $content,
			'sidebar' => $this->_getSidebar($post ['ID'], $current_user->ID)
		]);
	}



	/**
	 * Devuelve el form para un nuevo comentario
	 *
	 * @return View
	 */
	private function _getComentForm($postId) {
		ob_start();
		$params = [
			'comment_notes_after' => '',
			'author' => '<p class="comment-form-author">' . '<label for="author">' . __('Your Name') . '</label>
					<input id="author" name="author" type="text"  value="Your First and Last Name" size="30"' . $aria_req . ' /></p>',
			'comment_field' => '<div class="form-group comment-form-comment">
			            <label for="comment">' . _x('Comment', 'noun') . '</label>
			            <textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
			        </div>'
		];
		comment_form($params, $postId);
		$comment_form = ob_get_clean();
		$comment_form = str_replace('class="comment-form"', 'class="comment-form"', $comment_form);
		$comment_form = str_replace('id="submit"', 'class="btn btn-primary"', $comment_form);
		return $comment_form;
	}

	/**
	 * Devuelve una lista de comentarios
	 */
	private function _getComments($postId) {
		$args_comments = array(
			'post_id' => $postId,
			'orderby' => 'comment_date_gmt',
			'status' => 'approve'
		);
		return get_comments($args_comments, $postId);
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
			'user_id' => $user_id,
			'template_url' => get_template_directory_uri()
		]);
	}

}
