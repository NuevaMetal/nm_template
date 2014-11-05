<?php
require_once 'BaseController.php';

/**
 * Controlador principal de la web
 *
 * @author chemaclass
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
				$total = Analitica::getTotalRegistrosPorDia($cant);
				$result = Analitica::formatearDias($total);
				$xKey = 'dia';
				$yKeys = [
					'total'
				];
				$labels = [
					'Usuarios registrados'
				];
				break;

			case Analitica::TOTAL_VISITAS_USERS :
				$total = Analitica::getTotalVisitasUsersLogueados($cant);
				$result = Analitica::formatearDias($total);
				$xKey = 'dia';
				$yKeys = [
					'total'
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
}
