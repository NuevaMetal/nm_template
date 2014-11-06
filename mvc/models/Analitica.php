<?php

namespace Models;

/**
 * Analítica
 *
 * @author chema
 */
class Analitica extends ModelBase {
	public static $table = "analiticas";

	const TOTAL_USERS = 'total-users';

	const TOTAL_VISITAS = 'total-visitas';

	const TOTAL_VISITAS_USERS = 'total-visitas-users';

	const TOTAL_VISITAS_HORA = 'total-visitas-hora';

	/*
	 * Miebros
	 */
	public $user_id;

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
	 *
	 * @param string $ID
	 * @return NULL
	 */
	public function save() {
		global $wpdb, $post;
		$user = wp_get_current_user();
		// Comprobamos si existe
		$query = "SELECT * FROM {$wpdb->prefix}" . static::$table . "
		WHERE user_id = $user->ID AND DATE(created_at) = CURRENT_DATE;";
		// Utils::debug($query);
		$analitica = $wpdb->get_row($query);

		if ($analitica) {
			// Si existe actualizamos
			$wpdb->query($wpdb->prepare("
					UPDATE {$wpdb->prefix}" . static::$table . "
					SET updated_at = now()
					WHERE ID = %d", $analitica->ID));
			$this->ID = $analitica->ID;
			// dd($analitica);
		} else {
			// Si no existe lo creamos
			$result = $wpdb->query($wpdb->prepare("
				INSERT {$wpdb->prefix}" . static::$table . " (user_id, created_at, updated_at)
				VALUES (%d, null, null);", $user->ID));
			$this->ID = $wpdb->insert_id;
		}
	}

	/**
	 * Devuelve el número total de registros por día
	 *
	 * @param number $cantidad
	 * @return multitype:string multitype:string unknown
	 */
	public static function getTotalRegistrosPorDia($cantidad = 31, $mes = 'MONTH(NOW())', $ano = 'YEAR(NOW())') {
		global $wpdb;
		$query = "SELECT DATE( user_registered ) dia, COUNT( * ) total
				FROM wp_users
				WHERE MONTH( user_registered ) = $mes
				AND YEAR( user_registered ) = $ano
				GROUP BY dia
				ORDER BY dia DESC
				LIMIT $cantidad";
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve el número total de visitas por día
	 *
	 * @param number $cantidad
	 * @return multitype:string multitype:string unknown
	 */
	public static function getTotalVisitasUsersLogueados($cantidad = 31, $mes = 'MONTH(NOW())', $ano = 'YEAR(NOW())') {
		global $wpdb;
		$query = "SELECT DATE( created_at ) dia, count(*) total, user_id
				FROM wp_analiticas
				WHERE user_id != 0
				AND MONTH( created_at ) = $mes
				AND YEAR( created_at ) = $ano
				GROUP BY dia
				ORDER BY dia DESC limit $cantidad";
		return $wpdb->get_results($query);
	}

	/**
	 * Devuelve una lista con los nombres (y url) de los users logueados
	 *
	 * @param number $cantidad
	 *        	Límite máximo de nombres a obtener
	 * @param string $cuando
	 *        	fecha en SQL de cuándo se quiere dicha lista de nombres.
	 *        	Por defecto será el día actual
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
			$users[] = [
				'url' => get_author_posts_url($user_id),
				'nombre' => get_the_author_meta('display_name', $user_id)
			];
		}
		return $users;
	}

	/**
	 * Juntar valores de 2 arrays en base a que
	 *
	 * @param string $que
	 * @param array<array> $listaArraysVisitas
	 *        	Lista de arrays a juntar por su misma que
	 * @return array
	 */
	public static function juntarValoresPor($que, $listaArraysVisitas = []) {
		$result = [];
		foreach ($listaArraysVisitas as $lista) {
			foreach ($lista as $l) {
				if (! isset($result[$l->{$que}])) {
					$obj = new stdClass();
				} else {
					$obj = $result[$l->{$que}];
				}
				$obj->{$que} = $l->{$que};
				$obj->{$l->tipo} = $l->total;
				$result[$l->{$que}] = $obj;
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
		for($i = 1; $i < $numeroDeDias; $i ++) {
			foreach ($totalPorDia as $t) {
				$dia = _formatDia($i);
				if ($dia == $t->dia) {
					$result[] = $t;
					continue 2;
				}
			}
			$obj = new stdClass();
			$obj->dia = $dia;
			$obj->total = "0";
			$result[] = $obj;
		}
		return $result;
	}
	public static function formatearMeses($totalPorMes) {
		$result = [];
		for($i = 1; $i <= 12; $i ++) {
			foreach ($totalPorMes as $t) {
				if ($i == $t->mes) {
					$result[] = $t;
					continue 2;
				}
			}
			$obj = new \stdClass();
			$obj->mes = "$i";
			$obj->total = "0";
			$result[] = $obj;
		}
		return $result;
	}
}
