<?php
// Cargamos WP.
// Si no se hace, en Ajax no se conocerá y no funcionará ninguna función de WP
require_once dirname(__FILE__) . '/../../../../../wp-load.php';
require_once 'BaseController.php';
require_once 'AnaliticaController.php';

/**
 * Controlador del AJAX
 *
 * @author Chemaclass
 */
class AdminAjaxController extends BaseController {

	/**
	 *
	 * @param string $submit
	 */
	public static function getJsonBySubmit($submit, $_datos) {
		$ajax = new AdminAjaxController();
		switch ($submit) {
			case Ajax::ANALITICA :
				$tabla = $_datos['tabla'];
				$cant = $_datos['cant'];
				$json = AnaliticaController::getByTabla($tabla, $cant);
				break;

			default :
				Utils::debug("> AdminAjax> getJsonBySubmit()> Ocurrió un error inesperado");
				$json = $ajax->renderAlertaDanger('Ocurrió un error inesperado');
		}
		return $json;
	}
}

/**
 * -------------------------------------
 * Controlador para las peticiones AJAX
 * -------------------------------------
 */
$json = [
	'code' => 504
]; // Error default

$submit = $_REQUEST['submit'];

$user = Utils::getCurrentUser();
if (! $user || ! $user->canEditor()) {
	wp_die('No tienes permisos');
}

$json = AdminAjaxController::getJsonBySubmit($submit, $_REQUEST);

echo json_encode($json);
