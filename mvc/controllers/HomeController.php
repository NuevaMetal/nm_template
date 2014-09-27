<?php
// namespace Controllers\HomeController;
// use Controllers\BaseController;
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
		$bandas = self::getSeccion(Post::CATEGORY_BANDAS, 4);

		$videos = self::getSeccion(Post::CATEGORY_VIDEOS, 4);

		$entrevistas = self::getSeccion(Post::CATEGORY_ENTREVISTAS, 2, [
			'reducido' => true
		]);

		$noticias = self::getSeccion(Post::CATEGORY_NOTICIAS, 2, [
			'reducido' => true
		]);

		$conciertos = self::getSeccion(Post::CATEGORY_CONCIERTOS, 2, [
			'reducido' => true
		]);

		$criticas = self::getSeccion(Post::CATEGORY_CRITICAS, 2, [
			'reducido' => true
		]);

		$cronicas = self::getSeccion(Post::CATEGORY_CRONICAS, 2, [
			'reducido' => true
		]);

		$content = $this->_render('home', [
			Post::CATEGORY_BANDAS => $bandas,
			Post::CATEGORY_VIDEOS => $videos,
			Post::CATEGORY_CRITICAS => $criticas,
			Post::CATEGORY_CRONICAS => $cronicas,
			Post::CATEGORY_NOTICIAS => $noticias,
			Post::CATEGORY_CONCIERTOS => $conciertos,
			Post::CATEGORY_ENTREVISTAS => $entrevistas
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
	public static function getSeccion($seccion, $cant = 4, $args = [], $otherParams = []) {
		$args ['imagen'] = strtolower($seccion);
		$args ['seccion'] = strtolower($seccion);
		$args ['a_buscar'] = strtolower($seccion);
		$url = esc_url(get_category_link(get_cat_ID($seccion)));
		// Quitamos la ruta absoluta http://nuevametal.com
		$url = Utils::quitarUrlAbsoluta($url);
		// Quitamos las posibles dobles barras que salen para algunas categorías
		// como por ejemplo: /category//conciertos
		$url = preg_replace([
			'@/{1,}@i'
		], [
			'/'
		], $url);
		$args ['url'] = $url;
		$args ['cant'] = $cant;
		$args ['tipo'] = Utils::TIPO_CATEGORY;
		$args ['template_url'] = get_template_directory_uri();
		$args ['posts'] = self::getPostsByCategory($seccion, $cant, [], $otherParams);
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
	public static function getTags($seccion, $cant = 4, $args = [], $otherParams = []) {
		$args ['imagen'] = 'noimage';
		$args ['seccion'] = 'busqueda';
		$args ['a_buscar'] = strtolower($seccion);
		$args ['header'] = I18n::trans('resultado_tag', [
			'que' => $seccion
		]);
		$args ['url'] = get_tag_link($cat);
		$args ['cant'] = $cant;
		$args ['tipo'] = Utils::TIPO_TAG;
		$args ['template_url'] = get_template_directory_uri();
		$args ['posts'] = self::getPostsByTag($seccion, $cant, [], $otherParams);
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
	public static function getBusqueda($aBuscar, $cant = 4, $args = [], $otherParams = []) {
		$args ['imagen'] = 'noimage';
		$args ['seccion'] = 'busqueda';
		$args ['a_buscar'] = $aBuscar;
		$args ['header'] = I18n::trans('resultado_busqueda', [
			'que' => $aBuscar
		]);
		$args ['cant'] = $cant;
		$args ['tipo'] = Utils::TIPO_SEARCH;
		$args ['template_url'] = get_template_directory_uri();
		$args ['posts'] = self::getPostsBySearch($aBuscar, $cant, [], $otherParams);
		return $args;
	}

	public static function getAutor($aBuscar, $cant = 4, $args = [], $otherParams = []) {
		$args ['imagen'] = 'noimage';
		$args ['seccion'] = 'autor';
		$args ['a_buscar'] = $aBuscar;
		$args ['cant'] = $cant;
		$args ['tipo'] = Utils::TIPO_AUTHOR;
		$args ['template_url'] = get_template_directory_uri();
		$args ['posts'] = self::getPostsByAuthor($aBuscar, $cant, [], $otherParams);
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
	public static function getPostsByCategory($seccion, $max = 4, $moreQuerySettings = [], $otherParams = []) {
		return self::getPostsBy(Utils::TIPO_CATEGORY, $seccion, $max, $moreQuerySettings, $otherParams);
	}

	public static function getPostsByTag($seccion, $max = 4, $moreQuerySettings = [], $otherParams = []) {
		return self::getPostsBy(Utils::TIPO_TAG, $seccion, $max, $moreQuerySettings, $otherParams);
	}

	public static function getPostsBySearch($aBuscar, $max = 4, $moreQuerySettings = [], $otherParams = []) {
		return self::getPostsBy(Utils::TIPO_SEARCH, $aBuscar, $max, $moreQuerySettings, $otherParams);
	}

	public static function getPostsByAuthor($autor_id, $max = 4, $moreQuerySettings = [], $otherParams = []) {
		return self::getPostsBy(Utils::TIPO_AUTHOR, $autor_id, $max, $moreQuerySettings, $otherParams);
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
	private static function getPostsBy($tipo, $seccion, $max = 4, $moreQuerySettings = [], $otherParams = []) {
		if ($tipo == Utils::TIPO_TAG) {
			$tagId = Utils::getTagIdbyName($seccion);
			$moreQuerySettings ['tag_id'] = "$tagId";
		} elseif ($tipo == Utils::TIPO_CATEGORY) {
			$catId = get_cat_ID($seccion);
			$moreQuerySettings ['cat'] = "$catId";
		} elseif ($tipo == Utils::TIPO_SEARCH) {
			$moreQuerySettings ['s'] = "$seccion";
		} elseif ($tipo == Utils::TIPO_AUTHOR) {
			$moreQuerySettings ['author'] = $seccion;
		}
		return ChesterWPCoreDataHelpers::getPosts(false, 'post', $max, [], false, $moreQuerySettings, $otherParams);
	}

}
