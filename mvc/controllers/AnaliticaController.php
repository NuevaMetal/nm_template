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

		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}seguimientos (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`analitica_id` bigint(20) UNSIGNED NOT NULL,
			`post_id` bigint(20) UNSIGNED NOT NULL,
			`total` integer(10) UNSIGNED NOT NULL DEFAULT 1,
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

		/**
		 * Registro que cada día se borren los registros de seguimientos_horas de hace 2 días
		 */
		register_activation_hook(__FILE__, function () {
			// Si no está definido lo ponemos
			if (!wp_next_scheduled('eliminar_seguimientos_horas_2_dias')) {
				wp_schedule_event(time(), 'daily', 'eliminar_seguimientos_horas_2_dias');
			}
		});

		add_action('eliminar_seguimientos_horas_2_dias', function () {
			// Borrar los registros de los seguimientos_horas de hace 2 días
			global $wpdb;
			$result = $wpdb->query('DELETE FROM `wp_seguimientos_horas`
						WHERE date(created_at) < date(now())-1');
			if (!$result) {
				Utils::info("> No se borraron ningún registro en horas (?)");
			}
		});
	}

	/**
	 * Eliminar las tablas de analítica
	 *
	 * @return void
	 */
	public static function uninstall() {
		Utils::debug("> AnaliticaController->uninstall() ");
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}seguimientos_horas ");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}seguimientos ");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}analiticas ");
		/**
		 * Elimino el cron que borraba los registros de seguimientos_horas de hacía 2 días
		 */
		register_deactivation_hook(__FILE__, function () {
			// Si está definido lo quitamos
			if (wp_next_scheduled('eliminar_seguimientos_horas_2_dias')) {
				wp_clear_scheduled_hook('eliminar_seguimientos_horas_2_dias');
			}
		});
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
		$content = $this->render('plugin/analitica', [
			'logueados_hoy' => $logueados_hoy,
			'hay_logueados_hoy' => count($logueados_hoy) > 0,
			'logueados_ayer' => $logueados_ayer,
			'hay_logueados_ayer' => count($logueados_ayer) > 0,
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
			case Analitica::TOTAL_VISITAS :
				$totalVisitas = Analitica::getTotalVisitas($cant);
				$totalVisitasUnicas = Analitica::getTotalVisitasUnicasPorIP($cant);
				$totalPostUnicosVistos = Analitica::getTotalPostUnicosVistos($cant);
				$result = self::_juntarValoresPor('dia', [
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
				$unicasPorHora = Analitica::getUnicasVisitasPorHora($cant);
				$result = self::_juntarValoresPor('hora', [
					$totalPorHora,
					$unicasPorHora
				]);
				$result = self::_formatearHoras($result);
				$xKey = 'hora';
				$yKeys = [
					'totales_hora',
					'unicas_hora'
				];
				$labels = [
					'Total por hora',
					'Únicas por hora'
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
			$obj->totales_hora = "0";
			$obj->unicas_hora = "0";
			$result [] = $obj;
		}
		return $result;
	}

	/**
	 * Juntar valores de 2 arrays en base a que
	 *
	 * @param string $que
	 * @param array<array> $listaArraysVisitas
	 *        Lista de arrays a juntar por su misma que
	 * @return array
	 */
	private function _juntarValoresPor($que, $listaArraysVisitas = []) {
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

}
