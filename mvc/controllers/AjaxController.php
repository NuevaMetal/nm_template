<?php
// Cargamos WP.
// Si no se hace, en Ajax no se conocerá y no funcionará ninguna función de WP
require_once dirname(__FILE__) . '/../../../../../wp-load.php';

require_once 'BaseController.php';

/**
 * JSON de respuesta
 */
$json = array();

function mostrarNuevasNotificaciones() {
	echo "<br> > mostrarNuevasNotificaciones()<br>";
	global $wpdb, $current_user;
	$allowed_roles = array(
		'editor',
		'administrator'
	);
	if (array_intersect($allowed_roles, $current_user->roles)) {
		$num = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 FROM ' . $wpdb->prefix . 'notificaciones WHERE `active` = 1;');
		if (!$num)
			return;
		$admin_url = admin_url('admin.php?page=class-notificaciones');
		if ($num == 1) {
			//echo '<div id="mensaje" class="error">Hay '. $num .' nueva notificación</div>';
			$msg = "Hay $num nueva notificación pendiente";
		} else {
			$msg = "Hay $num nuevas notificaciones pendientes";
		}
		echo "<div id='message' class='error'><p><b>Ey bro!</b> $msg en <a href='$admin_url'>Notificaciones</a></p></div>";
		echo "<br>";
	} else {
		echo "no tienes permisos";
	}
}
class AjaxController extends BaseController {

	/**
	 * Crear una alerta
	 *
	 * @param string $tipo
	 *        Tipo de alerta. Será el nombre de la clase que definirá el estilo de la alerta
	 * @param string $mensaje
	 * @return unknown
	 */
	public function crearAlerta($tipo, $mensaje, $strong = false) {
		$post_id = $_POST ['post'];
		$user_id = $_POST ['user'];

		$post = get_post($post_id);
		$user = get_current_user();
		$strong = $post->post_title;
		$alerta = $this->render('ajax/alerta', [
			'tipo' => $tipo,
			'mensaje' => $mensaje,
			'strong' => $strong
		]);
		return $alerta;
	}

}
$ajax = new AjaxController();

$json ['alerta'] = $ajax->crearAlerta('success', 'Notificación enviada con éxito');

echo json_encode($json);

