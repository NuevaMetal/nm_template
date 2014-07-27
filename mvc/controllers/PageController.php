<?php
/**
 * Controlador principal de la web
 *
 * @author chema
 *
 */
class PageController extends ChesterBaseController {

	public function PageController() {
		parent::__construct();
		/*
		 * echo $this->render('menu/principal', array( 'home_url' => get_home_url() ));
		 */
	}

	/**
	 * home.php
	 */
	public function getHome() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$menuPrincipal = $this->render('menu/principal', [
			'home_url' => get_home_url()
		]);

		$content = $this->render('home', [
			'posts' => $posts,
			'next_posts_link' => get_next_posts_link(),
			'previous_posts_link' => get_previous_posts_link()
		]);

		return $this->renderPage('base', [
			'menuPrincipal' => $menuPrincipal,
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

		$content = $this->render('post', [
			'post' => $posts [0]
		]);

		$sidebar = $this->render('sidebar');

		return $this->renderPage('base', [
			'menuPrincipal' => $menuPrincipal,
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
		return $this->renderPage('base', array(
			'content' => $content
		));
	}

}