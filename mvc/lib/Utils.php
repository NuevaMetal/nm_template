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

	const HOY = 'hoy';

	const AYER = 'ayer';

	const TIPO_TAG = 'tag';

	const TIPO_CATEGORY = 'category';

	const TIPO_SEARCH = 'search';

	const TIPO_AUTHOR = 'author';

	const TIPO_AUTHOR_FAV = 'author-fav';

	const ANALITICA = "analitica";

	// CATEGORÍAS
	const CATEGORIA_BANDAS = "bandas";

	const CATEGORIA_CRITICAS = "criticas";

	const CATEGORIA_CRONICAS = "cronicas";

	const CATEGORIA_CONCIERTOS = "conciertos";

	const CATEGORIA_ENTREVISTAS = "entrevistas";

	const CATEGORIA_NOTICIAS = "noticias";

	const CATEGORIA_VIDEOS = "videos";

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
	 * Función para las Excepciones
	 *
	 * @param string $str
	 *        Cadena a pintar
	 */
	public static function exception($str) {
		error_log(" EXCEPTION - " . $str);
	}

	/**
	 * Devuelve un array con los roles de un User apartir de su ID
	 *
	 * @param integer $uid
	 *        ID del User
	 * @return array<string> Roles del user
	 * @deprecated Por nueva metodología
	 * @see User::getRol()
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
	 * @return User
	 */
	public static function getCurrentUser() {
		$user = wp_get_current_user();
		if ($user->ID) {
			$user = User::find($user->ID);
		}
		return $user;
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
	 * Devuelve un número de palabras de un string
	 *
	 * @param string $str
	 *        Cadena en la que buscar las palabras
	 * @param number $cant
	 *        Cantidad de palabras que queremos obtener
	 * @return string Cadena 'limitada' al número de palabras especificadas
	 */
	public static function getPalabrasByStr($str, $cant = 8, $separador = ' ') {
		// Genero un array a partir del content separando por espacios
		$palabras = explode($separador, $str, $cant + 1);
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
			$cant -= ($nPalabras - $nPalabrasFiltradas);
			$palabras = $palabrasFiltradas;
		}
		// Si el content fuese más largo que el extracto, concatenar '...'
		if (count($palabras) > $cant) {
			array_pop($palabras);
			$permalink = get_permalink($post_id);
			$palabras [] = '...';
		}
		// Obtengo el extracto del contenido juntando todas las palabras unidas por un espacio
		return implode($separador, $palabras);
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
	 * Quitar palabras innecesarias
	 *
	 * @param unknown $titulo
	 */
	public static function quitarPalabrasInnecesariasDeSeccion($titulo) {
		$palabrasAQuitar = [
			'official',
			'video',
			'clip',
			'(',
			')'
		];
		foreach ($palabrasAQuitar as $p) {
			$titulo = str_ireplace($p, '', $titulo);
		}
		return $titulo;
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

	/**
	 * Traduce una lista de valores con su correspondiente I18n
	 *
	 * @param array $lista
	 *        Lista a traducir
	 * @return array Lista traducida
	 */
	public static function traducirLista($lista) {
		$result = [];
		foreach ($lista as $l) {
			$result [] = I18n::trans($l);
		}
		return $result;
	}

	/**
	 * Traduce una lista de valores con su correspondiente I18n con ucfirst
	 *
	 * @param array $lista
	 *        Lista a traducir
	 * @return array Lista traducida
	 */
	public static function traducirListaU($lista) {
		$result = [];
		foreach ($lista as $l) {
			$result [] = I18n::transu($l);
		}
		return $result;
	}

	/**
	 * Convierte un array en un object
	 *
	 * @param array $array
	 */
	public static function arrayToObject(array $array) {
		$object = new stdClass();
		foreach ($array as $key => $value) {
			$object->{$key} = $value;
		}
		return $object;
	}

}