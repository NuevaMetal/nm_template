<?php
require_once 'ModelBase.php';
/**
 * Analítica
 *
 * @author chema
 *
 */
class Analitica extends ModelBase {

	const TOTAL_USERS = 'total-users';

	const TOTAL_VISITAS = 'total-visitas';

	const TOTAL_VISITAS_USERS = 'total-visitas-users';

	const TOTAL_VISITAS_HORA = 'total-visitas-hora';
	public static $table = "analiticas";
	public $user_id;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Devuelve el User asociado a una analítica
	 *
	 * @return User Usuario de la analítica,
	 *         o NULL en caso de no tener user_id asociado
	 */
	public function getUser() {
		if ($this->user_id == null) {
			return null;
		}
		global $wpdb;
		$query = "SELECT *
				FROM {$wpdb->prefix}" . static::$table . "
				WHERE user_id = $this->user_id";
		return $wpdb->get_row($query);
	}

	/**
	 * Devuelve todos los seguimientos de una analítica
	 *
	 * @return array<Seguimiento>
	 */
	public function getSeguimientos() {
		if ($this->ID == null) {
			return null;
		}
		global $wpdb;
		$query = "SELECT *
				FROM {$wpdb->prefix}" . Seguimiento::$table . "
				WHERE analitica_id = $this->ID";
		return $wpdb->get_results($query);
	}

	/**
	 *
	 * @param string $ID
	 * @return NULL
	 */
	public function save() {
		global $wpdb, $post;
		$user = wp_get_current_user();
		//Comprobamos si existe
		$query = "SELECT * FROM {$wpdb->prefix}" . static::$table . "
		WHERE user_id = $user->ID AND DATE(created_at) = CURRENT_DATE;";
		//Utils::debug($query);
		$analitica = $wpdb->get_row($query);

		if ($analitica) {
			//Si existe actualizamos
			$wpdb->query($wpdb->prepare("
					UPDATE {$wpdb->prefix}" . static::$table . "
					SET updated_at = now()
					WHERE ID = %d", $analitica->ID));
			$this->ID = $analitica->ID;
			//dd($analitica);
		} else {
			//Si no existe lo creamos
			$result = $wpdb->query($wpdb->prepare("
				INSERT {$wpdb->prefix}" . static::$table . " (user_id, created_at, updated_at)
				VALUES (%d, null, null);", $user->ID));
			$this->ID = $wpdb->insert_id;
		}

		// Sólo guardar el seguimiento si el post existe
		if ($post->ID) {
			$seguimiento = new Seguimiento();
			$seguimiento->analitica_id = $this->ID;
			$seguimiento->post_id = $post->ID;
			$seguimiento->save();
		}
	}

	/**
	 * Devuelve el número total de registros por día
	 *
	 * @param number $cantidad
	 * @return multitype:string multitype:string unknown
	 */
	public static function getTotalRegistrosPorDia($cantidad = 50) {
		global $wpdb;
		$query = 'SELECT DATE( user_registered ) dia, COUNT( * ) total
				FROM wp_users
				GROUP BY dia
				ORDER BY dia DESC limit ' . $cantidad;
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve el número total de visitas por día
	 *
	 * @param number $cantidad
	 * @return multitype:string multitype:string unknown
	 */
	public static function getTotalVisitasUsersLogueados($cantidad = 50) {
		global $wpdb;
		$query = 'SELECT DATE( created_at ) dia, count(*) total_users_logueados
				FROM wp_analiticas
				where user_id != 0
				GROUP BY dia
				ORDER BY dia DESC limit ' . $cantidad;
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve el número total de visitas por día
	 *
	 * @param number $cantidad
	 * @return multitype:string multitype:string unknown
	 */
	public static function getTotalVisitas($cantidad = 50, $post_id = false, $agrupadoPorDia = true) {
		global $wpdb;
		$andPostId = ($post_id) ? " and post_id = $post_id " : '';
		$groupBy = ($agrupadoPorDia) ? 'group by s.dia' : '';
		$query = "select sum(s.total) as total, s.dia, 'total_visitas' as tipo
					from (
					    select distinct post_id, count(seh.seguimiento_id) total, date(se.created_at) dia
					    from wp_seguimientos se, wp_seguimientos_horas seh
						where se.ID = seh.seguimiento_id $andPostId
					   group by dia, post_id) s
					$groupBy ";
		if ($cantidad) {
			$query .= " limit $cantidad";
		}
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve el número de visitas totales que ha tenido un post totales y hoy
	 *
	 * @return array 'totales', 'totales_hoy'
	 */
	public static function getTotalVisitasByPostId($post_id) {
		$totales = self::getTotalVisitas(false, $post_id, false);
		$hoy = self::getTotalVisitas(false, $post_id, true);
		return array(
			'totales' => $totales[0]->total,
			'totales_hoy' => array_pop($hoy)->total
		);
	}

	public static function getVisitasUnicasByPostId($post_id) {
		$totales = self::getTotalVisitasUnicasPorIP(false, $post_id, false);
		$hoy = self::getTotalVisitasUnicasPorIP(false, $post_id, true);
		return array(
			'unicas' => $totales[0]->total,
			'unicas_hoy' => array_pop($hoy)->total
		);
	}

	/**
	 * Devuelve el número total de visitas únicas por IP por día
	 *
	 * @param number $cantidad
	 */
	public static function getTotalVisitasUnicasPorIP($cantidad = 50, $post_id = false, $agrupadoPorDia = true) {
		global $wpdb;
		$wherePostId = ($post_id) ? " where post_id = $post_id " : '';
		$groupBy = ($agrupadoPorDia) ? 'group by s.dia' : '';
		$query = "SELECT COUNT( s.ip ) AS total, s.dia, 'total_unicas' as tipo
					FROM (
						SELECT DISTINCT ip, DATE( created_at ) dia
						FROM wp_seguimientos $wherePostId
					) s
					$groupBy";
		if ($cantidad) {
			$query .= " limit $cantidad";
		}
		return $wpdb->get_results($query);
	}



	/**
	 * Devuelve el número total de entradas vistas por usuario por día
	 *
	 * @param number $cantidad
	 */
	public static function getTotalPostUnicosVistos($cantidad = 50) {
		global $wpdb;
		$query = "select count(s.post_id) as total, s.dia, 'total_posts_unicos' as tipo
					from (
					  select distinct post_id, date(created_at) dia
					  from wp_seguimientos
					  group by dia, post_id) s
					group by s.dia LIMIT " . $cantidad;
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve el número total de visitas en las últimas 24 horas
	 *
	 * @param number $cantidad
	 */
	public static function getTotalVisitasPorHora($cantidad = 24) {
		global $wpdb;
		$query = "select time_format(created_at,'%H') hora, count(*) total
					from wp_seguimientos_horas
					group by hora LIMIT " . $cantidad;
		return $wpdb->get_results($query);
	}

}
