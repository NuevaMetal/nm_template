<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class User extends ModelBase {
	public static $table = "users";

	const KEY_USER_TWITTER = 'user_tw_txt';

	const KEY_USER_FACEBOOK = 'user_fb_txt';

	const KEY_USER_GOOGLE_PLUS = 'user_gp_txt';

	const KEY_USER_NOMBRE = 'first_name';

	const KEY_USER_APELLIDOS = 'last_name';

	const KEY_USER_IMG_HEADER = 'user_img_header';

	/**
	 * Número de post favoritos a mostrar en su perfil
	 */
	const NUM_FAV_PERFIL_DEFAULT = 2;

	/**
	 * Número de etiquetas de los posts favoritos a mostrar en su perfil
	 */
	const NUM_ETI_FAV_PERFIL_DEFAULT = 20;

	const ENTRADAS_PUBLICADAS_AJAX = 'entradas-publicadas';

	const ROL_SUPER_ADMIN = 'super admin';

	const ROL_ADMIN = 'administrator';

	const ROL_EDITOR = 'editor';

	const ROL_AUTOR = 'author';

	const ROL_CONTRIBUIDOR = 'contributor';

	const ROL_SUSCRIPTOR = 'subscriber';

	/**
	 * Devuelve el número total de posts publicados por el User
	 */
	public function getCountPosts() {
		return count_user_posts($this->ID);
	}

	public function getAvatar($tamano = 96, $default = "", $alt = false) {
		return get_avatar($this->ID, $tamano, $default, $alt);
	}

	public function getAvatarPerfil() {
		return get_avatar($this->ID, 160, '', "$this->display_name avatar");
	}

	public function setImgHeader($imgHeader) {
		// check for uploaded files OR need to be more secure since low privelege users can upload
		if (empty($imgHeader ['name']) || strpos($imgHeader ['name'], '.php')) {
			throw new Exception("User($this->ID)->setImgHeader() FAIL. Intento subir .php");
		}

		$avatar = wp_handle_upload($_FILES [self::KEY_USER_IMG_HEADER], array(
			'mimes' => array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png'
			),
			'test_form' => false,
			'unique_filename_callback' => array(
				$this,
				'unique_filename_callback'
			)
		));
		// Quitamos el anterior meta
		delete_user_meta($this->ID, self::KEY_USER_IMG_HEADER);
		$meta_value = array();

		$url_or_media_id = $avatar ['url'];
		// Establecemos el nuevo meta
		if (is_int($url_or_media_id)) {
			$meta_value ['media_id'] = $url_or_media_id;
			$url_or_media_id = wp_get_attachment_url($url_or_media_id);
		}
		$meta_value ['full'] = $url_or_media_id;
		update_user_meta($this->ID, self::KEY_USER_IMG_HEADER, $meta_value);
	}

	public function unique_filename_callback($dir, $name, $ext) {
		$name = $base_name = sanitize_file_name($this->display_name . '_header_' . time());
		$number = 1;
		while (file_exists($dir . "/$name$ext")) {
			$name = $base_name . '_' . $number;
			$number++;
		}
		return $name . $ext;
	}

	public function getImgHeader($sizeW = 900, $sizeH = 200) {
		// fetch local avatar from meta and make sure it's properly ste
		$local_avatars = get_user_meta($this->ID, self::KEY_USER_IMG_HEADER, true);
		if (empty($local_avatars ['full'])) {
			return '';
		}
		// generate a new size
		if (!array_key_exists($sizeW, $local_avatars)) {
			$local_avatars [$sizeW] = $local_avatars ['full']; // just in case of failure elsewhere


			$upload_path = wp_upload_dir();

			// get path for image by converting URL, unless its already been set, thanks to using media library approach
			if (!isset($avatar_full_path)) {
				$avatar_full_path = str_replace($upload_path ['baseurl'], $upload_path ['basedir'], $local_avatars ['full']);
			}
			// generate the new size
			$editor = wp_get_image_editor($avatar_full_path);
			if (!is_wp_error($editor)) {
				$resized = $editor->resize($sizeW, $sizeH, true);
				if (!is_wp_error($resized)) {
					$dest_file = $editor->generate_filename();
					$saved = $editor->save($dest_file);
					if (!is_wp_error($saved)) {
						$local_avatars [$sizeW] = str_replace($upload_path ['basedir'], $upload_path ['baseurl'], $dest_file);
					}
				}
			}
			// save updated avatar sizes
			update_user_meta($user_id, self::KEY_USER_IMG_HEADER, $local_avatars);
		}
		if ('http' != substr($local_avatars [$sizeW], 0, 4)) {
			$local_avatars [$sizeW] = home_url($local_avatars [$sizeW]);
		}
		return esc_url($local_avatars [$sizeW]);
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
	public function getDescription() {
		return get_the_author_meta('description', $this->ID);
	}

	/**
	 * Devuelve la URL de la pantalla de edición del perfil del User
	 *
	 * @return string
	 */
	public function getEditUrl() {
		return get_edit_user_link();
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
		$valor = get_user_meta($this->ID, self::KEY_USER_NOMBRE);
		return (is_array($valor)) ? $valor [0] : $valor;
	}

	/**
	 * Devuelve os apellidos del User
	 *
	 * @return string
	 */
	public function getApellidos() {
		$valor = get_user_meta($this->ID, self::KEY_USER_APELLIDOS);
		return (is_array($valor)) ? $valor [0] : $valor;
	}

	/**
	 * Devuelve el nombre y los apellidos del user
	 *
	 * @return string
	 */
	public function getNombreCompleto() {
		return $this->getNombre() . ' ' . $this->getApellidos();
	}

	public function isAdmin() {
		global $wpdb;
		$qRoles = $wpdb->get_var("SELECT meta_value
				FROM $wpdb->usermeta
				WHERE meta_key = 'wp_capabilities'
				AND user_id = $this->ID");
		$qRolesArr = unserialize($qRoles);
		$roles = is_array($qRolesArr) ? array_keys($qRolesArr) : array(
			'non-user'
		);
		return in_array(self::ROL_ADMIN, $roles);
	}

	/**
	 * Devuelve el nombre del rol del User
	 *
	 * @return string
	 */
	public function getRol() {
		global $wpdb;
		$qRoles = $wpdb->get_var("SELECT meta_value
				FROM $wpdb->usermeta
				WHERE meta_key = 'wp_capabilities'
				AND user_id = $this->ID");
		$qRolesArr = unserialize($qRoles);
		$roles = is_array($qRolesArr) ? array_keys($qRolesArr) : array(
			'non-user'
		);
		return I18n::transu($roles [0]);
	}

	/**
	 * Devuelve el Twitter del User
	 *
	 * @return string
	 */
	public function getTwitter() {
		$valor = get_user_meta($this->ID, self::KEY_USER_TWITTER);
		return (is_array($valor)) ? $valor [0] : $valor;
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
		$valor = get_user_meta($this->ID, self::KEY_USER_FACEBOOK);
		return (is_array($valor)) ? $valor [0] : $valor;
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
		$valor = get_user_meta($this->ID, self::KEY_USER_GOOGLE_PLUS);
		return (is_array($valor)) ? $valor [0] : $valor;
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
	 * Devuelve una lista de arrays con las etiquetas de las entradas a las que le dio favoritos
	 *
	 * @return array
	 */
	public function getArrayEtiquetasFavoritas($cant = User::NUM_ETI_FAV_PERFIL_DEFAULT) {
		$favoritos = $this->getFavoritos($limit = false, $offset = false, $conCategorias = true);
		$tags = [];
		foreach ($favoritos as $postFavorito) {
			if ($postFavorito->tieneEtiquetas()) {
				foreach ($postFavorito->getEtiquetas() as $t) {
					if (isset($tags [$t->name])) {
						$tags [$t->name]->total++;
					} else {
						$tags [$t->name] = $t;
						$tags [$t->name]->total = 1;
					}
				}
			}
		}
		// Ordenamos el array de etiquetas por su cantidad total
		usort($tags, function ($a, $b) {
			return $a->total < $b->total;
		});

		if ($cant) {
			return array_slice($tags, 0, $cant);
		}

		return $tags;
	}

	/**
	 * Devuelve la lista de todos los favoritos de un User
	 *
	 * @param number $limit
	 * @param number $offset
	 * @param boolean $conCategorias
	 * @return array
	 */
	public function getFavoritos($limit = false, $offset = false, $conCategorias = false) {
		global $wpdb;
		$status = Favorito::ACTIVO;
		$tabla = $wpdb->prefix . Favorito::$table;
		$user_id = $this->ID;
		$queryPostId = "SELECT post_id FROM $tabla
						WHERE status = $status
						AND user_id = $user_id
						ORDER BY updated_at desc ";
		if ($limit) {
			$queryPostId .= ' LIMIT ' . $limit;
		}
		if ($offset) {
			$queryPostId .= ' OFFSET ' . $offset;
		}
		$posts_id = $wpdb->get_col($queryPostId);
		$posts = [];
		foreach ($posts_id as $post_id) {
			$posts [] = Post::find($post_id);
		}
		return $posts;
	}

	/**
	 * Devuelve
	 *
	 * @param string $cant
	 * @return Ambigous <multitype:multitype: , unknown>
	 */
	public function getFavoritosAgrupados($cant = false) {
		$todosFavoritos = $this->getFavoritos($cant);
		$favoritos = [];
		foreach ($todosFavoritos as $k => $f) {
			$cat_name = strtolower($f->getCategoriaNombre());
			if (!isset($favoritos [$cat_name])) {
				$favoritos [$cat_name] = [];
				if ($k == 0 && !isset($favoritos [$cat_name] ['activo'])) {
					$favoritos [$cat_name] ['activo'] = true;
				}
			}
			$favoritos [$cat_name] ['lista'] [] = $f;
		}
		// Añadimos el total
		foreach ($favoritos as &$f) {
			$f ['total_lista'] = count($f ['lista']);
		}
		return $favoritos;
	}

	/**
	 * Devuelve el número total de favoritos que tiene el user
	 *
	 * @return number Total de favoritos que tiene el User
	 */
	public function getCountFavoritos() {
		global $wpdb;
		$activo = Favorito::ACTIVO;
		return ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE user_id = $this->ID AND status = $activo;");
	}

	/**
	 * Devuelve el número total de favoritos que han recibido sus entradas
	 *
	 * @return string
	 */
	public function getCountFavoritosRecibidos() {
		global $wpdb;
		$activo = Favorito::ACTIVO;
		return ( int ) $wpdb->get_var("SELECT SUM( p.totales )
			FROM (
				SELECT COUNT(ids.ID) as totales FROM wp_favoritos f,
					(SELECT ID FROM wp_posts
					where post_author = $this->ID) ids
				where f.post_id = ids.ID
				AND f.status = $activo
				GROUP BY f.user_id
			) p");
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
				WHERE post_author = ' . $this->ID . '
					AND post_type = "post"
					AND post_status = "publish"
					AND DATE( post_date ) >= DATE( NOW( ) ) -30
				GROUP BY dia
				ORDER BY dia DESC
	 			LIMIT ' . $cantidad;
		$result = $wpdb->get_results($query);
		$result = Analitica::formatearDias($result);
		return $result;
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
				WHERE post_author = ' . $this->ID . '
					AND post_type =  "post"
					AND post_status =  "publish"
					AND YEAR( post_date ) = YEAR( NOW( ) )
				GROUP BY mes, YEAR( post_date )
				ORDER BY YEAR( post_date ) DESC , mes DESC
				LIMIT ' . $cantidad;
		$result = $wpdb->get_results($query);
		$result = Analitica::formatearMeses($result);
		return $result;
	}

}