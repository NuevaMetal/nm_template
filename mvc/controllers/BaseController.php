<?php
/**
 *
 * @author chema
 *
 */
abstract class BaseController extends ChesterBaseController {

	/**
	 * Pintar la plantilla base con los menus
	 *
	 * @param array $args
	 *        Lista de parámetros a pasar a la plantilla base
	 */
	protected function _renderPageBase($args = []) {
		$current_user = wp_get_current_user();

		if ($current_user->ID) {
			$current_user->url = get_author_posts_url($current_user->ID);
		}

		$redirect = $_SERVER [REQUEST_URI];

		$menuPerfil = $this->render('menu/perfil', [
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'login_url' => wp_login_url($redirect),
			'home_url' => get_home_url(),
			'user_avatar' => get_avatar($current_user->ID),
				'redirect_to' => $redirect,
		]);

		$menuPrincipal = $this->render('menu/principal', [
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'login_url' => wp_login_url($redirect),
			'home_url' => get_home_url(),
			'user_avatar' => get_avatar($current_user->ID),
			'redirect_to' => $redirect,
		]);

		$menuFooter = $this->render('menu/footer', [
			'home_url' => get_home_url()
		]);

		$args ['menuPrincipal'] = $menuPrincipal;
		$args ['menuPerfil'] = $menuPerfil;
		$args ['menuFooter'] = $menuFooter;

		return $this->renderPage('base', $args);
	}

	/**
	 * Renderizar la home con
	 *
	 * @param array $args
	 */
	protected function _renderPage($args = []) {
		$next_posts_link = get_next_posts_link();
		$previous_posts_link = get_previous_posts_link();

		$args ['next_posts_link'] = $next_posts_link;
		$args ['previous_posts_link'] = $previous_posts_link;

		return $this->render('page', $args);
	}

	protected function _renderBusqueda($args = []) {
		$next_posts_link = get_next_posts_link();
		$previous_posts_link = get_previous_posts_link();

		$args ['next_posts_link'] = $next_posts_link;
		$args ['previous_posts_link'] = $previous_posts_link;

		return $this->render('busqueda_chester', $args);
	}

}
