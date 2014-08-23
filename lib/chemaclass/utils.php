<?php
/**
 * Clase con utilidades
 *
 * @author chemaclass
 *
 */
class Utils {

	const TIPO_TAG = 'tag';

	const TIPO_CATEGORY = 'category';

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
					$post = array(
						'permalink' => get_permalink($_p->ID),
						'title' => $_p->post_title,
						'time' => $_p->post_modified,
						'author' => get_user_by('id', $_p->post_author)->display_name,
						'author_link' => get_author_posts_url($_p->post_author)
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

	private static function addThumbnailsToPost($post, $_p) {
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
	 */
	public static function getLangBrowser() {
		return substr($_SERVER ['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}

	/**
	 * Devuelve el idioma actual del navegador en su forma convencional.
	 *
	 * @return string Los 5 primeros chars. Ej: es_ES, en_EN, fr_FR
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
		$currentUser = wp_get_current_user();

		if ($current_user->ID) {
			$currentUser->url = get_author_posts_url($current_user->ID);
		}
		return $currentUser;
	}

	/**
	 * Traducir todo el contenido que tengamos dentro de nuestro i18n
	 * en el fichero post.php
	 *
	 * @param string $content Contenido del post
	 */
	public static function traducirPost($content) {
		$lista = I18n::getFicheroIdioma('post');
		//Sustituimos todos los str del contenido que estén en la lista
		return str_ireplace(array_keys($lista), $lista, $content);
	}

	/**
	 * Reemplaza todos los acentos por sus equivalentes sin ellos
	 *
	 * @param string $string
	 *  	la cadena a sanear
	 *  @param boolean $completo
	 *  	se encarga de eliminar cualquier caracter extraño
	 *
	 * @return string $string Texto saneado
	 */
	public static function sanearString($string, $completo = false) {
	    $string = trim($string);
	    $string = str_replace(
	        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
	        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
	        $string
	    );
	    $string = str_replace(
	        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
	        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
	        $string
	    );
	    $string = str_replace(
	        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
	        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
	        $string
	    );
	    $string = str_replace(
	        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
	        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
	        $string
	    );
	    $string = str_replace(
	        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
	        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
	        $string
	    );
	    $string = str_replace(
	        array('ñ', 'Ñ', 'ç', 'Ç'),
	        array('n', 'N', 'c', 'C',),
	        $string
	    );
	    //Esta parte se encarga de eliminar cualquier caracter extraño
		if($completo){
		    $string = str_replace(
		        array("\\", "¨", "º", "-", "~",
		             "#", "@", "|", "!", "\"",
		             "·", "$", "%", "&", "/",
		             "(", ")", "?", "'", "¡",
		             "¿", "[", "^", "`", "]",
		             "+", "}", "{", "¨", "´",
		             ">", "< ", ";", ",", ":",
		             ".", " "),
		        '',
		        $string
		    );
		}
	    return $string;
	}

}

/**
 * -------------------------------------
 * Funciones de acceso rápido
 * -------------------------------------
 */

/**
 *
 * @param mixed $expression
 * @param string $tag
 */
function dd($expression, $tag = "Tag") {
	echo '' . $tag . '<br>';
	var_dump($expression);
	exit();
}
