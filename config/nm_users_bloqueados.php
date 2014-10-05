<?php

// use Controllers\AnaliticaController ;
require_once (dirname(__FILE__) . '/../mvc/controllers/UserBloqueadoController.php');

/**
 * Registramos el menú en para el admin
 */
add_action('admin_menu', function () {
	$page_title = 'Usuarios Bloqueados';
	$num = UserBloqueado::getTotal();
	$menu_title = 'Usuarios Bloqueados';
	if ($num) {
		$menu_title .= '<span class="update-plugins">
			<span class="plugin-count">' . $num . '</span>
		</span>';
	}
	$capability = 'edit_others_posts';
	$menu_slug = 'usuarios-bloqueados';
	$icon = 'dashicons-no';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, function () {
		$user = Utils::getCurrentUser();
		if (!$user || !$user->canEditor()) {
			wp_die('You do not have sufficient permissions to access this page.');
		}
		$controller = new UserBloqueadoController();
		$controller->getIndex();
	}, $icon);
});


