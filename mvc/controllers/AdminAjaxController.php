<?php
require_once 'AlertaController.php';

/**
 * Controlador del AJAX
 *
 * @author Chemaclass
 *
 */
class AdminAjaxController extends AlertaController {

	/**
	 *
	 * @param string $submit
	 */
	public static function getJsonBySubmit($submit, $_datos) {
		$ajax = new AdminAjaxController();
		switch ($submit) {
			case Utils::ANALITICA :
				$tabla = $_datos ['tabla'];
				$cant = $_datos ['cant'];
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
	'code' => 504 // Error default
];
$submit = $_REQUEST ['submit'];

$json = AdminAjaxController::getJsonBySubmit($submit, $_REQUEST);

echo json_encode($json);
