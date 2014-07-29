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
	protected function _renderBase($args = []) {
		$current_user = wp_get_current_user();

		$menuPrincipal = $this->render('menu/principal', [
			'home_url' => get_home_url(),
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'user_avatar' => get_avatar($current_user->ID)
		]);

		$menuFooter = $this->render('menu/footer', [
			'home_url' => get_home_url()
		]);
		$args ['menuPrincipal'] = $menuPrincipal;
		$args ['menuFooter'] = $menuFooter;

		return $this->renderPage('base', $args);
	}

}
