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
		echo $this->render('menu/principal', array(
			'home_url' => get_home_url()
		));
	}

	/**
	 * home.php
	 */
	public function getHome() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->render('home', array(
			'posts' => $posts,
			'next_posts_link' => get_next_posts_link(),
			'previous_posts_link' => get_previous_posts_link(),
			'home_url' => get_home_url()
		));

		echo $this->renderPage('base', array(
			'content' => $content
		));
	}

	/**
	 * single.php
	 */
	public function getPost() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		if (isset($posts [0])) {

			$content = $this->render('post', array(
				'post' => $posts [0]
			));

			$sidebar = $this->render('sidebar');

			echo $this->renderPage('base', array(
				'content' => $content,
				'sidebar' => $sidebar
			));
		}
	}

}