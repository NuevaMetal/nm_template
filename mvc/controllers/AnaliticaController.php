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
		Utils::debug("> AnaliticaController->uninstall() ");
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
			case Analitica::TOTAL_VISITAS :
				$totalVisitas = Analitica::getTotalVisitas($cant);
				$totalVisitasUnicas = Analitica::getTotalVisitasUnicasPorIP($cant);
				$totalPostUnicosVistos = Analitica::getTotalPostUnicosVistos($cant);
				$result = self::_juntarValoresPorDia([
					$totalVisitasUnicas,
					$totalVisitas,
					$totalPostUnicosVistos
				]);
				$xKey = 'dia';
				$yKeys = [
					'total_visitas',
					'total_posts_unicos',
					'total_unicas'
				];
				$labels = [
					'Visitas totales',
					'Entradas únicas vistas',
					'Visitas únicas por IP'
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
			case Analitica::TOTAL_VISITAS_HORA :
				$totalPorHora = Analitica::getTotalVisitasPorHora($cant);
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
			'labels' => $labels
		];
		return $json;
	}

	/**
	 * Añade un 0 a las horas que no tengan resultados.
	 * Para que se devuelva siempre un valor para cada hora
	 *
	 * @param unknown $totalPorHora
	 * @return multitype:stdClass unknown
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
			$obj->total = "0";
			$result [] = $obj;
		}
		return $result;
	}

	/**
	 *
	 * @param unknown $totalVisitasUnicas
	 * @param unknown $totalVisitas
	 */
	private function _juntarValoresPorDia($listaArraysVisitas = []) {
		$result = [];
		foreach ($listaArraysVisitas as $lista) {
			foreach ($lista as $l) {
				if (!isset($result [$l->dia])) {
					$obj = new stdClass();
				} else {
					$obj = $result [$l->dia];
				}
				$obj->dia = $l->dia;
				$obj->{$l->tipo} = $l->total;
				$result [$l->dia] = $obj;
			}
		}
		return array_values($result);
	}

}
