<?php

namespace Controllers;

use Libs\Utils;

/**
 * Controlador principal de la web
 *
 * @author chemaclass
 */
class HomeController extends BaseController {

	/**
	 * home.php
	 */
	public function getHome() {
		return $this->renderPage('home', [
			'bandas' => true,
			'videos' => true,
			'conciertos' => true,
			'cronicas' => true,
			'entrevistas' => true,
			'criticas' => true,
			'noticias' => true
		]);
	}

	/**
	 * Devuelve una sección en base a una categoría o etiqueta
	 *
	 * @param string $seccion
	 *        	Nombre de la categoría de la que sacar la sección
	 * @param number $cant
	 *        	Cantidad de entradas a obtener
	 * @param array $args
	 *        	Lista de parámetros opcionales para la vista de post
	 */
	public static function getSeccion($seccion, $cant = 4, $args = []) {
		$args['imagen'] = strtolower($seccion);
		$args['seccion'] = strtolower($seccion);
		$args['a_buscar'] = strtolower($seccion);
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
		$args['url'] = $url;
		$args['cant'] = $cant;
		$args['tipo'] = Utils::TIPO_CATEGORY;
		$args['template_url'] = get_template_directory_uri();
		$args['posts'] = self::getPostsByCategory($seccion, $cant, []);
		return $args;
	}

	/**
	 * Devuelve una sección en base a una categoría o etiqueta
	 *
	 * @param string $seccion
	 *        	Nombre de la categoría de la que sacar la sección
	 * @param number $cant
	 *        	Cantidad de entradas a obtener
	 * @param array $args
	 *        	Lista de parámetros opcionales para la vista de post
	 */
	public static function getBusqueda($aBuscar, $cant = 4, $args = []) {
		$args['imagen'] = 'NM_avatar_2';
		$args['seccion'] = 'busqueda-posts';
		$args['a_buscar'] = $aBuscar;
		$args['header'] = I18n::trans('resultado_busqueda', [
			'que' => $aBuscar
		]);
		$args['cant'] = $cant;
		$args['tipo'] = Utils::TIPO_SEARCH;
		$args['template_url'] = get_template_directory_uri();
		$args['posts'] = self::getPostsBySearch($aBuscar, $cant, []);
		$args['total_posts'] = count($args['posts']);
		return $args;
	}

	/**
	 *
	 * @param unknown $aBuscar
	 * @param number $cant
	 * @param unknown $args
	 * @return unknown
	 */
	public static function getAutor($aBuscar, $cant = 4, $args = []) {
		$args['imagen'] = 'noimage';
		$args['seccion'] = 'autor';
		$args['a_buscar'] = $aBuscar;
		$args['cant'] = $cant;
		$args['tipo'] = Utils::TIPO_AUTHOR;
		$args['template_url'] = get_template_directory_uri();
		$args['posts'] = self::getPostsByAuthor($aBuscar, $cant, []);
		return $args;
	}

	/**
	 * Devuelve un número determinado de posts en base al ID de su categoría
	 *
	 * @param number $catId
	 *        	ID de la categoría
	 * @param number $max
	 *        	número máximo de posts a devolver
	 * @return multitype:
	 */
	public static function getPostsByCategory($seccion, $max = 4, $moreQuerySettings = []) {
		return self::getPostsBy(Utils::TIPO_CATEGORY, $seccion, $max, $moreQuerySettings);
	}
	public static function getPostsByTag($seccion, $max = 4, $moreQuerySettings = []) {
		return self::getPostsBy(Utils::TIPO_TAG, $seccion, $max, $moreQuerySettings);
	}
	public static function getPostsBySearch($aBuscar, $max = 4, $moreQuerySettings = []) {
		return self::getPostsBy(Utils::TIPO_SEARCH, $aBuscar, $max, $moreQuerySettings);
	}
	public static function getPostsByAuthor($autor_id, $max = 4, $moreQuerySettings = []) {
		return self::getPostsBy(Utils::TIPO_AUTHOR, $autor_id, $max, $moreQuerySettings);
	}

	/**
	 * Devuelve un número determinado de posts en base al ID de su categoría
	 *
	 * @param number $catId
	 *        	ID de la categoría
	 * @param number $max
	 *        	número máximo de posts a devolver
	 * @return multitype:
	 */
	private static function getPostsBy($tipo, $seccion, $max = 4, $moreQuerySettings = []) {
		if ($tipo == Utils::TIPO_TAG) {
			$tagId = Utils::getTagIdbyName($seccion);
			$moreQuerySettings['tag_id'] = "$tagId";
		} elseif ($tipo == Utils::TIPO_CATEGORY) {
			$catId = get_cat_ID($seccion);
			$moreQuerySettings['cat'] = "$catId";
		} elseif ($tipo == Utils::TIPO_SEARCH) {
			$aBuscar = $seccion;
			// Comprobamos si tiene ':' para indicar una búsqueda especial por categoría
			if (strpos($seccion, ':') !== false) {
				$_explode = explode(':', $seccion);
				// Eliminamos espacios en blanco
				$explode = array_map('trim', $_explode);
				list($categoriaNombre, $aBuscar) = $explode;
				// Si no tiene una s o la s no la tiene al final se la ponemos (al final)
				// Por el motivo de que todas las categorías son en plural.
				if (! strpos($categoriaNombre, 's') || strpos($categoriaNombre, 's') != strlen($categoriaNombre) - 1) {
					$categoriaNombre .= 's';
				}
				$moreQuerySettings['category_name'] = $categoriaNombre;
			}
			$moreQuerySettings['s'] = "$aBuscar";
		} elseif ($tipo == Utils::TIPO_AUTHOR) {
			$moreQuerySettings['author'] = $seccion;
		}
		return self::getPosts(false, 'post', $max, [], false, $moreQuerySettings);
	}
}
