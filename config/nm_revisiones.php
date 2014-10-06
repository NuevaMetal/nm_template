<?php

// use Controllers\AnaliticaController ;
require_once (dirname(__FILE__) . '/../mvc/controllers/RevisionesController.php');

/**
 * Registramos las alertas.
 * Mostrar alerta indicando el número total de revisiones pendientes que hay
 */
add_action('admin_notices', function () {
	global $wpdb, $current_user;
	$user = User::find($current_user->ID);
	if ($user->canEditor()) {
		$numTotalPorRevisar = Revision::getTotalPorRevisar();
		if ($numTotalPorRevisar) {
			if ($numTotalPorRevisar == 1) {
				$msg = 'Hay ' . $numTotalPorRevisar . '</span> nueva revisión pendiente';
			} else {
				$msg = 'Hay ' . $numTotalPorRevisar . '</span> nuevas revisiones pendientes';
			}
			$urlRevisiones = admin_url('admin.php?page=revisiones');
			echo '
		<div class="update-nag">
		  <strong>¡Ey ' . $user->display_name . '!</strong> ' . $msg . ' en <a href="' . $urlRevisiones . '">Revisiones</a>
		</div>';
		}
	}
});

/**
 * Registramos el menú en para el admin
 */
add_action('admin_menu', function () {
	$page_title = 'Revisiones';
	$num = Revision::getTotalPorRevisar();
	$menu_title = 'Revisiones
		<span class="update-plugins">
			<span class="plugin-count">' . $num . '</span>
		</span>';
	$capability = 'edit_others_posts';
	$menu_slug = 'revisiones';
	$function = 'nm_revisiones_index';
	$icon = 'dashicons-clipboard';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon);
	// Add submenu page with same slug as parent to ensure no duplicates
	$sub_menu_title = 'Revisiones';
	add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);

	$submenu_page_title = 'Usuarios baneados';
	$submenu_title = 'Baneos';
	$submenu_slug = 'class-revisiones-ban';
	add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, function () {
		// Mostrar tabla de usuarios baneados en revisiones
		if (!current_user_can('edit_others_posts')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}
		$controller = new RevisionesController();
		$controller->getBanIndex();
	});
});

/**
 * Mostrar tablas de revisiones
 */
function nm_revisiones_index() {
	if (!current_user_can('edit_others_posts')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	$controller = new RevisionesController();
	$controller->getIndex();
}
