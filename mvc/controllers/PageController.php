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

		//$posts_videos = ChesterWPCoreDataHelpers::getPosts($dateFormat = false, $postType = 'post', $numberPostsToFetch = -1, $customFields = array(), $oddOrEven = false);


		$content = $this->render('home', [
			'bandas' => $posts_bandas,
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

		$content = $this->render('post', [
			'post' => $posts [0],
			'next_post' => get_next_post_link("%link"),
			'previous_post' => get_previous_post_link("%link")
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
