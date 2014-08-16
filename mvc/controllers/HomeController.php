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
		$bandas = self::getSeccion('bandas', 4);

		$videos = self::getSeccion('videos', 4);

		$entrevistas = self::getSeccion('entrevistas', 2, [
			'reducido' => true
		]);

		$noticias = self::getSeccion('noticias', 2, [
			'reducido' => true
		]);

		$conciertos = self::getSeccion('conciertos', 2, [
			'reducido' => true
		]);

		$content = $this->render('home', [
			'bandas' => $bandas,
			'videos' => $videos,
			'entrevistas' => $entrevistas,
			'noticias' => $noticias,
			'conciertos' => $conciertos
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Devuelve una sección en base a una categoría o etiqueta
	 *
	 * @param string $seccion
	 *        Nombre de la categoría de la que sacar la sección
	 * @param number $cant
	 *        Cantidad de entradas a obtener
	 * @param array $args
	 *        Lista de parámetros opcionales para la vista de post
	 */
	public static function getSeccion($seccion, $cant = 4, $args = []) {
		$cat = get_cat_ID($seccion);

		$args ['header'] = [
			'header' => ucfirst($seccion),
			'url' => get_category_link($cat)
		];
		$args ['posts'] = self::getPostsByCategory($seccion, $cant);
		$args ['seccion'] = $seccion;
		$args ['cant'] = $cant;
		$args ['tipo'] = 'category';
		$args ['template_url'] = get_template_directory_uri();
		return $args;
	}

	/**
	 * Devuelve una sección en base a una categoría o etiqueta
	 *
	 * @param string $seccion
	 *        Nombre de la categoría de la que sacar la sección
	 * @param number $cant
	 *        Cantidad de entradas a obtener
	 * @param array $args
	 *        Lista de parámetros opcionales para la vista de post
	 */
	public static function getTags($seccion, $cant = 4, $args = []) {
		$args ['header'] = [
			'header' => ucfirst($seccion)
		];
		$args ['posts'] = self::getPostsByTag($seccion, $cant);
		$args ['seccion'] = $seccion;
		$args ['cant'] = $cant;
		$args ['tipo'] = 'tag';
		$args ['template_url'] = get_template_directory_uri();
		return $args;
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
	public static function getPostsByCategory($seccion, $max = 4, $moreQuerySettings = []) {
		return self::getPostsBy('category', $seccion, $max, $moreQuerySettings);
	}

	public static function getPostsByTag($seccion, $max = 4, $moreQuerySettings = []) {
		return self::getPostsBy('tag', $seccion, $max, $moreQuerySettings);
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
	private static function getPostsBy($tipo, $seccion, $max = 4, $moreQuerySettings = []) {
		if ($tipo == 'tag') {
			$catId = Utils::getTagID($seccion);
			$moreQuerySettings ['tag_id'] = "$catId";
		} else {
			$catId = get_cat_ID($seccion);
			$moreQuerySettings ['cat'] = "$catId";
		}
		return ChesterWPCoreDataHelpers::getPosts(false, 'post', $max, [], false, $moreQuerySettings);
	}

}
