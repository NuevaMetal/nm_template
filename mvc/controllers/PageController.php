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
	}

	/**
	 * Pintar la plantilla base con los menus
	 *
	 * @param array $args
	 *        Lista de parámetros a pasar a la plantilla base
	 */
	private function _renderBase($args = []) {
		$menuPrincipal = $this->render('menu/principal', [
			'home_url' => get_home_url()
		]);
		$menuFooter = $this->render('menu/footer', [
			'home_url' => get_home_url()
		]);
		$args ['menuPrincipal'] = $menuPrincipal;
		$args ['menuFooter'] = $menuFooter;

		return $this->renderPage('base', $args);
	}

	/**
	 * home.php
	 */
	public function getHome() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->render('home', [
			'posts' => $posts,
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

		$content = $this->render('post', [
			'post' => $posts [0]
		]);

		$sidebar = $this->render('sidebar');

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

}
