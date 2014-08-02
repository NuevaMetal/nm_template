<?php
require_once 'BaseController.php';
/**
 * Controlador principal de la web
 *
 * @author chema
 *
 */
class PageController extends BaseController {

	/**
	 * index.php
	 */
	public function getIndex() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->_renderHome([
			'posts' => $posts
		]);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * home.php
	 */
	public function getHome() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->_renderHome([
			'posts' => $posts
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * category.php
	 */
	public function getCategory() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();
		$current_category = single_cat_title("", false);
		$content = $this->_renderHome([
			'header' => "Búsqueda en la categoría '$current_category'",
			'posts' => $posts
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * tag.php
	 */
	public function getTag() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();
		$current_tag = single_tag_title("", false);
		$content = $this->_renderHome([
			'header' => "Búsqueda por la etiqueta '$current_tag'",
			'posts' => $posts
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * tag.php
	 */
	public function getAuthor() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();
		$author_id = get_the_author_meta('ID');
		$author_name = get_the_author($author_id);
		$user_post_count = count_user_posts($author_id);

		$meta = $this->render('posts/_meta', [
			'user_avatar' => get_avatar($author_id, 36),
			'user_url' => get_the_author_meta('user_url'),
			'display_name' => get_the_author_meta('display_name'),
			'description' => get_the_author_meta('description'),
			'edit_user_link' => ($author_id == wp_get_current_user()->ID) ? get_edit_user_link() : false,
			'' => ''
		]);

		$content = $this->_renderHome([
			'header' => "Entradas de '$author_name' ($user_post_count entradas)",
			'subheader' => $meta,
			'posts' => $posts
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * search.php
	 */
	public function getSearch() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();
		$search_query = get_search_query();

		$content = $this->_renderHome([
			'header' => "Resultado de la búsqueda '$search_query'",
			'posts' => $posts
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * page.php
	 */
	public function getPage() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->_renderPage([
			'post' => $posts [0]
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * single.php
	 */
	public function getPost() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		if (!isset($posts [0])) {
			return $this->renderPage('404');
		}

		$menuPrincipal = $this->render('menu/principal', [
			'home_url' => get_home_url()
		]);

		$meta = $this->render('posts/_meta', [
			'user_avatar' => get_avatar(get_the_author_meta('ID'), 36),
			'user_url' => get_the_author_meta('user_url'),
			'display_name' => get_the_author_meta('display_name'),
			'description' => get_the_author_meta('description'),
			'edit_user_link' => (get_the_author_meta('ID') == wp_get_current_user()->ID) ? get_edit_user_link() : false
		]);

		$content = $this->render('post', [
			'post' => $posts [0],
			'meta' => $meta,
			'comments' => get_comments([
				'post_id' => $posts [0]->ID
			]),
			'edit_post' => get_edit_post_link(),
			'next_post' => get_next_post_link("%link"),
			'previous_post' => get_previous_post_link("%link")
		]);

		$similares = Utils::getPostsSimilares(4);

		$current_user = wp_get_current_user();
		if ($current_user->ID)
			$current_user->url = get_author_posts_url($current_user->ID);
		$sidebar = $this->render('sidebar', [
			'similares' => $similares,
			'hay_similares' => count($similares) > 0,
			'home_url' => get_home_url(),
			'is_admin' => is_admin(),
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'user_avatar' => get_avatar($current_user->ID, 120)
		]);

		return $this->_renderPageBase([
			'content' => $content,
			'sidebar' => $sidebar
		]);
	}

	/**
	 * 404.php
	 */
	public function getError($num) {
		$content = $this->render('error', array(
			'num' => $num
		));
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * page-pattern-primer.php
	 */
	public function showPatternPrimer() {
		$patternPrimerController = new ChesterPatternPrimerController();

		$post = $patternPrimerController->renderPattern('post', array(
			'post' => array(
				'permalink' => 'http://brightonculture.co.uk',
				'title' => 'Post title',
				'time' => '12th Nov 2012',
				'content' => '<p>Sample content</p>'
			)
		));

		$postPreview = $patternPrimerController->renderPattern('home', array(
			'posts' => array(
				'permalink' => 'http://brightonculture.co.uk',
				'title' => 'Post preview title',
				'time' => '12th Nov 2012',
				'content' => '<p>Sample content</p>'
			)
		));

		$patternGroup = $patternPrimerController->renderCustomPatternGroup($post . $postPreview, 'modules/');
		return $patternPrimerController->showPatternPrimer(array(
			'typography',
			'grids'
		), $patternGroup);
	}

}
