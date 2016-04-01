<?php

namespace Controllers;

use Libs\Utils;
use Models\Post;

/**
 * Controlador principal de la web
 *
 * @author chemaclass
 */
class HomeController extends BaseController {

    const NUM_POST_POR_SECCION = 8;

    const NUM_BANDAS = 8;
    const NUM_VIDEOS = 4;
    const NUM_ENTREVISTAS = 4;
    const NUM_CONCIERTOS = 4;
    const NUM_NOTICIAS = 4;
    const NUM_CRONICAS = 2;
    const NUM_CRITICAS = 2;

    /**
     * home.php
     */
    public function getHome()
    {
        $catActivas = [
            Post::CATEGORY_BANDAS => self::NUM_BANDAS,
            Post::CATEGORY_VIDEOS => self::NUM_VIDEOS,
            Post::CATEGORY_ENTREVISTAS => self::NUM_ENTREVISTAS,
            Post::CATEGORY_CONCIERTOS => self::NUM_CONCIERTOS,
            Post::CATEGORY_NOTICIAS=> self::NUM_NOTICIAS,
            //Post::CATEGORY_CRONICAS => self::NUM_CRONICAS,
            // Post::CATEGORY_CRITICAS=> self::NUM_CRITICAS,
        ];
        $args = [];
        foreach ($catActivas as $categoria => $cantidad) {
            $args[$categoria] = [
                'seccion' => $categoria,
                'url' => '/category/' . $categoria,
                'cantidad_total' => Post::getCantidadTotalEnCategoria($categoria),
                'cantidad_limit' => $cantidad
            ];
        }
        return $this->renderPage('home', $args);
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
	public static function getSeccion($seccion, $cant = self::NUM_POST_POR_SECCION, $args = []) {
		$seccionLower = strtolower($seccion);
		$args['imagen'] = $seccionLower;
		$args['seccion'] = $seccionLower;
		$args['a_buscar'] = $seccionLower;
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
		$TRANSIENT_HOME_SECCION = 'home-seccion-' . $seccion;
		// Get any existing copy of our transient data
		if (false === ($posts = get_transient($TRANSIENT_HOME_SECCION))) {
      		// If wasn't there, so regenerate the data and save the transient
      		$posts = self::getPostsByCategory($seccion, $cant, []);
      		set_transient($TRANSIENT_HOME_SECCION, $posts, DAY_IN_SECONDS);
		}
		$args['posts'] = $posts;
		$args['cantidad_total'] = Post::getCantidadTotalEnCategoria($seccionLower);
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
	public static function getBusqueda($aBuscar, $cant = self::NUM_POST_POR_SECCION, $args = []) {
		$args['seccion'] = 'busqueda-posts';
		$args['a_buscar'] = $aBuscar;
		$args['que'] = $aBuscar;
		$args['cant'] = $cant;
		$args['tipo'] = Utils::TIPO_SEARCH;
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
	public static function getAutor($aBuscar, $cant = self::NUM_POST_POR_SECCION, $args = []) {
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
	public static function getPostsByCategory($seccion, $max = self::NUM_POST_POR_SECCION, $moreQuerySettings = []) {
		return self::getPostsBy(Utils::TIPO_CATEGORY, $seccion, $max, $moreQuerySettings);
	}
	public static function getPostsByTag($seccion, $max = self::NUM_POST_POR_SECCION, $moreQuerySettings = []) {
		return self::getPostsBy(Utils::TIPO_TAG, $seccion, $max, $moreQuerySettings);
	}
	public static function getPostsBySearch($aBuscar, $max = self::NUM_POST_POR_SECCION, $moreQuerySettings = []) {
		return self::getPostsBy(Utils::TIPO_SEARCH, $aBuscar, $max, $moreQuerySettings);
	}
	public static function getPostsByAuthor($autor_id, $max = self::NUM_POST_POR_SECCION, $moreQuerySettings = []) {
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
	private static function getPostsBy($tipo, $seccion, $max = self::NUM_POST_POR_SECCION, $moreQuerySettings = []) {
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
