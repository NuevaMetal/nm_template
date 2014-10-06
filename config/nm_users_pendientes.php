<?php

// use Controllers\AnaliticaController ;
require_once (dirname(__FILE__) . '/../mvc/controllers/UserPendienteController.php');

/**
 * Registramos las alertas
 */
add_action('admin_notices', function () {
	global $wpdb, $current_user;
	$user = User::find($current_user->ID);
	if ($user->canEditor()) {
		$numTotalPorRevisar = UserPendiente::getTotal();
		if ($numTotalPorRevisar) {
			if ($numTotalPorRevisar == 1) {
				$msg = 'Hay ' . $numTotalPorRevisar . '</span> nuevo usuario pendiente';
			} else {
				$msg = 'Hay ' . $numTotalPorRevisar . '</span> nuevos usuarios pendientes';
			}
			$urlRevisiones = admin_url('admin.php?page=usuarios-pendientes');
			echo '
			<div class="update-nag">
			  <strong>¡Ey ' . $user->display_name . '!</strong> ' . $msg . ' en <a href="' . $urlRevisiones . '">Pendientes</a>
			</div>';
		}
	}
});

/**
 * Registramos el menú en para el admin
 */
add_action('admin_menu', function () {
	$page_title = 'Usuarios Pendientes';
	$num = UserPendiente::getTotal();
	$menu_title = 'Usuarios Pendientes';
	if ($num) {
		$menu_title .= '<span class="update-plugins">
			<span class="plugin-count">' . $num . '</span>
		</span>';
	}
	$capability = 'edit_others_posts';
	$menu_slug = 'usuarios-pendientes';
	$icon = 'dashicons-groups';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, function () {
		if (!current_user_can('edit_others_posts')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}
		$controller = new UserPendienteController();
		$controller->getIndex();
	}, $icon);
});


