<?php

namespace Models;

use I18n\I18n;
use Libs\Utils;

/**
 * Modelo que representa un Usuario
 *
 * @author José María Valera Reales <@Chemaclass>
 */
class User extends Favoriteador {
	public static $table = "users";
	/*
	 * Tamaños del avatar
	 */
	const AVATAR_SIZE_ICO = 26;

	const AVATAR_SIZE_PEQUENO = 64;

	const AVATAR_SIZE_DEFAULT = 96;

	const AVATAR_SIZE_PERFIL = 190;

	/*
	 * Tamaño por defecto para el header
	 */
	const IMG_HEADER_WIDTH = 1200;

	const IMG_HEADER_HEIGHT = 270;

	const IMG_HEADER_WIDTH_PEQUE = 540; // 540

	const IMG_HEADER_HEIGHT_PEQUE = 250; // 250

	/*
	 * Claves de los metadatos
	 */
	const KEY_USER_TWITTER = 'tw_txt';

	const KEY_USER_FACEBOOK = 'fb_txt';

	const KEY_USER_GOOGLE_PLUS = 'gp_txt';

	const KEY_USER_YOUTUBE = 'yt_txt';

	const KEY_USER_SOUNDCLOUD = 'sc_txt';

	const KEY_USER_NOMBRE = 'first_name';

	const KEY_USER_APELLIDOS = 'last_name';

	const KEY_USER_IMG_HEADER = 'img_header';

	const KEY_USER_IMG_AVATAR = 'simple_local_avatar';

	const KEY_USER_IDIOMA = 'idioma';

	const KEY_USER_UBICACION = 'ubicacion';

	const KEY_USER_GENEROS_DESTACADOS = 'generos_destacados';

	const KEY_USER_BANDAS_DESTACADAS = 'bandas_destacadas';

	const KEY_USER_TIPO = 'tipo_usuario';

	const KEY_USER_HASH_ACTIVATION_KEY = 'hash_activation_key';

	/*
	 * Tipos de Usuario
	 */
	const TIPO_USUARIO = 'user';

	const TIPO_BANDA = 'band';

	const TIPO_PRODUCTOR = 'producer';

	const TIPO_MANAGER = 'manager';

	const TIPO_DISCOGRAFICA = 'record-seal';

	// Número de actividades a mostrar
	const NUM_ACTIVIDADES = 20;

	// Número de post favoritos a mostrar en su perfil
	const NUM_FAV_PERFIL_DEFAULT = 6;

	// Número de etiquetas de los posts favoritos a mostrar en su perfil
	const NUM_ETI_FAV_PERFIL_DEFAULT = 20;

	// Número de palabras para la descripción corta
	const NUM_DESCRIPCION_CORTA = 30;

	// Número de posts favositos para ir mostrando
	const NUM_POSTS_FAV = 8;

	// Número de posts recomendados
	const NUM_POSTS_RECOMENDADOS = 1;

	// Número límite de mensajes recibidos por petición
	const LIMIT_MENSAJES_RECIBIDOS = 10;

	const ENTRADAS_PUBLICADAS_AJAX = 'entradas-publicadas';

	/*
	 * Roles posibles
	 */
	const ROL_SUPER_ADMIN = 'super admin';

	const ROL_ADMIN = 'administrator';

	const ROL_EDITOR = 'editor';

	const ROL_AUTOR = 'author';

	const ROL_COLABORADOR = 'contributor';

	const ROL_SUSCRIPTOR = 'subscriber';

	/*
	 * Seguir a un User
	 */
	const SEGUIR = 'seguir';

	const ACTIVIDAD = 'actividad';

	const ENVIAR_MENSAJE = 'enviar-mensaje';

	const BORRAR_MENSAJE = 'borrar-mensaje';

	const MENSAJES = 'mensajes';

	const FAVORITOS = 'favoritos';

	const MENSAJES_ENVIADOS = 'mensajes-enviados';

	const MENSAJES_RECIBIDOS = 'mensajes-recibidos';

	/**
	 * Devuelve el login
	 *
	 * @return string
	 */
	public function getLogin() {
		return stripslashes($this->user_login);
	}

	/**
	 * Devuelve el email
	 *
	 * @return string
	 */
	public function getEmail() {
		return stripslashes($this->user_email);
	}

	/**
	 * Devuelve el nombre público del User (display_name)
	 *
	 * @return string
	 */
	public function getAlias() {
		return stripslashes($this->display_name);
	}

	/**
	 * Devuelve el número total de posts publicados por el User
	 *
	 * @return integer
	 */
	public function getTotalPosts() {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
				FROM wp_posts
				WHERE post_author = %d
				AND post_type = "post"
				AND post_status = "publish"', $this->ID));
	}

	/**
	 * Devuelve el número total de posts publicados por el User este año
	 *
	 * @return integer
	 */
	public function getTotalPostsEsteAno() {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
				FROM wp_posts
				WHERE post_author = %d
				AND post_type = "post"
				AND post_status = "publish"
				AND YEAR(post_modified) = YEAR(now())', $this->ID));
	}

	/**
	 * Establecer un nuevo avatar al User
	 *
	 * @param FILE $newAvatar
	 * @return boolean
	 */
	public function setAvatar($newAvatar = false) {
		return $this->_setImg(self::KEY_USER_IMG_AVATAR, $newAvatar);
	}

	/**
	 * Devuelve la url del avatar del User, y si no tuviera devolvería una imagen de avatar vacío
	 *
	 * @param integer $tamano
	 *        	Tamaño de la imágen
	 * @return string
	 */
	public function getAvatar($tamano = self::AVATAR_SIZE_DEFAULT) {
		$avatar = $this->_getImg(self::KEY_USER_IMG_AVATAR, $tamano, $tamano);
		if (empty($avatar)) {
			return Utils::getUrlGravatarDefault($tamano);
		}
		return $avatar;
	}

	/**
	 * Devuelve la url del avatar para el perfil
	 *
	 * @return string
	 */
	public function getAvatarPerfil() {
		return $this->getAvatar(self::AVATAR_SIZE_PERFIL);
	}

	/**
	 * Devuelve la url del avatar tipo icono
	 *
	 * @return string
	 */
	public function getAvatarIco() {
		return $this->getAvatar(self::AVATAR_SIZE_ICO);
	}

	/**
	 * Devuelve la url del avatar tipo pequeño
	 *
	 * @return string
	 */
	public function getAvatarPequeno() {
		return $this->getAvatar(self::AVATAR_SIZE_PEQUENO);
	}

	/**
	 * Devuelve un array con todos los tamaños que puede tener un avatar
	 *
	 * @return array<integer>
	 */
	private function _getTamanosAvatar() {
		return [
			32, // Era el tamaño del antiguo ICO, ahora es 26. Dejo esto para que los futuros avatares
			    // Se borren cuando se cambien. Recordar eliminar este número en un tiempo
			self::AVATAR_SIZE_ICO,
			self::AVATAR_SIZE_PEQUENO,
			self::AVATAR_SIZE_DEFAULT,
			self::AVATAR_SIZE_PERFIL
		];
	}

	/**
	 * Tamaños a borrar
	 *
	 * @return array<integer>
	 */
	protected function _getTamanosABorrar() {
		return $this->_getTamanosAvatar();
	}

	/**
	 * Devuelve la ruta de la imagen del header del user
	 *
	 * @return string
	 */
	public function getHeader() {
		return $this->_getImg(self::KEY_USER_IMG_HEADER, self::IMG_HEADER_WIDTH, self::IMG_HEADER_HEIGHT);
	}

	/**
	 * Devuelve la url del avatar tipo pequeño
	 *
	 * @return string
	 */
	public function getHeaderPequena() {
		return $this->_getImg(self::KEY_USER_IMG_HEADER, self::IMG_HEADER_WIDTH_PEQUE, self::IMG_HEADER_HEIGHT_PEQUE); // 540, 250);
	}

	/**
	 * Establecer el nuevo Header al User
	 *
	 * @param file $imgHeader
	 * @return boolean
	 */
	public function setHeader($imgHeader) {
		return $this->_setImg(self::KEY_USER_IMG_HEADER, $imgHeader);
	}

	/**
	 * Devuelve la URL del User
	 *
	 * @return string
	 */
	public function getUrl() {
		return get_the_author_meta('user_url', $this->ID);
	}

	/**
	 * Devuelve la descripción del User
	 *
	 * @return string
	 */
	public function getDescripcion() {
		$string = get_the_author_meta('description', $this->ID);
		if (strlen($string)) {
			return $string;
		}
		return I18n::trans('user.no_hay_info_perfil');
	}

	/**
	 * Devuelve la descripción corta
	 *
	 * @return string
	 */
	public function getDescripcionCorta() {
		return Utils::cortarStr($this->getDescripcion(), self::NUM_DESCRIPCION_CORTA);
	}

	/**
	 * Devuelve true si tiene descripción
	 *
	 * @return boolean
	 */
	public function tieneDescripcion() {
		return strlen($this->getDescripcion());
	}

	/**
	 * Devuelve la URL de la pantalla de edición del perfil del User
	 *
	 * @return string
	 */
	public function getEditUrl() {
		return admin_url('user-edit.php?user_id=' . $this->ID, 'http');
	}

	/**
	 * Devuelve la URL del perfil del User
	 */
	public function getPerfilUrl() {
		return get_author_posts_url($this->ID);
	}

	/**
	 * Devuelve el nombre del User
	 *
	 * @return string
	 */
	public function getNombre() {
		return get_user_meta($this->ID, self::KEY_USER_NOMBRE, true);
	}

	/**
	 * Devuelve os apellidos del User
	 *
	 * @return string
	 */
	public function getApellidos() {
		return get_user_meta($this->ID, self::KEY_USER_APELLIDOS, true);
	}

	/**
	 * Devuelve el nombre y los apellidos del user.
	 * Si no tiene puesto ninguno, mostrará el alias.
	 *
	 * @return string
	 */
	public function getNombreCompleto() {
		$nombreCompleto = $this->getNombre() . ' ' . $this->getApellidos();
		if (strlen(trim($nombreCompleto))) {
			return $nombreCompleto;
		}
		return $this->getAlias();
	}
	/**
	 * Devuelve la fecha del registro
	 */
	public function getFechaRegistro() {
		return $this->user_registered;
	}
	/**
	 * Devuelve la fecha del registro formateada
	 */
	public function getMesYAnoRegistro() {
		$fechaParseada = date_parse($this->getFechaRegistro());
		$dateObj = \DateTime::createFromFormat('!m', $fechaParseada['month']);
		$numMes = $dateObj->format('n');

		$mesTrans = Utils::getMesTransByNum($numMes);

		return [
			'mes' => $mesTrans,
			'ano' => $fechaParseada['year']
		];
	}

	/**
	 * Establecer un rol al User
	 *
	 * @param string $rol
	 */
	public function setRol($rol) {
		if (in_array($rol, self::getRolesPermitidos())) {
			$u = new \WP_User($this->ID);
			$u->set_role($rol);
			return true;
		}
		return false;
	}

	/**
	 * Devuelve la lista de roles para el usuario
	 *
	 * @return array<string>
	 */
	public function getRoles() {
		// global $wpdb;
		// $qRoles = $wpdb->get_var("SELECT meta_value FROM $wpdb->usermeta
		// WHERE meta_key = 'wp_capabilities' AND user_id = $this->ID");
		// $qRolesArr = unserialize($qRoles);
		$qRolesArr = get_user_meta($this->ID, 'wp_capabilities', true);
		return is_array($qRolesArr) ? array_keys($qRolesArr) : array(
			'non-user'
		);
	}

	/**
	 * Devuelve todos los roles permitidos
	 *
	 * @return array<string>
	 */
	public static function getRolesPermitidos() {
		return [
			self::ROL_ADMIN,
			self::ROL_EDITOR,
			self::ROL_AUTOR,
			self::ROL_COLABORADOR,
			self::ROL_SUSCRIPTOR
		];
	}

	/**
	 * Devuelve verdadero en caso de tener el rol de Admin
	 *
	 * @return boolean
	 */
	public function isAdmin() {
		return in_array(self::ROL_ADMIN, self::getRoles());
	}

	/**
	 * Devuelve verdadero en caso de tener el rol de Editor
	 *
	 * @return boolean
	 */
	public function isEditor() {
		return in_array(self::ROL_EDITOR, self::getRoles());
	}

	/**
	 * Devuelve verdadero en caso de tener el rol de Autor
	 *
	 * @return boolean
	 */
	public function isAutor() {
		return in_array(self::ROL_AUTOR, self::getRoles());
	}

	/**
	 * Devuelve verdadero en caso de tener el rol de colaborador
	 *
	 * @return boolean
	 */
	public function isColaborador() {
		return in_array(self::ROL_COLABORADOR, self::getRoles());
	}

	/**
	 * Devuelve verdadero en caso de tener el rol de suscriptor
	 *
	 * @return boolean
	 */
	public function isSuscriptor() {
		return in_array(self::ROL_SUSCRIPTOR, self::getRoles());
	}

	/**
	 * Devuelve true si es un suscriptor rechazado
	 *
	 * @return boolean
	 */
	public function isSuscriptorRechazado() {
		global $wpdb;
		$status = UserPendiente::RECHAZADO;
		$count = (int) $wpdb->get_var("select count(*)
				from wp_users_pendientes
				where user_id =  $this->ID
				and status = $status");
		return $this->isSuscriptor() && ($count > 0);
	}

	/**
	 * Devuelve verdadero en caso de tener privilegios de Admin
	 *
	 * @param array $args
	 * @return boolean
	 */
	public function canAdmin($args = []) {
		$args[] = self::ROL_ADMIN;
		return array_intersect($args, self::getRoles());
	}

	/**
	 * Devuelve verdadero en caso de tener privilegios de Editor
	 *
	 * @param array $args
	 * @return boolean
	 */
	public function canEditor($args = []) {
		$args[] = self::ROL_EDITOR;
		return $this->canAdmin($args);
	}

	/**
	 * Devuelve verdadero en caso de tener privilegios de Autor
	 *
	 * @param array $args
	 * @return boolean
	 */
	public function canAutor($args = []) {
		$args[] = self::ROL_AUTOR;
		return $this->canEditor($args);
	}

	/**
	 * Devuelve verdadero en caso de tener privilegios de Autor
	 *
	 * @param array $args
	 * @return boolean
	 */
	public function canColaborador($args = []) {
		$args[] = self::ROL_COLABORADOR;
		return $this->canAutor($args);
	}

	/**
	 * Devuelve verdadero en caso de tener privilegios de Suscriptor
	 *
	 * @param array $args
	 * @return boolean
	 */
	public function canSuscriptor($args = []) {
		$args[] = self::ROL_SUSCRIPTOR;
		return $this->canColaborador($args);
	}

	/**
	 * Devuelve true si el user es el usuario actual
	 *
	 * @return boolean
	 */
	public function isCurrentUser() {
		return ($this->ID == wp_get_current_user()->ID);
	}

	/**
	 * Devuelve true si el user es el usuario actual o el usuario actual es un admin
	 *
	 * @return boolean
	 */
	public function isCurrentUserOrAdmin() {
		return $this->isCurrentUser() || (wp_get_current_user()->roles[0] == self::ROL_ADMIN);
	}

	/**
	 * Devuelve el nombre del rol del User traducido
	 *
	 * @return string Rol del User traducido con I18n::transu()
	 */
	public function getRol() {
		$roles = self::getRoles();
		return I18n::transu($roles[0]);
	}

	/**
	 * Devuelve true si tiene alguna red social o url
	 *
	 * @return boolean
	 */
	public function tieneRedes() {
		if (strlen($this->getUrl())) {
			return true;
		}
		if (strlen($this->getTwitter())) {
			return true;
		}
		if (strlen($this->getFacebook())) {
			return true;
		}
		if (strlen($this->getGooglePlus())) {
			return true;
		}
		if (strlen($this->getSoundcloud())) {
			return true;
		}
		if (strlen($this->getYoutube())) {
			return true;
		}
		return false;
	}

	/**
	 * Devuelve el Twitter del User
	 *
	 * @return string
	 */
	public function getTwitter() {
		return get_user_meta($this->ID, self::KEY_USER_TWITTER, true);
	}

	/**
	 * Establecer un nuevo Twitter
	 *
	 * @param string $nuevo
	 */
	public function setTwitter($nuevo) {
		update_user_meta($this->ID, User::KEY_USER_TWITTER, $nuevo);
	}

	/**
	 * Devuelve el Twitter del User
	 *
	 * @return string
	 */
	public function getFacebook() {
		return get_user_meta($this->ID, self::KEY_USER_FACEBOOK, true);
	}

	/**
	 * Establecer un nuevo Facebook
	 *
	 * @param string $nuevo
	 */
	public function setFacebook($nuevo) {
		update_user_meta($this->ID, User::KEY_USER_FACEBOOK, $nuevo);
	}

	/**
	 * Devuelve el Twitter del User
	 *
	 * @return string
	 */
	public function getGooglePlus() {
		return get_user_meta($this->ID, self::KEY_USER_GOOGLE_PLUS, true);
	}

	/**
	 * Establecer un nuevo Google+
	 *
	 * @param string $nuevo
	 */
	public function setGooglePlus($nuevo) {
		update_user_meta($this->ID, User::KEY_USER_GOOGLE_PLUS, $nuevo);
	}

	/**
	 * Devuelve el Youtube del User
	 *
	 * @return string
	 */
	public function getYoutube() {
		return get_user_meta($this->ID, self::KEY_USER_YOUTUBE, true);
	}

	/**
	 * Establecer un nuevo Youtube
	 *
	 * @param string $nuevo
	 */
	public function setYoutube($nuevo) {
		update_user_meta($this->ID, User::KEY_USER_YOUTUBE, $nuevo);
	}

	/**
	 * Devuelve el Soundcloud del User
	 *
	 * @return string
	 */
	public function getSoundcloud() {
		return get_user_meta($this->ID, self::KEY_USER_SOUNDCLOUD, true);
	}

	/**
	 * Establecer un nuevo Soundcloud
	 *
	 * @param string $nuevo
	 */
	public function setSoundcloud($nuevo) {
		update_user_meta($this->ID, User::KEY_USER_SOUNDCLOUD, $nuevo);
	}

	/**
	 * Devuelve la ubicación del User
	 *
	 * @return string
	 */
	public function getUbicacion() {
		return get_user_meta($this->ID, self::KEY_USER_UBICACION, true);
	}

	/**
	 * Establecer la nueva ubicación
	 *
	 * @param string $nuevo
	 */
	public function setUbicacion($nuevo) {
		update_user_meta($this->ID, User::KEY_USER_UBICACION, $nuevo);
	}

	/**
	 * Devuelve las nuevas bandas destacadas
	 *
	 * @return string
	 */
	public function getBandasDestacadas() {
		return get_user_meta($this->ID, self::KEY_USER_BANDAS_DESTACADAS, true);
	}

	/**
	 * Devuelve la lista de las bandas destacadas separados por una coma
	 *
	 * @return array<string>
	 */
	public function getArrayBandasDestacadas() {
		$destacadas = $this->getBandasDestacadas();
		return $this->_getArrayConValoresUnicosByStr($destacadas);
	}

	/**
	 * Devuelve el total de bandas destacadas
	 *
	 * @return number
	 */
	public function getTotalBandasDestacadas() {
		return count($this->getArrayBandasDestacadas());
	}

	/**
	 * Establecer las nuevas bandas destacadas
	 *
	 * @param string $nuevo
	 */
	public function setBandasDestacadas($nuevo) {
		$nuevo = ucwords($nuevo);
		update_user_meta($this->ID, User::KEY_USER_BANDAS_DESTACADAS, $nuevo);
	}

	/**
	 * Devuelve los géneros destacados
	 *
	 * @return string
	 */
	public function getGenerosDestacados() {
		return get_user_meta($this->ID, self::KEY_USER_GENEROS_DESTACADOS, true);
	}

	/**
	 * Devuelve la lista de los géneros destacados separados por una coma
	 *
	 * @return array<string>
	 */
	public function getArrayGenerosDestacados() {
		$destacados = $this->getGenerosDestacados();
		return $this->_getArrayConValoresUnicosByStr($destacados);
	}

	/**
	 * Devuelve una lista con los valores separados por el delimitador (por defecto ',')
	 * del string pasado como 1er param.
	 * Además de hacerle un trim a cada item del array generado, utilizamos también
	 * array_values(array_unique(lista)) para sacar los valores únicos obtenidos
	 * y así evitar duplicados.
	 *
	 * @param string $str
	 *        	Cadena a cortar
	 * @param string $delimitador
	 *        	Delimitador para la cadena (para el explode)
	 * @return array Lista cortada por el delimitador
	 */
	private function _getArrayConValoresUnicosByStr($str, $delimitador = ',') {
		if (! strlen($str)) {
			return [];
		}
		return array_values(array_unique(array_map('trim', explode($delimitador, $str))));
	}

	/**
	 * Devuelve el total de géneros destacados
	 *
	 * @return number
	 */
	public function getTotalGenerosDestacados() {
		return count($this->getArrayGenerosDestacados());
	}

	/**
	 * Establecer los géneros destacados
	 *
	 * @param string $nuevo
	 */
	public function setGenerosDestacados($nuevo) {
		$nuevo = ucwords($nuevo);
		update_user_meta($this->ID, User::KEY_USER_GENEROS_DESTACADOS, $nuevo);
	}

	/**
	 * Devuelve el tipo del User.
	 * Si no hubiera tipo devolverá por defecto tipo User
	 *
	 * @return string
	 */
	public function getTipo() {
		$tipo = get_user_meta($this->ID, self::KEY_USER_TIPO, true);
		return (! $tipo) ? self::TIPO_USUARIO : $tipo;
	}

	/**
	 * Establecer un nuevo tipo
	 *
	 * @param string $nuevo
	 */
	public function setTipo($tipo) {
		// Sólo establecer el tipo si es un tipo válido
		if (in_array($tipo, self::getTiposDeUsuarioValidos())) {
			update_user_meta($this->ID, User::KEY_USER_TIPO, $tipo);
		}
	}

	/**
	 * Devuelve el idioma
	 *
	 * @return string
	 */
	public function getIdioma() {
		return get_user_meta($this->ID, self::KEY_USER_IDIOMA, true);
	}

	/**
	 * Devuelve el idioma traducido
	 *
	 * @return string
	 */
	public function getIdiomaTrans() {
		$idioma = ($_idioma = $this->getIdioma()) ? $_idioma : Utils::getLangBrowser();
		return I18n::transu('user.' . $this->getIdioma());
	}

	/**
	 * Establecer el idioma del user
	 *
	 * @param string $nuevo
	 */
	public function setIdioma($idioma) {
		// Sólo establecer el tipo si es un tipo válido
		if (in_array($idioma, I18n::getTodosIdiomasDisponibles())) {
			update_user_meta($this->ID, User::KEY_USER_IDIOMA, $idioma);
		}
	}

	/**
	 *
	 * @return boolean
	 */
	public function isTipoUsuario() {
		return ($this->getTipo() == self::TIPO_USUARIO);
	}
	public function isTipoBanda() {
		return ($this->getTipo() == self::TIPO_BANDA);
	}
	public function isTipoManager() {
		return ($this->getTipo() == self::TIPO_MANAGER);
	}
	public function isTipoProductor() {
		return ($this->getTipo() == self::TIPO_PRODUCTOR);
	}
	public function isTipoDiscografica() {
		return ($this->getTipo() == self::TIPO_DISCOGRAFICA);
	}

	/**
	 * Devuelve un array con los tipos de user válidos
	 *
	 * @return array<string>
	 */
	public static function getTiposDeUsuarioValidos() {
		return [
			self::TIPO_USUARIO,
			self::TIPO_BANDA,
			self::TIPO_PRODUCTOR,
			self::TIPO_MANAGER,
			self::TIPO_DISCOGRAFICA
		];
	}

	/**
	 * Devuelve una lista con la cantidad de entradas publicadas por días durante el último mes
	 *
	 * @param number $cantidad
	 * @return array
	 */
	public function getTotalEntradasPublicadasPorDia($cantidad = 31) {
		global $wpdb;
		$query = 'SELECT DATE(post_date) dia, COUNT(*) total
				FROM wp_posts
				WHERE post_author = %d
					AND post_type = "post"
					AND post_status = "publish"
					AND DATE( post_date ) >= DATE( NOW( ) ) -30
				GROUP BY dia
				ORDER BY dia DESC
	 			LIMIT %d ';
		$results = $wpdb->get_results($wpdb->prepare($query, $this->ID, $cantidad));
		return Analitica::formatearMeses($results);
	}

	/**
	 * Devuelve una lista con la cantidad de entradas publicadas por mes durante el último año
	 *
	 * @param number $cantidad
	 * @return array
	 */
	public function getTotalEntradasPublicadasPorMes($cantidad = 12) {
		global $wpdb;
		$query = 'SELECT MONTH( post_date ) mes, COUNT( * ) total
				FROM wp_posts
				WHERE post_author = %d
					AND post_type =  "post"
					AND post_status =  "publish"
					AND YEAR( post_date ) = YEAR( NOW( ) )
				GROUP BY mes, YEAR( post_date )
				ORDER BY YEAR( post_date ) DESC , mes DESC
				LIMIT %d';
		$results = $wpdb->get_results($wpdb->prepare($query, $this->ID, $cantidad));
		return Analitica::formatearMeses($results);
	}

	/**
	 * Devuelve true si el user está baneado en las revisiones y no puede reportar más post
	 *
	 * @return boolean
	 */
	public function estaBaneadoDeRevisiones() {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
				FROM  wp_revisiones_ban
				WHERE user_id = %d
				AND status = %d', $this->ID, UserBaneado::BANEADO));
	}

	/**
	 * Devuelve true si el user ya envió una notificación del post
	 *
	 * @param integer $post_id
	 *        	Identificador del Post
	 */
	public function yaNotificoPost($post_id) {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
		 	FROM wp_revisiones
			WHERE user_id = %d
				AND post_id = %d
				AND status = %d', $this->ID, $post_id, Revision::ESTADO_PENDIENTE));
	}

	/**
	 * Comprobar si un User ha sido bloqueado
	 *
	 * @return boolean Devuelve true si ha sido bloqueado, false si no ha sido bloqueado
	 */
	public function isBloqueado() {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*)
				FROM  wp_users_bloqueados
				WHERE user_id = %d
				AND status = %d;", $this->ID, UserBloqueado::ESTADO_BLOQUEADO));
	}

	/**
	 * Devuelve los usuarios seguidores
	 *
	 * @param integer $offset
	 * @param integer $limit
	 * @return array<User>
	 */
	public function getSeguidores($offset = 0, $limit = self::NUM_ACTIVIDADES) {
		global $wpdb;
		$seguidores = [];
		foreach ($this->_getSeguidoresIds($offset, $limit) as $seguidorId) {
			$seguidores[] = User::find($seguidorId);
		}
		return $seguidores;
	}

	/**
	 * Devuelve los usuarios a los que sigue el user
	 *
	 * @param integer $offset
	 * @param integer $limit
	 * @return array<User>
	 */
	public function getSiguiendo($offset = 0, $limit = self::NUM_ACTIVIDADES) {
		global $wpdb;
		$siguiendo = [];
		foreach ($this->_getSiguiendoIds($offset, $limit) as $siguiendoId) {
			$siguiendo[] = User::find($siguiendoId);
		}
		return $siguiendo;
	}

	/**
	 * Devuelve las Ids de los usuarios que estás siguiendo
	 *
	 * @return array<integer>
	 */
	public function _getSiguiendoIds($offset = 0, $limit = self::NUM_ACTIVIDADES) {
		global $wpdb;
		$sql = 'SELECT distinct s.a_quien_id
				FROM  wp_users u join wp_users_seguimientos s on (s.a_quien_id = u.ID)
				WHERE s.user_id = %d
				ORDER BY s.updated_at DESC';
		if ($limit) {
			$sql .= ' LIMIT %d OFFSET %d';
			return $wpdb->get_col($wpdb->prepare($sql, $this->ID, $limit, $offset));
		}
		return $wpdb->get_col($wpdb->prepare($sql, $this->ID));
	}

	/**
	 * Devuelve las Ids de los usuarios que te están siguiendo
	 *
	 * @return array<integer>
	 */
	private function _getSeguidoresIds($offset = 0, $limit = self::NUM_ACTIVIDADES) {
		global $wpdb;
		$sql = 'SELECT distinct s.user_id
				FROM  wp_users u left join wp_users_seguimientos s on (s.user_id = u.ID)
				WHERE s.a_quien_id = %d
				ORDER BY s.updated_at DESC';
		if ($limit) {
			$sql .= ' LIMIT %d OFFSET %d';
			return $wpdb->get_col($wpdb->prepare($sql, $this->ID, $limit, $offset));
		}
		return $wpdb->get_col($wpdb->prepare($sql, $this->ID));
	}

	/**
	 * Devuelve el número total de usuarios que le siguen
	 *
	 * @return array<User>
	 */
	public function getTotalSeguidores() {
		return count($this->_getSeguidoresIds(false, false));
	}

	/**
	 * Devuelve el número total de usuarios a los que está siguiendo
	 *
	 * @return integer
	 */
	public function getTotalSiguiendo() {
		return count($this->_getSiguiendoIds(false, false));
	}

	/**
	 * Devuelve el total de puntos
	 *
	 * @return integer
	 */
	public function getTotalPuntos() {
		$total = 0;
		foreach ($this->getPuntos() as $tipo => $p) {
			$total += $p->getTotalPuntosByTipo();
		}
		return $total;
	}

	/**
	 * Devuelve una lista con los puntos teniendo por clave el tipo de actividad/puntuable y como valor el total
	 *
	 * @return array<string,integer>
	 */
	public function getPuntos() {
		global $wpdb;
		$sql = 'SELECT tipo_que, count(*) as total
					FROM wp_v_actividades
					WHERE user_id = %d
					and tipo_que != "tipo_entrada_editada"
					GROUP BY tipo_que
				UNION
				SELECT "tipo_favoritos_recibidos" as tipo_que, count(*) total
					FROM wp_posts p JOIN wp_favoritos f ON (p.ID = f.post_id)
					where post_author = %d
					and post_status = "publish"
					and f.status = %d
				UNION
				SELECT "tipo_seguimiento_user_a_ti" as tipo_que, count( * ) total
					FROM wp_v_actividades
					WHERE que_id = %d
					AND tipo_que = "tipo_seguimiento_user"
				order by total desc';
		$actividades = $wpdb->get_results($wpdb->prepare($sql, $this->ID, $this->ID, Favorito::ACTIVO, $this->ID));
		// Parseo los objetos genéricos (StdClass) a VActividad
		array_walk($actividades, function (&$item) {
			$item = new VPuntos($item->tipo_que,  $item->total);
		});
		// Ordenamos por puntos
		usort($actividades, function ($a, $b) {
			$pa = $a->getTotalPuntosByTipo();
			$pb = $b->getTotalPuntosByTipo();
			if ($pa == $pb) {
				return 0;
			}
			return ($pa < $pb) ? 1 : - 1;
		});
		return $actividades;
	}

	/**
	 * Seguir a otro User
	 *
	 * @param integer $a_quien_id
	 *        	Identificador del User a seguir
	 * @throws Exception Si no se pudo seguir
	 * @return boolean true si se crea el seguimiento y false si se borra
	 */
	public function seguir($aQuienId, $seguir = Utils::SI) {
		$seguimiento = new Seguimiento($this->ID, $aQuienId);
		try {
			if ($seguir == Utils::SI) {
				$seguimiento->save();
				return true;
			} else {
				$seguimiento->delete();
				return false;
			}
		} catch ( Exception $e ) {
			throw $e;
		}
	}

	/**
	 * Devuelve true en caso de que el User actual ya siga al user que está viendo
	 *
	 * @param integer $user_id
	 * @return boolean
	 */
	public function yaLoSigues($user_id = false) {
		if (! $user_id) {
			$user_id = Utils::getCurrentUser()->ID;
		}
		global $wpdb;
		$leGusta = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(s.ID)
				FROM wp_users_seguimientos s join wp_users u on (s.user_id = u.ID)
				WHERE s.user_id = %d
				AND s.a_quien_id = %d;', $user_id, $this->ID));
		return $leGusta > 0;
	}

	/**
	 * Devuelve true en caso de que el User actual ya siga al user que está viendo
	 *
	 * @param integer $user_id
	 * @return boolean
	 */
	public function yaTeSigue($user_id = false) {
		if (! $user_id) {
			$user_id = Utils::getCurrentUser()->ID;
		}
		global $wpdb;
		$leGusta = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(s.ID)
				FROM wp_users_seguimientos s join wp_users u on (s.user_id = u.ID)
				WHERE s.user_id = %d
				AND s.a_quien_id = %d;', $this->ID, $user_id));
		return $leGusta > 0;
	}

	/**
	 * Devuelve la lista de actividades que le pertenecen únicamente al User
	 *
	 * @return array<VActividad>
	 */
	public function getActividadesPropias($offset = 0, $limit = self::NUM_ACTIVIDADES) {
		global $wpdb;
		$sql = 'SELECT tipo_que, user_id, que_id, updated_at
				FROM wp_v_actividades
				WHERE user_id = %d
				ORDER BY updated_at DESC ';

		if ($limit) {
			$sql .= ' LIMIT %d OFFSET %d';
			$actividades = $wpdb->get_results($wpdb->prepare($sql, $this->ID, $limit, $offset));
		} else {
			$actividades = $wpdb->get_results($wpdb->prepare($sql, $this->ID));
		}

		// Parseo los objetos genéricos (StdClass) a VActividad
		array_walk($actividades, function (&$item) {
			$item = new VActividad($item->tipo_que, $item->user_id, $item->que_id, $item->updated_at);
		});
		return $actividades;
	}

	/**
	 * Devuelve la lista de actividades que le pertenecen al User y a las personas que sigue
	 *
	 * @return array<VActividad>
	 */
	public function getActividades($offset = 0, $limit = self::NUM_ACTIVIDADES) {
		global $wpdb;
		$siguientoIds = implode(',', $this->_getSiguiendoIds());
		$siguientoIds .= (strlen($siguientoIds) > 1) ? ",$this->ID" : $this->ID;
		$sql = 'SELECT tipo_que, user_id, que_id, updated_at
				FROM wp_v_actividades
				WHERE user_id IN (' . $siguientoIds . ')
				AND tipo_que != "' . VActividad::TIPO_SEGUIMIENTO_USER . '"
				OR ((user_id = %d OR que_id = %d) AND tipo_que = %s )
				ORDER BY updated_at DESC ';

		if ($limit) {
			$sql .= ' LIMIT %d OFFSET %d';
			$actividades = $wpdb->get_results($wpdb->prepare($sql, $this->ID, $this->ID, VActividad::TIPO_SEGUIMIENTO_USER, $limit, $offset));
		} else {
			$actividades = $wpdb->get_results($wpdb->prepare($sql, $this->ID, $this->ID, VActividad::TIPO_SEGUIMIENTO_USER));
		}
		// Parseo los objetos genéricos (StdClass) a VActividad
		array_walk($actividades, function (&$item) {
			$item = new VActividad($item->tipo_que, $item->user_id, $item->que_id, $item->updated_at);
		});
		return $actividades;
	}

	/**
	 * Devuelve el número total de actividades propias
	 *
	 * @return number
	 */
	public function getTotalActividadesPropias() {
		return count($this->getActividadesPropias(false, false));
	}

	/**
	 * Devuelve el número total de actividades
	 *
	 * @return number
	 */
	public function getTotalActividades() {
		return count($this->getActividades(false, false));
	}

	/**
	 * Enviar un mensaje a un user
	 *
	 * @param Integer $aQuienId
	 *        	Idenficiador del usuario al que
	 * @param string $mensaje
	 *        	Mensaje a enviar
	 * @throws Exception
	 */
	private function _enviarMensaje($tipo, $mensajeTexto, $aQuienId = null, $respuestaId = null) {
		$mensaje = new Mensaje($this->ID);
		try {
			$mensaje->tipo = $tipo;
			$mensaje->mensaje = $mensajeTexto;
			$mensaje->a_quien_id = $aQuienId;
			$mensaje->respuesta_id = $respuestaId;
			$mensaje->save();
		} catch ( Exception $e ) {
			throw $e;
		}
	}

	/**
	 * Escribir un mensaje privado
	 *
	 * @param string $mensajeTexto
	 * @param integer $aQuienId
	 * @param integer $respuestaId
	 */
	public function enviarMensajePrivado($mensajeTexto, $aQuienId = null, $respuestaId = null) {
		$this->_enviarMensaje(Mensaje::TIPO_MENSAJE_PRIVADO, $mensajeTexto, $aQuienId, $respuestaId);
	}

	/**
	 * Escribir un estado
	 *
	 * @param unknown $mensajeTexto
	 * @param string $aQuienId
	 * @param string $respuestaId
	 */
	public function enviarMensajeEstado($mensajeTexto, $aQuienId = null, $respuestaId = null) {
		$this->_enviarMensaje(Mensaje::TIPO_ESTADO, $mensajeTexto, $aQuienId, $respuestaId);
	}

	/**
	 * Devuelve el nonce para un nuevo mensaje
	 *
	 * @return string
	 */
	public function getNonceEnviarMensaje() {
		return $this->crearNonce(User::ENVIAR_MENSAJE);
	}

	/**
	 * Devuelve el nonce para un borrar un mensaje
	 *
	 * @return string
	 */
	public function getNonceBorrarMensaje() {
		return $this->crearNonce(User::BORRAR_MENSAJE);
	}

	/**
	 * Devuelve el nonce para un nuevo mensaje
	 *
	 * @return string
	 */
	public function getNonceSeguir() {
		return $this->crearNonce(User::SEGUIR);
	}

	/**
	 * Devuelve el número de mensajes recibidos sin leer
	 *
	 * @return number
	 */
	public function getTotalMensajesRecibidosSinLeer() {
		return count($this->_getMensajesIds(self::MENSAJES_RECIBIDOS, true));
	}

	/**
	 * Devuelve el número de mensajes recibidos
	 *
	 * @return number
	 */
	public function getTotalMensajesRecibidos() {
		return count($this->_getMensajesIds(self::MENSAJES_RECIBIDOS));
	}

	/**
	 * Devuelve el número de mensajes recibidos borrados
	 *
	 * @return number
	 */
	public function getTotalMensajesBorrados() {
		return count($this->_getMensajesIds(self::MENSAJES_RECIBIDOS, false, false));
	}

	/**
	 * Devuelve una lista con los IDs de los mensajes según el tipo
	 *
	 * @param string $tipo
	 * @param boolean $noLeidos
	 *        	Por defecto todos (no leidos y leidos)
	 */
	private function _getMensajesIds($tipo, $noLeidos = false, $estadoActivo = true) {
		global $wpdb;
		switch ($tipo) {
			case self::MENSAJES_ENVIADOS :
				$columna = 'user_id';
				break;
			case self::MENSAJES_RECIBIDOS :
				$columna = 'a_quien_id';
				break;
		}
		$sql = 'SELECT ID
				FROM wp_mensajes
				WHERE ' . $columna . ' = ' . $this->ID;

		$sql .= ' AND estado = ' . (($estadoActivo) ? Mensaje::ESTADO_ACTIVO : Mensaje::ESTADO_BORRADO);

		if ($noLeidos) {
			$sql .= ' AND leido = ' . Mensaje::LEIDO_NO;
		}
		$sql .= ' ORDER BY updated_at DESC ';

		return $wpdb->get_col($sql);
	}

	/**
	 * Devuelve el número total de mensajes enviados
	 *
	 * @return number
	 */
	public function getTotalMensajesEnviados() {
		return count($this->_getMensajesIds(self::MENSAJES_ENVIADOS));
	}

	/**
	 * Devuelve todos los mensajes recibidos del usuario
	 *
	 * @return unknown
	 */
	public function getMensajesRecibidos($offset = 0, $limit = self::LIMIT_MENSAJES_RECIBIDOS) {
		global $wpdb;
		$mensajesIds = $wpdb->get_col($wpdb->prepare('
				SELECT ID
				FROM wp_mensajes
				WHERE a_quien_id = %d AND estado = %d
				ORDER BY updated_at DESC
				LIMIT %d OFFSET %d
				', $this->ID, Mensaje::ESTADO_ACTIVO, $limit, $offset));
		$mensajes = [];
		foreach ($mensajesIds as $id) {
			$mensajes[] = Mensaje::find($id);
		}
		return $mensajes;
	}

	/**
	 * Devuelve todos los mensajes recibidos del usuarios
	 *
	 * @return array<Mensaje>
	 */
	public function getMensajesEnviados($offset = 0, $limit = self::LIMIT_MENSAJES_RECIBIDOS) {
		global $wpdb;
		$mensajesIds = $wpdb->get_col($wpdb->prepare('
				SELECT ID
				FROM wp_mensajes
				WHERE user_id = %d AND estado = %d
				ORDER BY updated_at DESC
				LIMIT %d OFFSET %d
				', $this->ID, Mensaje::ESTADO_ACTIVO, $limit, $offset));
		$mensajes = [];
		foreach ($mensajesIds as $id) {
			$mensajes[] = Mensaje::find($id);
		}
		return $mensajes;
	}

	/**
	 * Devuelve todos los mensajes recibidos que has borrado del usuarios
	 *
	 * @return unknown
	 */
	public function getMensajesBorrados($offset = 0, $limit = self::LIMIT_MENSAJES_RECIBIDOS) {
		global $wpdb;
		$mensajesIds = $wpdb->get_col($wpdb->prepare('
				SELECT ID
				FROM wp_mensajes
				WHERE a_quien_id = %d AND estado = %d
				ORDER BY updated_at DESC
				LIMIT %d OFFSET %d
				', $this->ID, Mensaje::ESTADO_BORRADO, $limit, $offset));
		$mensajes = [];
		foreach ($mensajesIds as $id) {
			$mensajes[] = Mensaje::find($id);
		}
		return $mensajes;
	}

	/**
	 * Devuelve una lista de usuarios apartir de un nombre para buscar
	 *
	 * @param string $search_query
	 *        	Nombre a buscar del usuario
	 * @param number $offset
	 * @param number $limit
	 */
	public static function getUsersBySearch($search_query, $offset = 0, $limit = 4) {
		global $wpdb;
		$aBuscar = "%$search_query%";
		$sql = 'SELECT DISTINCT ID
				FROM wp_users u
				LEFT JOIN wp_usermeta m ON ( u.ID = m.user_id )
				LEFT OUTER JOIN (
					SELECT ac.user_id, sum( ac.total ) suma
					FROM (
						SELECT user_id,
						CASE WHEN tipo_que = "' . VActividad::TIPO_SEGUIMIENTO_USER . '" THEN
                    			count(*) * ' . VActividad::PUNTOS_TIPO_SEGUIMIENTO_USER . '
                   			WHEN tipo_que = "' . VActividad::TIPO_NUEVO_COMENTARIO . '" THEN
                    			count(*) * ' . VActividad::PUNTOS_TIPO_NUEVO_COMENTARIO . '
                    		WHEN tipo_que = "' . VActividad::TIPO_ENTRADA_EDITADA . '" THEN
                    			count(*) * ' . VActividad::PUNTOS_TIPO_ENTRADA_EDITADA . '
                    		WHEN tipo_que = "' . VActividad::TIPO_ME_GUSTA . '" THEN
                    			count(*) * ' . VActividad::PUNTOS_TIPO_ME_GUSTA . '
                    		WHEN tipo_que = "' . VActividad::TIPO_NUEVA_ENTRADA . '" THEN
                    			count(*) * ' . VActividad::PUNTOS_TIPO_NUEVA_ENTRADA . '
                    		ELSE count(*) END AS total
						FROM wp_v_actividades
						GROUP BY user_id, tipo_que
					) ac
					GROUP BY ac.user_id
					ORDER BY suma DESC ) actividad
				ON (actividad.user_id = u.ID)
				WHERE (
					display_name LIKE %s
					OR (
						meta_key = "first_name"
						AND meta_value LIKE %s
					) OR (
						meta_key = "last_name"
						AND meta_value LIKE %s
					)
				)
				GROUP BY ID, actividad.suma
				ORDER BY actividad.suma DESC ';
		if ($limit) {
			$users_ids = $wpdb->get_col($wpdb->prepare($sql . '
				LIMIT %d OFFSET %d
				', $aBuscar, $aBuscar, $aBuscar, $limit, $offset));
		} else {
			$users_ids = $wpdb->get_col($wpdb->prepare($sql, $aBuscar, $aBuscar, $aBuscar));
		}
		$users = [];
		foreach ($users_ids as $user_id) {
			$users[] = User::find($user_id);
		}
		/*
		 * Ordenamos por puntos.
		 * No es necesario, ya hacemos el ordenamiento mediante la consulta SQL. Más óptimo.
		 */
		// uasort($users, function ($a, $b) {
		// $aPuntos = $a->getTotalPuntos();
		// $bPuntos = $b->getTotalPuntos();
		// if ($aPuntos == $bPuntos) { return 0; }
		// return $aPuntos > $bPuntos ? 1 : - 1;
		// });
		return $users;
	}

	/**
	 * Generar una nueva contraseña para el User.
	 *
	 * @return string Nueva contraseña
	 */
	public function setNewPassword() {
		$user_pass = wp_generate_password(12, false);
		wp_set_password($user_pass, $this->ID);
		return $user_pass;
	}

	/**
	 * Devuelve la url para generear la nueva password
	 *
	 * @return string|boolean
	 */
	public function getActivationKeyUrl() {
		global $wpdb;
		$generarNuevaKey = function () {
			return wp_generate_password(30, false);
		};
		$activationKey = $generarNuevaKey();
		/*
		 * Mientras la key no sea única, generar otra nueva.
		 */
		while ($wpdb->get_var($wpdb->prepare('select count(*)
				from wp_usermeta
				where meta_key = %s
				and meta_value = %s', self::KEY_USER_HASH_ACTIVATION_KEY, wp_hash_password($activationKey)))) {
			$activationKey = $generarNuevaKey();
		}
		if ($this->setActivationKey($activationKey)) {
			return home_url() . '/activation?user=' . $this->user_nicename . '&key=' . $activationKey;
		}
		return false;
	}

	/**
	 * Establecer un nuevo hash para el cambio de password o borrarlo
	 *
	 * @param string $activationKey
	 *        	clave sin hashear.
	 * @param boolean $borrar
	 *        	true para borrar el hash.
	 */
	public function setActivationKey($activationKey, $borrar = false) {
		if ($borrar) {
			return delete_user_meta($this->ID, self::KEY_USER_HASH_ACTIVATION_KEY);
		}
		return update_user_meta($this->ID, self::KEY_USER_HASH_ACTIVATION_KEY, wp_hash_password($activationKey));
	}

	/**
	 * Devuelve el hash de la activación
	 *
	 * @return string
	 */
	public function getHashActivationKey() {
		return get_user_meta($this->ID, self::KEY_USER_HASH_ACTIVATION_KEY, true);
	}

	/**
	 * Devuelve las alertas que tenga el usuario actual pendientes
	 *
	 * @return array<View> Alertas
	 */
	public function getAlertas() {
		$alertas = [];
		if (($total = $this->getTotalMensajesRecibidosSinLeer())) {
			if ($total == 1) {
				$msg = I18n::trans('actividad.tienes_un_mensaje_nuevo');
			} else {
				$msg = I18n::trans('actividad.tienes_mensajes_nuevos', [
					'total' => $total
				]);
			}
			$alertas[] = [
				'strong' => I18n::transu('mensajes'),
				'mensaje' => $msg,
				'url' => home_url() . '/messages'
			];
		}
		return $alertas;
	}

	/**
	 * Aumentar el contador de la revisión para el mismo post si ya le dió
	 * para revisar y aún no se corrigió
	 *
	 * @param integer $post_id
	 *        	Identificador del post
	 */
	public function aumentarContadorRevision($post_id) {
		global $wpdb;
		return $wpdb->query($wpdb->prepare('UPDATE wp_revisiones
		 		SET count = count + 1
		 		WHERE post_id = %d
		 		AND user_id = %d
		 		AND status = %d', $post_id, $this->ID, Revision::ESTADO_PENDIENTE));
	}

	/**
	 * Devuelve true si al User le gusta el Post pasado por parámetro.
	 * False en caso contrario.
	 *
	 * @param Post $post
	 */
	public function teGusta($post) {
		global $wpdb;
		$estado = (int) $wpdb->get_var($wpdb->prepare('SELECT status FROM wp_favoritos
				WHERE post_id = %d AND user_id = %d', $post->ID, $this->ID));
		if ($estado == Favorito::ACTIVO) {
			return true;
		}
		return false;
	}

	/**
	 * Indicar 'me gusta' o quitar 'me gusta', dependiendo del estado anterior que tenga el me gusta
	 * del Post pasado por parámetro.
	 * Si no existe lo crea.
	 *
	 * @param Post $post
	 *        	Post que te gusta
	 * @return boolean true si te gusta, false si no te gusta.
	 */
	public function meGustaToogle($post) {
		global $wpdb;
		// Comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = (int) $wpdb->get_var($wpdb->prepare('
				SELECT COUNT(*) FROM wp_favoritos
				WHERE post_id = %d AND user_id =%d', $post->ID, $this->ID));

		// Si no existe, lo creamos con estado borrado por defecto.
		if (! $num) {
			$result = $wpdb->query($wpdb->prepare('
					INSERT INTO wp_favoritos (post_id, user_id, status, created_at, updated_at)
					VALUES (%d, %d, %d, null, null );', $post->ID, $this->ID, Favorito::BORRADO));
		}

		if ($this->teGusta($post)) {
			// Si está activo lo ponemos borrado
			$wpdb->query($wpdb->prepare('
					UPDATE wp_favoritos
					SET status =  %d, count = count + 1
					WHERE post_id = %d AND user_id = %d
					AND status = %d;', Favorito::BORRADO, $post->ID, $this->ID, Favorito::ACTIVO));
			return false;
		}
		// Si está borrado lo ponemos activo
		$wpdb->query($wpdb->prepare('
					UPDATE wp_favoritos
					SET status =  %d, count = count + 1
					WHERE post_id = %d AND user_id = %d
					AND status = %d;', Favorito::ACTIVO, $post->ID, $this->ID, Favorito::BORRADO));
		return true;
	}

	/**
	 * Devuelve una lista de Users en base a las etiquetas de los favoritos que tenga el User.
	 *
	 * @return array<User> Users recomendados en base a sus favoritos.
	 */
	public function getUsersRecomendados() {
		// TODO:
		// global $wpdb;
		// $pieces = $this->getEtiquetasUnicasByFavoritos();
		// $tagsFavsIds = implode(',', $pieces);
		return [];
	}

	/**
	 * Devuelve una lista de Posts en base a las etiquetas de los favoritos que tenga el User.
	 *
	 * @return array<Post> Posts recomendados en base a sus favoritos.
	 */
	public function getPostsRecomendados($limit = self::NUM_POSTS_RECOMENDADOS) {
		global $wpdb;
		$pieces = $this->getEtiquetasUnicasByFavoritos();
		$tagsFavsIds = implode(',', $pieces);
		$posts_id = $wpdb->get_col($wpdb->prepare('
				SELECT distinct object_id AS post_id
				FROM wp_term_relationships
				WHERE term_taxonomy_id IN (%s)
				ORDER BY RAND()
				LIMIT %d', $tagsFavsIds, $limit));
		foreach ($posts_id as $post_id) {
			$posts[] = Post::find($post_id);
		}
		return $posts;
	}

}