<?php

// use Controllers\AnaliticaController ;
require_once (dirname(__FILE__) . '/../mvc/controllers/UserPendienteController.php');

/**
 * Registramos las alertas
 */
add_action('admin_notices', function () {
	// 	global $wpdb, $current_user;
	// 	$user = User::find($current_user->ID);
	// 	if ($user->isEditor()) {
	// 		$numTotalPorRevisar = UsersPendientes::getTotalPorRevisar();
	// 		$urlRevisiones = admin_url('admin.php?page=users-pendientes');
	// 		echo '<br>
	// 		<div class="error">
	// 		  <strong>¡Ey ' . $user->display_name . '!</strong> ' . $msg . ' en <a href="' . $urlRevisiones . '">Revisiones</a>
	// 		</div>';
	// 	}
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


