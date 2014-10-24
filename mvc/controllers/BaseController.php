<?php
// namespace Controllers\BaseController;
require_once dirname(__FILE__) . '/../i18n/I18n.php';
/**
 *
 * @author chema
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
	 *        	Lista de parámetros a pasar a la plantilla base
	 */
	protected function _renderPageBase($args = []) {
		$args['current_user'] = User::find(wp_get_current_user()->ID);
		$args['template_url'] = get_template_directory_uri();
		$args['blog_name'] = get_bloginfo('name');
		$args['poner_analitica'] = ($_SERVER["SERVER_NAME"] == URL_PRODUCCION);

		$args['alertas'] = $this->_getAlertas();

		return $this->renderPage('base', $args);
	}

	/**
	 * Devuelve las alertas que tenga el usuario actual pendientes
	 *
	 * @return array>View> Alertas
	 */
	private function _getAlertas() {
		$alertas = [];
		$user = Utils::getCurrentUser();

		if ($user && ($total = $user->getTotalMensajesRecibidosSinLeer())) {
			$msg = I18n::trans('user.tienes_mensajes_nuevos', [
				'total' => $total
			]);
			$alertas[] = $this->renderAlertaDanger($msg, I18n::transu('mensajes'), 'messages');
		}
		return $alertas;
	}

	/**
	 * Devuelve la pintada con los datos básicos para cualquier plantilla como
	 * el usuario actual y template_url
	 *
	 * @param string $template
	 *        	plantilla a pintar
	 * @param array $args
	 *        	argumentos adicionales para esa plantilla
	 */
	protected function _render($template, $args = []) {
		return $this->render($template, array_merge($args, [
			'current_user' => Utils::getCurrentUser(),
			'template_url' => get_template_directory_uri(),
			'home_url' => get_home_url()
		]));
	}

	/**
	 * Pintar la plantilla base para los plugins
	 *
	 * @param array $args
	 *        	Lista de parámetros a pasar a la plantilla base de plugins
	 */
	protected function _renderPageBasePlugin($args = []) {
		$template_url = get_template_directory_uri();
		$args['blog_name'] = get_bloginfo('name');
		echo $this->_render('base_plugin', $args);
	}

	/**
	 * page-*.php
	 */
	protected function _renderPage($args = []) {
		$next_posts_link = get_next_posts_link();
		$previous_posts_link = get_previous_posts_link();

		$args['next_posts_link'] = $next_posts_link;
		$args['previous_posts_link'] = $previous_posts_link;

		return $this->_render('page', $args);
	}
	/**
	 * search.php
	 *
	 * @param unknown $args
	 */
	protected function _renderBusqueda($args = []) {
		$next_posts_link = get_next_posts_link();
		$previous_posts_link = get_previous_posts_link();

		$args['next_posts_link'] = $next_posts_link;
		$args['previous_posts_link'] = $previous_posts_link;

		return $this->_render('busqueda', $args);
	}
	/**
	 * author.php
	 *
	 * @param unknown $args
	 */
	protected function _renderAutor($args = []) {
		return $this->_render('autor', $args);
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

	/*
	 * ALERTAS
	 */

	/**
	 * Crear una alerta
	 *
	 * @param string $tipo
	 *        	Tipo de alerta. Será el nombre de la clase que definirá el estilo de la alerta
	 * @param string $mensaje
	 * @param string $strong
	 * @param unknown $args
	 * @return View
	 */
	private function _renderAlerta($tipo, $mensaje, $strong = false, $href = false, $args = []) {
		$args['tipo'] = $tipo;
		$args['mensaje'] = $mensaje;
		$args['strong'] = $strong;
		$args['href'] = $href;
		return $this->render('ajax/alerta', $args);
	}

	/**
	 * Crear una alerta de tipo Success
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaSuccess($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('success', $mensaje, $strong, $href);
	}

	/**
	 * Crear una alerta de tipo Danger
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	public function renderAlertaDanger($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('danger', $mensaje, $strong, $href);
	}

	/**
	 * Crear una alerta de tipo Info
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaInfo($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('info', $mensaje, $strong, $href);
	}

	/**
	 * Crear una alerta de tipo Warning
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaWarning($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('warning', $mensaje, $strong, $href);
	}
}
