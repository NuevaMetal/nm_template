<?php
/**
 * Clase con utilidades
 *
 * @author chemaclass
 *
 *
 */
class Utils {

	const SI = 'si';

	const NO = 'no';

	const ACTIVO = 0;

	const BORRADO = 1;

	const TIPO_TAG = 'tag';

	const TIPO_CATEGORY = 'category';

	const ME_GUSTA = "me-gusta";

	const NOTIFICAR = "notificar";

	const MOSTRAR_MAS = "mostrar-mas";

	const REVISION = "revision";

	const REVISION_BAN = "revision-ban";

	const ANALITICA = "analitica";

	// CATEGORÍAS
	const CATEGORIA_BANDAS = "bandas";

	const CATEGORIA_CRITICAS = "críticas";

	const CATEGORIA_CRONICAS = "crónicas";

	const CATEGORIA_CONCIERTOS = "conciertos";

	const CATEGORIA_ENTREVISTAS = "entrevistas";

	const CATEGORIA_NOTICIAS = "noticias";

	const CATEGORIA_VIDEOS = "vídeos";

	/**
	 * Devuelve una lista con el nombre de los días de la semana
	 *
	 * @return multitype:string
	 */
	public static function getDias() {
		return array(
			"Domingo",
			"Lunes",
			"Martes",
			"Miercoles",
			"Jueves",
			"Viernes",
			"Sábado"
		);
	}

	/**
	 * Devuelve una lista con el nombre de los días de los meses
	 *
	 * @return multitype:string
	 */
	public static function getMeses() {
		return array(
			"Enero",
			"Febrero",
			"Marzo",
			"Abril",
			"Mayo",
			"Junio",
			"Julio",
			"Agosto",
			"Septiembre",
			"Octubre",
			"Noviembre",
			"Diciembre"
		);
	}

	/**
	 * devuelve el contenido de un texto etiquetadoencontrado entre las etiquetas especificadas
	 *
	 * @param string $string
	 *        contenido a buscar
	 * @param string $tagname
	 *        etiqueta a buscar
	 * @return string contenido en dicha etiqueta
	 */
	public static function getTextBetweenTags($string, $tagname) {
		$pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
		preg_match($pattern, $string, $matches);
		return $matches [1];
	}

	/**
	 * Función para info
	 *
	 * @param string $str
	 *        Cadena a pintar
	 */
	public static function info($str) {
		error_log(" INFO - " . $str);
	}

	/**
	 * Función para DEBUG
	 *
	 * @param string $str
	 *        Cadena a pintar
	 */
	public static function debug($str) {
		error_log(" DEBUG - " . $str);
	}

	/**
	 * Devuelve un array con posts similares basásndose en sus tags
	 *
	 * @param number $max
	 *        Número máximo de posts similares que queremos
	 * @return array<post>
	 */
	public static function getPostsSimilares($max = 4) {
		$cont = 0;
		$postsSimilares = array();
		global $post;
		$nextTagThumb = '-1';
		$tags = wp_get_post_tags($post->ID);
		foreach ($tags as $tag) {
			if ($tags) {
				$what_tag = $tags [($nextTagThumb + '1')]->term_id;
				$args = array(
					'tag__in' => array(
						$what_tag
					),
					'post__not_in' => array(
						$post->ID
					),
					'showposts' => 3,
					'ignore_stickies' => 1
				);

				$posts = get_posts($args);

				foreach ($posts as $k => $_p) {
					$title = explode('-', $_p->post_title);
					//$category = get_the_category($_p->ID);
					$post = array(
						'permalink' => get_permalink($_p->ID),
						'title' => $_p->post_title,
						'title_corto' => $title [0],
						'time' => $_p->post_modified,
						'author' => get_user_by('id', $_p->post_author)->display_name,
						'author_link' => get_author_posts_url($_p->post_author),
						//'category' => $category [0]->name
					);
					$post = self::addThumbnailsToPost($post, $_p);

					$postsSimilares [] = $post;
					if (++$cont == $max) {
						break 2;
					}
				}
			}
			wp_reset_query();
			$nextTagThumb = ($nextTagThumb + 1);
		}
		return $postsSimilares;
	}

	public static function addThumbnailsToPost($post, $_p) {
		$sizes = array(
			'thumbnail',
			'medium',
			'large',
			'full'
		);
		foreach ($sizes as $size) {
			$imageObject = wp_get_attachment_image_src(get_post_thumbnail_id($_p->ID), $size);
			if (!empty($imageObject)) {
				$post ['featured_image_url_' . $size] = $imageObject [0];
			}
		}
		return $post;
	}

	/**
	 * Devuelve un array con los roles de un User apartir de su ID
	 *
	 * @param integer $uid
	 *        ID del User
	 * @return array<string> Roles del user
	 */
	public static function getRoleByUserId($uid) {
		global $wpdb;
		$role = $wpdb->get_var("SELECT meta_value
				FROM {$wpdb->usermeta}
		WHERE meta_key = 'wp_capabilities'
		AND user_id = {$uid}");
		if (!$role)
			return 'non-user';
		$rarr = unserialize($role);
		$roles = is_array($rarr) ? array_keys($rarr) : array(
			'non-user'
		);
		return $roles [0];
	}

	/**
	 * Comprueba que un User tenga un rol
	 *
	 * @param integer $user_id
	 *        ID del user
	 * @param array<string> $roles
	 *        Lista de roles a comprobar
	 * @return boolean
	 */
	public static function isUserRol($user_id, $roles) {
		return in_array(self::getRoleByUserId($user_id), $roles);
	}

	/**
	 * Devuelve un array con el total de usuarios y los post que han creado
	 *
	 * @return array
	 */
	public static function getArrayTotalPostPorUser() {
		global $wpdb;
		$select = "SELECT u.ID, u.user_login nombre, COUNT( p.ID ) total
					FROM wp_posts p, wp_users u
					WHERE p.post_author = u.ID
					AND p.post_name NOT LIKE '%revision%'
					AND p.post_type = 'post'
					GROUP BY u.ID
					ORDER BY total DESC";
		$result = $wpdb->query($select);
		return $result;
	}

	/**
	 * Devuelve el id en base al nombre de una tag
	 *
	 * @param string $tag_name
	 * @return number
	 */
	public static function getTagIdbyName($tagName) {
		$tag = get_term_by('name', $tagName, 'post_tag');
		if ($tag) {
			return $tag->term_id;
		} else {
			return 0;
		}
	}

	/**
	 * Devuelve el idioma actual que entiende la web.
	 * Si no tuviera dicha i18n devolvería el idioma inglés por defecto
	 *
	 * @return string
	 */
	public static function getLang() {
		$lang = self::getLangBrowser();
		switch ($lang) {
			case "es" :
				return I18n::LANG_ES;
				break;
			default :
				return I18n::LANG_EN;
		}
	}

	/**
	 * Devuelve el idioma actual del navegador.
	 *
	 * @return string Los dos primeros chars. Ej: es, en, fr
	 *
	 */
	public static function getLangBrowser() {
		return substr($_SERVER ['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}

	/**
	 * Devuelve el idioma actual del navegador en su forma convencional.
	 *
	 * @return string Los 5 primeros chars. Ej: es_ES, en_EN, fr_FR
	 *
	 */
	public static function getLangBrowserFull() {
		$l = static::getLangBrowser();
		return $l . '_' . strtoupper($l);
	}

	/**
	 * Devuelve la instancia del usuario actual.
	 *
	 *
	 * @return user o false en caso de no estar registrado
	 */
	public static function getCurrentUser() {
		$current_user = wp_get_current_user();
		if ($current_user->ID) {
			$current_user->url = get_author_posts_url($current_user->ID);
		}
		return $current_user;
	}

	/**
	 * Traducir todo el contenido que tengamos dentro de nuestro i18n
	 * en el fichero post.php
	 * Y aplicamos el filtro de idioma de forma genérica, aplicado a todos los idiomas,
	 * que se encuentra en el fichero post_format
	 *
	 * @param string $content
	 *        Contenido del post
	 */
	public static function traducirPost($content) {
		$lista = I18n::getFicheroIdioma('post');
		//Sustituimos todos los str del contenido que estén en la lista
		$content = str_ireplace(array_keys($lista), $lista, $content);
		$lista = I18n::getFicheroIdioma('../post_format');
		return str_ireplace(array_keys($lista), $lista, $content);
	}

	/**
	 * Devuelve si a un user le gusta un post
	 *
	 * @param int $post_id
	 *        Identificador del post
	 * @param int $user_id
	 *        Identificador del usuario
	 */
	public static function getSiUserGustaPost($post_id, $user_id) {
		global $wpdb;
		$post = get_post($post_id);
		$leGusta = ( int ) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . "favoritos
				WHERE user_id = %d
	AND post_id = %d
	 AND status = 0;", $user_id, $post_id));
		return $leGusta > 0;
	}

	/**
	 * Devuelve un fragmento del contenido de un post conociendo su ID
	 *
	 * @param integer $post_id
	 *        Identificador del post
	 * @param number $limit
	 *        Limite de palabras a buscar
	 * @return string Extracto obtenido
	 */
	public static function getExcerptById($post_id, $limit = 8) {
		$the_post = get_post($post_id);
		$the_excerpt = $the_post->post_content;
		$excerpt_length = $limit;
		// Quito las etiquetas e img
		$the_excerpt = strip_tags(strip_shortcodes($the_excerpt));
		// Dejo el str en una única línea
		$the_excerpt = trim(preg_replace('/\s\s+/', ' ', $the_excerpt));
		// Sustituyo todos los espacios raros por espacios normales
		$the_excerpt = preg_replace("/[\xc2|\xa0]/", ' ', $the_excerpt);
		// Genero un array a partir del content separando por espacios
		$palabras = explode(' ', $the_excerpt, $excerpt_length + 1);
		$nPalabras = count($palabras);
		// Aplicamos un filtro para quitar determinadas palabras
		$palabras = array_filter($palabras, function ($item) {
			return !self::strContieneAlgunValorArray($item, [
				'youtube'
			]) ? $item : '';
		});
		// Quitamos los valores vacíos
		$palabrasFiltradas = array_filter($palabras, 'strlen');
		$nPalabrasFiltradas = count($palabrasFiltradas);
		// Si hay un distinto número de palabras, significará que se filtraron algunas
		if ($nPalabrasFiltradas != $nPalabras) {
			$excerpt_length -= ($nPalabras - $nPalabrasFiltradas);
			$palabras = $palabrasFiltradas;
		}
		// Si el content fuese más largo que el extracto, concatenar '...'
		if (count($palabras) > $excerpt_length) {
			array_pop($palabras);
			$permalink = get_permalink($post_id);
			$palabras [] = '...';
		}
		// Añado btn de mostrar más
		$palabras [] = '<a href="' . $permalink . '" class="btn btn-default btn-xs">' . I18n::transu('mostrar_mas') . '</a>';
		// Obtengo el extracto del contenido juntando todas las palabras unidas por un espacio
		$the_excerpt = implode(' ', $palabras);
		// Aplicamos negrita a ciertas palabras
		$the_excerpt = preg_replace([
			'/(Género)/i',
			'/(País)/i',
			'/(Álbumes)/i',
			'/(Estado)/i',
			'/(Miembros)/i'
		], '<b>$1</b>', $the_excerpt);
		return $the_excerpt;
	}

	/**
	 * Devuelve true si el la cadena contiene algún valor del array
	 *
	 * @param string $str
	 * @param array $arr
	 * @return boolean
	 *
	 */
	public static function strContieneAlgunValorArray($str, array $arr) {
		foreach ($arr as $a) {
			if (stripos($str, $a)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Devuelve el número de post que le gustan a un usuario o el número de usuarios que le gustan un post
	 *
	 * @param number $user_id
	 *        Identificador del usuario
	 * @param number $post_id
	 *        Identificador del post
	 * @return number Número total
	 */
	public static function getTotalMeGustas($user_id = false, $post_id = false) {
		global $wpdb;
		/*
		 * if (!$user_id) { $current_user = wp_get_current_user(); $user_id = $current_user->ID; }
		 */
		$activo = self::ACTIVO;
		$andUserId = ($user_id) ? ' AND user_id = ' . $user_id : ' ';
		$andPostId = ($post_id) ? ' AND post_id = ' . $post_id : ' ';

		$total = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE 1 = 1
				$andUserId
				$andPostId
				AND status = $activo;");
		return $total;
	}

	/**
	 * Crear clave Nonce para las peticiones AJAX
	 *
	 * @param string $tipoNonceString
	 * @param string $post_id
	 */
	public static function crearNonce($tipoNonceString, $post_id = null) {
		if ($post_id == null) {
			global $post;
			$post_id = $post->ID;
		}
		return wp_create_nonce($tipoNonceString . $post_id);
	}

	/**
	 * Crear clave Nonce para las peticiones AJAX
	 *
	 * @param string $tipoNonceString
	 * @param string $post_id
	 */
	public static function esNonce($nonce, $submit, $post_id = null) {
		if ($post_id == null) {
			global $post;
			$post_id = $post->ID;
		}
		return wp_verify_nonce($nonce, $submit . $post_id);
	}

	/**
	 * Obtener todos los modelos
	 *
	 * @return array<string> lista de todos los modelos
	 */
	private static function getTodosModelos() {
		$DIR_MODELS = path('models');
		$models = array();
		if ($handle = opendir($DIR_MODELS)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && is_file("$DIR_MODELS/$entry")) {
					$class = self::generaNombreModelo($entry);
					if (strlen($class) > 0) {
						$models [] = $class;
					}
				}
			}
			closedir($handle);
		}
		return $models;
	}

	/**
	 * Obtener el nombre del fichero del modelo.
	 *
	 * @param string $entry
	 *        Nombre del fichero con su extensión
	 * @return string nombre de la clase del fichero.
	 */
	private static function generaNombreModelo($entry) {
		list($class, $extension) = explode('.', "$entry");
		return ucfirst($class);
	}

}