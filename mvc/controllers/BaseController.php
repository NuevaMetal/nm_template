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
		$menuPerfil = $this->render('menu/perfil', [
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'user_avatar' => get_avatar($current_user->ID)
		]);

		//dd($menuPerfil);

		$menuPrincipal = $this->render('menu/principal', [
			'home_url' => get_home_url()
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
	protected function _renderBusqueda($args = []) {
		$next_posts_link = get_next_posts_link();
		$previous_posts_link = get_previous_posts_link();

		$args ['next_posts_link'] = $next_posts_link;
		$args ['previous_posts_link'] = $previous_posts_link;

		return $this->render('busqueda', $args);
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

		return $this->render('post', $args);
	}

}
