<?php

namespace config;

use Controllers\AnaliticaController;
use Models\Analitica;

/**
 *
 * @author chema
 */
class ConfigAnalitica {

	/**
	 * Registramos el menú
	 */
	public static function addActionAdminMenu() {
		add_action('admin_menu', function () {
			$page_title = 'Analitica';
			$menu_title = 'Analitica';
			$capability = 'list_users';
			$menu_slug = 'analitica';
			$icon = 'dashicons-chart-line';
			add_menu_page($page_title, $menu_title, $capability, $menu_slug, function () {
				if (! current_user_can('list_users')) {
					wp_die('You do not have sufficient permissions to access this page.');
				}
				$controller = new AnaliticaController();
				$controller->getIndex();
			}, $icon);
		});
	}

	/**
	 * Esta acción será llamada cada vez que se cargue la web
	 */
	public static function addActionWP() {
		add_action('wp', function () {
			try {
				$analitica = new Analitica();
				$analitica->save();
			} catch ( Exception $e ) {
				Utils::debug('No se pudo guardar la Analitica ?');
			}
		});
	}
}

ConfigAnalitica::addActionAdminMenu();
ConfigAnalitica::addActionWP();
