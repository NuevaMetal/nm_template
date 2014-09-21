<?php
//namespace Controllers\BaseController;
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
			},
			'substr' => function ($text, $params = []) {
				return I18n::substr($text, $params);
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
		$template_url = get_template_directory_uri();
		$menuArgs = [
			'current_user' => $current_user->ID != 0 ? $current_user : false,
			'login_url' => wp_login_url($redirect),
			'home_url' => get_home_url(),
			'user_avatar' => get_avatar($current_user->ID),
			'redirect_to' => $redirect,
			'template_url' => $template_url
		];
		$menuPerfil = $this->render('menu/perfil', $menuArgs);

		$menuPrincipal = $this->render('menu/principal', $menuArgs);

		$menuFooter = $this->render('menu/footer', [
			'home_url' => get_home_url()
		]);

		$args ['menuPrincipal'] = $menuPrincipal;
		$args ['menuPerfil'] = $menuPerfil;
		$args ['menuFooter'] = $menuFooter;
		$args ['template_url'] = $template_url;
		$args ['blog_name'] = get_bloginfo('name');
		$args ['template_url'] = get_template_directory_uri();
		$args ['poner_analitica'] = ($_SERVER ["SERVER_NAME"] == URL_PRODUCCION);
		return $this->renderPage('base', $args);
	}

	/**
	 * Devuelve la pintada con los datos básicos para cualquier plantilla como
	 * el usuario actual y template_url
	 *
	 * @param string $template
	 *        plantilla a pintar
	 * @param array $args
	 *        argumentos adicionales para esa plantilla
	 */
	protected function _render($template, $args = []) {
		return $this->render($template, array_merge($args, [
			'user' => User::find(wp_get_current_user()->ID),
			'template_url' => get_template_directory_uri()
		]));
	}

	/**
	 * Pintar la plantilla base para los plugins
	 *
	 * @param array $args
	 *        Lista de parámetros a pasar a la plantilla base de plugins
	 */
	protected function _renderPageBasePlugin($args = []) {
		$current_user = Utils::getCurrentUser();

		$template_url = get_template_directory_uri();

		// 		$args ['menuPrincipal'] = $menuPrincipal;
		// 		$args ['menuPerfil'] = $menuPerfil;
		// 		$args ['menuFooter'] = $menuFooter;


		$args ['current_user'] = $current_user;
		$args ['template_url'] = $template_url;
		$args ['blog_name'] = get_bloginfo('name');
		echo $this->render('base_plugin', $args);
	}

	/**
	 * Renderizar la home con
	 *
	 * @param array $args
	 */
	protected function _renderPage($args = []) {
		$args ['template_url'] = get_template_directory_uri();
		$next_posts_link = get_next_posts_link();
		$previous_posts_link = get_previous_posts_link();

		$args ['next_posts_link'] = $next_posts_link;
		$args ['previous_posts_link'] = $previous_posts_link;

		return $this->render('page', $args);
	}

	protected function _renderBusqueda($args = []) {
		$args ['template_url'] = get_template_directory_uri();
		$next_posts_link = get_next_posts_link();
		$previous_posts_link = get_previous_posts_link();

		$args ['next_posts_link'] = $next_posts_link;
		$args ['previous_posts_link'] = $previous_posts_link;

		return $this->render('busqueda', $args);
	}

	protected function _renderAutor($args = []) {
		$args ['template_url'] = get_template_directory_uri();
		return $this->render('autor', $args);
	}

	/**
	 * 404.php
	 */
	public function getError($num) {
		$content = $this->render('error', array(
			'num' => $num
		));
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

}
