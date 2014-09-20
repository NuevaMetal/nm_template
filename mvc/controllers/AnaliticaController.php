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
	 * Crear las tablas para la analítica
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
	}

	/**
	 * Eliminar las tablas de analítica
	 *
	 * @return void
	 */
	public static function uninstall() {
		Utils::debug("> AnaliticaController->uninstall() ");
		global $wpdb;
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
		$logueados_hoy = Analitica::getUsersLogueados(50);
		$logueados_ayer = Analitica::getUsersLogueados(50, 'date(now())-1');

		$total_logueados_hoy = count($logueados_hoy);
		$total_logueados_ayer = count($logueados_ayer);

		$content = $this->render('plugin/analitica', [
			'logueados_hoy' => $logueados_hoy,
			'hay_logueados_hoy' => $total_logueados_hoy > 0,
			'total_logueados_hoy' => $total_logueados_hoy,
			'logueados_ayer' => $logueados_ayer,
			'hay_logueados_ayer' => $total_logueados_ayer > 0,
			'total_logueados_ayer' => $total_logueados_ayer,
			'public_directory' => $template_url . '/public',
			'template_url' => $template_url
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
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
				$totalRegistros = Analitica::getTotalRegistrosPorDia($cant);
				$result = $totalRegistros;
				$xKey = 'dia';
				$yKeys = [
					'total'
				];
				$labels = [
					'Total'
				];
				break;

			case Analitica::TOTAL_VISITAS_USERS :
				$totalVisitasUsersLogueados = Analitica::getTotalVisitasUsersLogueados($cant);
				$result = $totalVisitasUsersLogueados;
				$xKey = 'dia';
				$yKeys = [
					'total_users_logueados'
				];
				$labels = [
					'Usuarios logueados'
				];
				break;
		}
		$json = [
			'data' => $result,
			'xkey' => $xKey,
			'ykeys' => $yKeys,
			'labels' => $labels
		];
		return $json;
	}

	/**
	 * Añade un 0 a las horas que no tengan resultados.
	 * Para que se devuelva siempre un valor para cada hora
	 *
	 * @param array $totalPorHora
	 * @return array
	 */
	private function _formatearHoras($totalPorHora) {
		$result = [];
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
			$obj->totales_hora_hoy = "0";
			$obj->unicas_hora_hoy = "0";
			$obj->totales_hora_ayer = "0";
			$obj->unicas_hora_ayer = "0";
			$result [] = $obj;
		}
		return $result;
	}

}
