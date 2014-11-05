<?php
/**
 * Clase con utilidades
 *
 * @author chemaclass
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

	const TIPO_BUSCAR_USUARIOS = 'buscar-usuarios';
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
	 *        	contenido a buscar
	 * @param string $tagname
	 *        	etiqueta a buscar
	 * @return string contenido en dicha etiqueta
	 */
	public static function getTextBetweenTags($string, $tagname) {
		$pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
		preg_match($pattern, $string, $matches);
		return $matches[1];
	}

	/**
	 * Función para info
	 *
	 * @param string $str
	 *        	Cadena a pintar
	 */
	public static function info($str) {
		error_log(" INFO - " . $str);
	}

	/**
	 * Función para DEBUG
	 *
	 * @param string $str
	 *        	Cadena a pintar
	 */
	public static function debug($str) {
		error_log(" DEBUG - " . $str);
	}

	/**
	 * Función para las Excepciones
	 *
	 * @param string $str
	 *        	Cadena a pintar
	 */
	public static function exception($str) {
		error_log(" EXCEPTION - " . $str);
	}

	/**
	 * Devuelve un array con los roles de un User apartir de su ID
	 *
	 * @param integer $uid
	 *        	ID del User
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
		if (! $role)
			return 'non-user';
		$rarr = unserialize($role);
		$roles = is_array($rarr) ? array_keys($rarr) : array(
			'non-user'
		);
		return $roles[0];
	}

	/**
	 * Comprueba que un User tenga un rol
	 *
	 * @param integer $user_id
	 *        	ID del user
	 * @param array<string> $roles
	 *        	Lista de roles a comprobar
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
		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
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
	 * Devuelve la instancia del usuario actual o null en caso de no estar logueado.
	 *
	 * @return User
	 */
	public static function getCurrentUser() {
		$user = wp_get_current_user();
		if ($user->ID) {
			return User::find($user->ID);
		}
		return null;
	}

	/**
	 * Devuelve un número de palabras de un string
	 *
	 * @param string $str
	 *        	Cadena en la que buscar las palabras
	 * @param number $cant
	 *        	Cantidad de palabras que queremos obtener
	 * @return string Cadena 'limitada' al número de palabras especificadas
	 */
	public static function cortarStr($str, $cant, $separador = ' ') {
		$palabras = explode($separador, $str, $cant + 1);
		$nPalabras = count($palabras);
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
			$palabras[] = '...';
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
						$models[] = $class;
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
	 *        	Nombre del fichero con su extensión
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
	 *        	Lista a traducir
	 * @return array Lista traducida
	 */
	public static function traducirLista($lista) {
		$result = [];
		foreach ($lista as $l) {
			$result[] = I18n::trans($l);
		}
		return $result;
	}

	/**
	 * Traduce una lista de valores con su correspondiente I18n con ucfirst
	 *
	 * @param array $lista
	 *        	Lista a traducir
	 * @return array Lista traducida
	 */
	public static function traducirListaU($lista) {
		$result = [];
		foreach ($lista as $l) {
			$result[] = I18n::transu($l);
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

	/**
	 * Devuelve la URL pasada por parámetro
	 *
	 * @param string $url
	 *        	URL a quitar la ruta base: get_home_url()
	 * @return string URL sin la ruta base
	 */
	public static function quitarUrlAbsoluta($url) {
		return str_replace(get_home_url(), '', esc_url($url));
	}

	/**
	 * Devuelve true si es una cadena válida
	 * isset(str) && is_string($str) && strlen($str) > 0);
	 *
	 * @param string $str
	 *        	Cadena a comprobar
	 * @return boolean true si es válida, false en caso contrario
	 */
	public static function cadenaValida($str = '') {
		return (isset($str) && is_string($str) && strlen($str) > 0);
	}
	public static function getUrlGravatarDefault($size = User::AVATAR_SIZE_DEFAULT) {
		$host = is_ssl() ? 'https://secure.gravatar.com' : 'http://0.gravatar.com';
		return $host . '/avatar/ad516503a11cd5ca435acc9bb6523536?s=' . $size;
	}

	/**
	 * Devuelve el mes traducido a partir del número pasado como parámetro
	 *
	 * @param integer $num
	 * @return string Mes traducido
	 */
	public static function getMesTransByNum($num) {
		switch ($num) {
			case 0 :
			case 1 :
				return I18n::transu('enero');
			case 2 :
				return I18n::transu('febrero');
			case 3 :
				return I18n::transu('marzo');
			case 4 :
				return I18n::transu('abril');
			case 5 :
				return I18n::transu('mayo');
			case 6 :
				return I18n::transu('junio');
			case 7 :
				return I18n::transu('julio');
			case 8 :
				return I18n::transu('agosto');
			case 9 :
				return I18n::transu('septiembre');
			case 10 :
				return I18n::transu('octubre');
			case 11 :
				return I18n::transu('noviembre');
			case 12 :
				return I18n::transu('diciembre');
			default :
				return '?';
		}
	}

	/**
	 * Devuelve el día de la semana traducido a partir del número pasado como parámetro
	 *
	 * @param integer $num
	 * @return string Nombre del día traducido
	 */
	public static function getDiaTransByNum($num) {
		switch ($num) {
			case 0 :
			case 1 :
				return I18n::transu('lunes');
			case 2 :
				return I18n::transu('martes');
			case 3 :
				return I18n::transu('miercoles');
			case 4 :
				return I18n::transu('jueves');
			case 5 :
				return I18n::transu('viernes');
			case 6 :
				return I18n::transu('sabado');
			case 7 :
				return I18n::transu('domingo');
			default :
				return '?';
		}
	}

	/**
	 * Devuelve $_SERVER[ REQUEST_URI ]
	 *
	 * @return string
	 */
	public static function getRequestUri() {
		return $_SERVER[REQUEST_URI];
	}

	/**
	 * Ejemplo:
	 * $time = strtotime('2010-04-28 17:25:43');
	 * echo 'event happened '.humanTiming($time).' ago';
	 *
	 * @param unknown $time
	 * @return string
	 */
	public static function tiempoHumano($time) {
		$time = time() - $time; // to get the time since that moment

		$tokens = array(
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);

		foreach ($tokens as $unit => $text) {
			if ($time < $unit)
				continue;
			$numberOfUnits = floor($time / $unit);
			return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
		}
	}

	/**
	 * Devuelve el ID del attachment apartir de su url
	 *
	 * @param string $attachment_url
	 *        	URL del attachment
	 * @return integer ID del attachment
	 */
	function getAttachmentIdFromUrl($attachment_url = '') {
		global $wpdb;
		$attachment_id = false;
		// If there is no url, return.
		if ('' == $attachment_url) {
			return;
		}
		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();
		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if (false !== strpos($attachment_url, $upload_dir_paths['baseurl'])) {
			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url);
			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace($upload_dir_paths['baseurl'] . '/', '', $attachment_url);
			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var($wpdb->prepare("SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url));
		}
		return $attachment_id;
	}

	/**
	 * Comprobamos que la cadena contenga algún valor, no sea todo espacios y sea mayor que 0
	 *
	 * @param string $valor
	 *        	cadena a comprobar
	 * @return boolean true: válida, false: no válida
	 */
	public static function esCadenaValida($valor) {
		return (isset($valor) && ! ctype_space($valor) && strlen($valor) > 0);
	}
}