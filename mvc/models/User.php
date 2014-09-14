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

	/**
	 * Número de post favoritos a mostrar en su perfil
	 */
	const NUM_FAV_PERFIL_DEFAULT = 2;

	/**
	 * Número de etiquetas de los posts favoritos a mostrar en su perfil
	 */
	const NUM_ETI_FAV_PERFIL_DEFAULT = 20;

	const ENTRADAS_PUBLICADAS_AJAX = 'entradas-publicadas';

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

	public function getUrl() {
		return get_the_author_meta('user_url', $this->ID);
	}

	public function getDescription() {
		return get_the_author_meta('description', $this->ID);
	}

	public function getEditUrl() {
		return get_edit_user_link();
	}

	/**
	 * Devuelve la URL del perfil del User
	 */
	public function getPefilUrl() {
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

	/**
	 * Devuelve el nombre del rol del User
	 *
	 * @return string
	 */
	public function getRol() {
		global $wpdb;
		$role = $wpdb->get_var("SELECT meta_value
				FROM $wpdb->usermeta
				WHERE meta_key = 'wp_capabilities'
				AND user_id = $this->ID");
		if (!$role)
			return 'non-user';
		$rarr = unserialize($role);
		$roles = is_array($rarr) ? array_keys($rarr) : array(
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
		foreach ($favoritos as $f) {
			if (isset($f ['the_tags'])) {
				foreach ($f ['the_tags'] as $t) {
					if (isset($tags [$t ['name']])) {
						$tags [$t ['name']] ['total']++;
					} else {
						$tags [$t ['name']] = $t;
						$tags [$t ['name']] ['total'] = 1;
					}
				}
			}
		}
		// Ordenamos el array de etiquetas por su cantidad total
		usort($tags, function ($a, $b) {
			return $a ['total'] < $b ['total'];
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
			$posts [] = Post::get($post_id, $dateFormat = false, $conCategorias);
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
			$cat_name = strtolower(Post::getCategoryName($f ['post_id']));
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