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
			$seguimiento->post_id = is_home() ? 0 : $post->ID;
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
		$query = 'SELECT DATE( created_at ) dia, count(*) total_users_logueados, user_id
				FROM wp_analiticas
				where user_id != 0
				GROUP BY dia
				ORDER BY dia DESC limit ' . $cantidad;
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve una lista con los nombres (y url) de los users logueados
	 *
	 * @param number $cantidad
	 *        Límite máximo de nombres a obtener
	 * @param string $cuando
	 *        fecha en SQL de cuándo se quiere dicha lista de nombres.
	 *        Por defecto será el día actual
	 * @return array Lista con el nombre y la url del usuario
	 */
	public static function getUsersLogueados($cantidad = 50, $cuando = 'DATE(NOW())') {
		global $wpdb;
		$query = "SELECT distinct user_id
				FROM wp_analiticas
				WHERE user_id !=0
				AND DATE( created_at ) = $cuando
				LIMIT $cantidad";
		$users_id = $wpdb->get_col($query);
		$users = [];
		foreach ($users_id as $user_id) {
			$users [] = [
				'url' => get_author_posts_url($user_id),
				'nombre' => get_the_author_meta('display_name', $user_id)
			];
		}
		return $users;
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

		$noBootSQL = self::_getNoBootSQL();

		$query = "select sum(s.total) as total, s.dia, 'total_visitas' as tipo
					from (
					    select se.total total, date(se.created_at) dia
					    from wp_seguimientos se
						where $noBootSQL
						$andPostId
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
			'totales' => $totales [0]->total,
			'totales_hoy' => array_pop($hoy)->total
		);
	}

	public static function getVisitasUnicasByPostId($post_id) {
		$totales = self::getTotalVisitasUnicasPorIP(false, $post_id, false);
		$hoy = self::getTotalVisitasUnicasPorIP(false, $post_id, true);
		return array(
			'unicas' => $totales [0]->total,
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
		$andPostId = ($post_id) ? " and post_id = $post_id " : '';
		$groupBy = ($agrupadoPorDia) ? 'group by s.dia' : '';
		$noBootSQL = self::_getNoBootSQL();
		$query = "SELECT COUNT( s.ip ) AS total, s.dia, 'total_unicas' as tipo
					FROM (
						SELECT DISTINCT ip, DATE( created_at ) dia
						FROM wp_seguimientos
						where $noBootSQL
						$andPostId
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
	 *        Cantidad
	 * @param string $cuando
	 *        HOY o AYER. Default: HOY
	 */
	public static function getTotalVisitasPorHora($cantidad = 24, $cuando = Utils::HOY) {
		if ($cuando == Utils::HOY) {
			$cuandoQuery = 'date(now())';
			$cuandoAlias = 'totales_hora_hoy';
		} elseif ($cuando == Utils::AYER) {
			$cuandoQuery = 'date(now())-1';
			$cuandoAlias = 'totales_hora_ayer';
		}
		global $wpdb;
		$noBootSQL = self::_getNoBootSQL();
		$query = "SELECT CONCAT( TIME_FORMAT( sh.created_at,  '%H' ) ,  ':00' ) hora, COUNT( * ) total,  '$cuandoAlias' AS tipo
				FROM wp_seguimientos_horas sh, wp_seguimientos s
				WHERE sh.seguimiento_id = s.ID
					and $noBootSQL
					and date(sh.created_at) = $cuandoQuery
				GROUP BY hora
				ORDER BY hora
				LIMIT " . $cantidad;
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve el número total de visitas únicas entre user/post en las últimas 24 horas
	 *
	 * @param number $cantidad
	 *        Cantidad
	 * @param string $cuando
	 *        HOY o AYER. Default: HOY
	 */
	public static function getUnicasVisitasPorHora($cantidad = 24, $cuando = Utils::HOY) {
		if ($cuando == Utils::HOY) {
			$cuandoQuery = 'date(now())';
			$cuandoAlias = 'unicas_hora_hoy';
		} elseif ($cuando == Utils::AYER) {
			$cuandoQuery = 'date(now())-1';
			$cuandoAlias = 'unicas_hora_ayer';
		}
		global $wpdb;
		$noBootSQL = self::_getNoBootSQL();
		$query = "SELECT b.hora, COUNT( b.total ) total,  '$cuandoAlias' AS tipo
				FROM (
					SELECT CONCAT( TIME_FORMAT( sh.created_at,  '%H' ) ,  ':00' ) hora, COUNT( * ) total
					FROM wp_seguimientos_horas sh, wp_seguimientos s
					WHERE sh.seguimiento_id = s.ID
						and $noBootSQL
						and date(sh.created_at) = $cuandoQuery
					GROUP BY hora, s.post_id
					ORDER BY hora
				) b
				GROUP BY b.hora
				LIMIT " . $cantidad;
		return $wpdb->get_results($query);
	}

	/**
	 * Juntar valores de 2 arrays en base a que
	 *
	 * @param string $que
	 * @param array<array> $listaArraysVisitas
	 *        Lista de arrays a juntar por su misma que
	 * @return array
	 */
	public static function juntarValoresPor($que, $listaArraysVisitas = []) {
		$result = [];
		foreach ($listaArraysVisitas as $lista) {
			foreach ($lista as $l) {
				if (!isset($result [$l->{$que}])) {
					$obj = new stdClass();
				} else {
					$obj = $result [$l->{$que}];
				}
				$obj->{$que} = $l->{$que};
				$obj->{$l->tipo} = $l->total;
				$result [$l->{$que}] = $obj;
			}
		}
		return array_values($result);
	}

	/**
	 * Formatea la información añadiendo un 0 a los días que no tengan nada acumulado
	 *
	 * @param array $totalPorDia
	 * @return array
	 */
	public static function formatearDias($totalPorDia) {

		function _formatDia($dia) {
			if ($dia < 10) {
				return date('Y-m-0') . "$dia";
			}
			return date('Y-m-') . "$dia";
		}
		$result = [];
		$numeroDeDias = intval(date("t", date('m')));
		for ($i = 1; $i < $numeroDeDias; $i++) {
			foreach ($totalPorDia as $t) {
				$dia = _formatDia($i);
				if ($dia == $t->dia) {
					$result [] = $t;
					continue 2;
				}
			}
			$obj = new stdClass();
			$obj->dia = $dia;
			$obj->total = "0";
			$result [] = $obj;
		}
		return $result;
	}

	public static function formatearMeses($totalPorMes) {
		$result = [];
		for ($i = 1; $i <= 12; $i++) {
			foreach ($totalPorMes as $t) {
				if ($i == $t->mes) {
					$result [] = $t;
					continue 2;
				}
			}
			$obj = new stdClass();
			$obj->mes = "$i";
			$obj->total = "0";
			$result [] = $obj;
		}
		return $result;
	}

	/**
	 * Devuelve el SQL para filtrar los boot de las consultas SQL
	 *
	 * @return string
	 */
	private static function _getNoBootSQL() {
		return '(LOWER(user_agent) not like "%bot%"
				and LOWER(user_agent) not like "%feed%"
				and LOWER(user_agent) not like "%compatible%")';
	}

}
