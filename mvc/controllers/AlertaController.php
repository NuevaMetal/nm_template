<?php
// Cargamos WP.
// Si no se hace, en Ajax no se conocerá y no funcionará ninguna función de WP
require_once dirname(__FILE__) . '/../../../../../wp-load.php';
require_once 'BaseController.php';
require_once 'HomeController.php';

/**
 * Controlador de las alertas
 *
 * @author Chemaclass
 *
 */
abstract class AlertaController extends BaseController {

	/**
	 * Crear una alerta
	 *
	 * @param string $tipo
	 *        Tipo de alerta. Será el nombre de la clase que definirá el estilo de la alerta
	 * @param string $mensaje
	 * @param string $strong
	 * @param unknown $args
	 * @return View
	 */
	protected function renderAlerta($tipo, $mensaje, $strong = false, $args = []) {
		$args ['tipo'] = $tipo;
		$args ['mensaje'] = $mensaje;
		$args ['strong'] = $strong;
		return $this->render('ajax/alerta', $args);
	}

	/**
	 * Crear una alerta de tipo Success
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaSuccess($mensaje, $strong = false) {
		return $this->renderAlerta('success', $mensaje, $strong);
	}

	/**
	 * Crear una alerta de tipo Danger
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	public function renderAlertaDanger($mensaje, $strong = false) {
		return $this->renderAlerta('danger', $mensaje, $strong);
	}

	/**
	 * Crear una alerta de tipo Info
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaInfo($mensaje, $strong = false) {
		return $this->renderAlerta('info', $mensaje, $strong);
	}

	/**
	 * Crear una alerta de tipo Warning
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaWarning($mensaje, $strong = false) {
		return $this->renderAlerta('warning', $mensaje, $strong);
	}

}