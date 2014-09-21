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
			case Ajax::ANALITICA :
				$tabla = $_datos ['tabla'];
				$cant = $_datos ['cant'];
				$json = AnaliticaController::getByTabla($tabla, $cant);
				break;
			case Ajax::HACER_COLABORADOR :
				$user_id = $_datos ['user'];
				$editor_id = $_datos ['editor'];
				$que = $_datos ['que'];
				if ($que == Ajax::HACER_COLABORADOR) {
					$json = $ajax->hacerColaborador($editor_id, $user_id);
				} else { // if ($que == Ajax::RECHAZAR_COLABORADOR) {
					$json = $ajax->rechazarColaborador($editor_id, $user_id);
				}
				break;
			default :
				Utils::debug("> AdminAjax> getJsonBySubmit()> Ocurrió un error inesperado");
				$json = $ajax->renderAlertaDanger('Ocurrió un error inesperado');
		}
		return $json;
	}

	public function hacerColaborador($editor_id, $user_id) {
		$userPendiente = UserPendiente::first('user_id', '=', $user_id);
		$userPendiente->aceptarPor($editor_id);
		$json ['content'] = 'OK';
		return $json;
	}

	public function rechazarColaborador($editor_id, $user_id) {
		$userPendiente = UserPendiente::first('user_id', '=', $user_id);
		$userPendiente->rechazarPor($editor_id);
		$json ['content'] = 'OK';
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
