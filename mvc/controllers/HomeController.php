<?php
require_once 'BaseController.php';
/**
 * Controlador principal de la web
 *
 * @author chemaclass
 *
 */
class HomeController extends BaseController {

	/**
	 * home.php
	 *
	 * @deprecated por nueva maquetación
	 * @see HomeController::getHomePorSecciones()
	 */
	public function getHome() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->_renderBusqueda([
			'posts' => $posts
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * home.php
	 */
	public function getHomePorSecciones() {
		$catBandas = get_cat_ID('bandas');
		$catVideos = get_cat_ID('videos');
		$catEntrevistas = get_cat_ID('entrevistas');

		$postsBandas = self::_getPostsByCategory($catBandas, 4);
		$postsVideos = self::_getPostsByCategory($catVideos, 4);
		$postsEntrevistas = self::_getPostsByCategory($catEntrevistas, 4);

		$bandas = $this->render('home/_posts', [
			'header' => 'Últimas entradas <small>Biografías, discografías y promoción</small>',
			'posts' => $postsBandas
		]);

		$videos = $this->render('home/_posts', [
			'header' => 'Últimos vídeos <small>Videoclips, singles, directos, covers</small>',
			'posts' => $postsVideos
		]);

		$entrevistas = $this->render('home/_posts', [
			'header' => 'Últimas entrevistas <small></small>',
			'posts' => $postsEntrevistas
		]);

		$content = $this->render('home', [
			'bandas' => $bandas,
			'videos' => $videos,
			'entrevistas' => $entrevistas
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Devuelve un número determinado de posts en base al ID de su categoría
	 *
	 * @param number $catId
	 *        ID de la categoría
	 * @param number $max
	 *        número máximo de posts a devolver
	 * @return multitype:
	 */
	private static function _getPostsByCategory($catId, $max = 4) {
		$moreQuerySettings = [
			'cat' => "$catId"
		];
		return ChesterWPCoreDataHelpers::getPosts(false, 'post', $max, [], false, $moreQuerySettings);
	}

}
