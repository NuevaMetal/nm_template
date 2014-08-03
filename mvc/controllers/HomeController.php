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
		$catVideos = get_cat_ID('videos');
		$catEntrevistas = get_cat_ID('entrevistas');

		$postsVideos = self::_getPostsByCategory($catVideos, 4);
		$postsEntrevistas = self::_getPostsByCategory($catEntrevistas, 2);

		$bandas = $this->_getSeccion('bandas', 4);

		$videos = $this->_getSeccion('videos', 4);

		$entrevistas = $this->_getSeccion('entrevistas', 2, [
			'reducido' => true
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
	 * Devuelve la sección de bandas
	 *
	 * @param string $seccion
	 *        Nombre de la categoría de la que sacar la sección
	 * @param number $cant
	 *        Cantidad de entradas a obtener
	 * @param array $args
	 *        Lista de parámetros opcionales para la vista de post
	 */
	private function _getSeccion($seccion, $cant = 4, $args = []) {
		$cat = get_cat_ID($seccion);
		$posts = self::_getPostsByCategory($cat, $cant);

		$args ['header'] = $this->render('home/_header', [
			'header' => ucfirst($seccion),
			'url' => get_category_link($cat)
		]);
		$args ['posts'] = $posts;

		return $this->render('home/_posts', $args);
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
