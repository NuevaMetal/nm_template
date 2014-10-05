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
				$userPendiente = UserPendiente::first('user_id', '=', $user_id);
				if ($que == Ajax::HACER_COLABORADOR) {
					$userPendiente->aceptarPor($editor_id);
				} else if ($que == Ajax::RECHAZAR_COLABORADOR) {
					$userPendiente->rechazarPor($editor_id);
				} else if ($que == Ajax::HACER_PENDIENTE_COLABORADOR) {
					$userPendiente->pendienterPor($editor_id);
				} else if ($que == Ajax::BORRAR_COLABORADOR_PENDIENTE) {
					$userPendiente->delete();
				}
				$json ['content'] = 'OK';
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

$user = Utils::getCurrentUser();
if (!$user || !$user->canEditor()) {
	wp_die('No tienes permisos');
}

$json = AdminAjaxController::getJsonBySubmit($submit, $_REQUEST);

echo json_encode($json);
