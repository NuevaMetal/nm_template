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

		$content = $this->render('index', array(
			'posts' => $posts,
			'next_posts_link' => get_next_posts_link(),
			'previous_posts_link' => get_previous_posts_link()
		));

		return $this->_renderBase([
			'content' => $content
		]);
	}

	/**
	 * home.php
	 */
	public function getHome() {
		$posts_bandas = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		/*
		 $catBandasId = get_cat_ID('bandass');
		$posts_bandas = get_posts([
				'category' => $catBandasId
				]);
		foreach($posts_bandas as $p){
		echo "$p->title";
		}*/
		$content = $this->render('home', [
			'posts' => $posts_bandas,
			//'videos' => $posts_videos
			'next_posts_link' => get_next_posts_link(),
			'previous_posts_link' => get_previous_posts_link()
		]);
		return $this->_renderBase([
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
			'user_avatar' => get_avatar(get_the_author_meta('ID'), 32),
			'user_url' => get_the_author_meta('user_url'),
			'display_name' => get_the_author_meta('display_name'),
			'description' => get_the_author_meta('description')
		]);

		$content = $this->render('post', [
			'post' => $posts [0],
			'meta' => $meta,
			'next_post' => get_next_post_link("%link"),
			'previous_post' => get_previous_post_link("%link")
		]);

		$current_user = wp_get_current_user();

		$sidebar = $this->render('sidebar', [
			'home_url' => get_home_url(),
			'is_admin' => is_admin(),
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'user_avatar' => get_avatar($current_user->ID, 120)
		]);

		return $this->_renderBase([
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
		return $this->_renderBase([
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
