<?php
require_once dirname(__FILE__) . '/../i18n/I18n.php';
/**
 *
 * @author chema
 *
 */
abstract class BaseController extends ChesterBaseController {

	public function __construct() {
		parent::__construct();
		$this->template->setHelpers(array(
			'trans' => function ($text, $params = []) {
				return I18n::trans($text, $params);
			},
			'transu' => function ($text, $params = []) {
				return I18n::transu($text, $params);
			},
			'transupper' => function ($text, $params = []) {
				return I18n::transupper($text, $params);
			}
		));
	}

	/**
	 * Pintar la plantilla base con los menus
	 *
	 * @param array $args
	 *        Lista de parámetros a pasar a la plantilla base
	 */
	protected function _renderPageBase($args = []) {
		$current_user = Utils::getCurrentUser();

		$redirect = $_SERVER [REQUEST_URI];

		$menuPerfil = $this->render('menu/perfil', [
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'login_url' => wp_login_url($redirect),
			'home_url' => get_home_url(),
			'user_avatar' => get_avatar($current_user->ID),
			'redirect_to' => $redirect
		]);

		$menuPrincipal = $this->render('menu/principal', [
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'login_url' => wp_login_url($redirect),
			'home_url' => get_home_url(),
			'user_avatar' => get_avatar($current_user->ID),
			'redirect_to' => $redirect
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
