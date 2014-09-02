<?php
//namespace Controllers\AnaliticaController;
//use Controllers\BaseController;
require_once 'BaseController.php';
require_once 'HomeController.php';
/**
 * Controlador principal de la web
 *
 * @author chemaclass
 *
 */
class AnaliticaController extends BaseController {

	/**
	 * Create table and register an option when activate
	 *
	 * @return void
	 */
	public static function install() {
		Utils::debug("> AnaliticaController->install() ");
		global $wpdb;
		// Create table
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}analiticas (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) UNSIGNED NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT 0,
			`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`ID`),
			FOREIGN KEY (`user_id`) REFERENCES `{$wpdb->prefix}users`(`ID`) ON DELETE SET NULL,
			UNIQUE KEY (user_id, created_at)
			)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($query);

		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}seguimientos (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`analitica_id` bigint(20) UNSIGNED NOT NULL,
			`post_id` bigint(20) UNSIGNED NOT NULL,
			`ip` varchar(45) NOT NULL,
			`user_agent` varchar(200) NOT NULL,
			`referer` varchar(2000) NOT NULL,
			`request_time` varchar(50) NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT 0,
			`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`ID`),
			FOREIGN KEY (`analitica_id`) REFERENCES `{$wpdb->prefix}users`(`ID`) ON DELETE CASCADE,
			FOREIGN KEY (`post_id`) REFERENCES `{$wpdb->prefix}posts`(`ID`) ON DELETE SET NULL,
			UNIQUE KEY (analitica_id, post_id)
			)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($query);

		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}seguimientos_horas (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`seguimiento_id` bigint(20) UNSIGNED NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`ID`),
			FOREIGN KEY (`seguimiento_id`) REFERENCES `{$wpdb->prefix}seguimientos`(`ID`) ON DELETE CASCADE
			)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($query);
	}

	/**
	 * Drop tables
	 *
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}seguimientos_horas ");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}seguimientos ");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}analiticas ");
	}

	/**
	 * Pintar el index
	 *
	 * @param unknown $content
	 * @return string
	 */
	public function getIndex() {
		$template_url = get_template_directory_uri();
		$content = $this->render('plugin/analitica', [
			'analiticas' => Analitica::all(),
			'public_directory' => get_bloginfo('template_directory') . '/public',
			'template_url' => $template_url
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
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
	public static function getTotalVisitas($cantidad = 50) {
		global $wpdb;
		$query = 'select sum(s.total) as total_visitas, s.dia
					from (
					    select distinct post_id, count(seh.seguimiento_id) total, date(se.created_at) dia
					    from wp_seguimientos se, wp_seguimientos_horas seh
						where se.ID = seh.seguimiento_id
					    group by dia, post_id) s
					group by s.dia limit ' . $cantidad;
		return $wpdb->get_results($query);
	}

	public static function getTotalPostUnicosVistos($cantidad = 50) {
		global $wpdb;
		$query = 'select count(s.post_id) as total_posts_unicos, s.dia
					from (
					  select distinct post_id, date(created_at) dia
					  from wp_seguimientos
					  group by dia, post_id) s
					group by s.dia LIMIT ' . $cantidad;
		return $wpdb->get_results($query);
	}

	public static function getTotalVisitasPorHora($cantidad = 50) {
		global $wpdb;
		$query = "select time_format(created_at,'%H') hora, count(*) total
					from wp_seguimientos
					group by hora LIMIT " . $cantidad;
		return $wpdb->get_results($query);
	}

	/**
	 * Para la analítica por Ajax
	 *
	 * @param string $tabla
	 * @return array
	 */
	public static function getByTabla($tabla, $cant = 30) {
		switch ($tabla) {
			case Analitica::TOTAL_USERS :
				$totalRegistros = self::getTotalRegistrosPorDia($cant);
				$result = $totalRegistros;
				$xKey = 'dia';
				$yKeys = [
					'total'
				];
				$labels = [
					'Total'
				];
				break;
			case Analitica::TOTAL_VISITAS :
				$totalVisitas = self::getTotalVisitas($cant);
				$result = $totalVisitas;
				$xKey = 'dia';
				$yKeys = [
					'total_visitas'
				];
				$labels = [
					'Visitas totales'
				];
				break;
			case Analitica::TOTAL_VISITAS_USERS :
				$totalVisitasUsersLogueados = self::getTotalVisitasUsersLogueados($cant);
				$result = $totalVisitasUsersLogueados;
				$xKey = 'dia';
				$yKeys = [
					'total_users_logueados'
				];
				$labels = [
					'Usuarios logueados'
				];
				break;
			case Analitica::TOTAL_VISITAS_POST :
				$totalPostUnicosVistos = self::getTotalPostUnicosVistos($cant);
				$result = $totalPostUnicosVistos;
				$xKey = 'dia';
				$yKeys = [
					'total_posts_unicos'
				];
				$labels = [
					'Visitas únicas'
				];
				break;
			case Analitica::TOTAL_VISITAS_HORA :
				$totalPorHora = self::getTotalVisitasPorHora($cant);
				$result = self::_formatearHoras($totalPorHora);

				$xKey = 'hora';
				$yKeys = [
					'total'
				];
				$labels = [
					'Total por hora'
				];
				break;
		}
		$json = [
			'data' => $result,
			'xkey' => $xKey,
			'ykeys' => $yKeys,
			'labels' => $labels,
		];
		return $json;
	}

	private function _formatearHoras($totalPorHora){
		$result= [];
		// Las horas vacías ponemos un 0
		for ($i = 0; $i < 24; $i++) {
			foreach ($totalPorHora as $t) {
				if ($i == $t->hora) {
					$result [] = $t;
					continue 2;
				}
			}
			$obj = new stdClass();
			$obj->hora = "$i";
			$obj->total = "0";
			$result [] = $obj;
		}
		return $result;
	}

}
