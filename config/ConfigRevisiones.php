<?php
require_once 'mvc/models/Revision.php';

/**
 *
 * @author chema
 */
class ConfigRevisiones {

	/**
	 * Registramos las alertas.
	 * Mostrar alerta indicando el número total de revisiones pendientes que hay
	 */
	public static function addActionAdminNotices() {
		add_action('admin_notices', function () {
			global $wpdb, $current_user;
			$user = User::find($current_user->ID);
			if ($user->canEditor()) {
				$numTotalPorRevisar = Revision::getTotalPorRevisar();
				if ($numTotalPorRevisar) {
					if ($numTotalPorRevisar == 1) {
						$msg = 'Hay ' . $numTotalPorRevisar . '</span> nueva revisión pendiente';
					} else {
						$msg = 'Hay ' . $numTotalPorRevisar . '</span> nuevas revisiones pendientes';
					}
					echo '
				<div class="update-nag">
				  <strong>¡Ey ' . $user->display_name . '!</strong> ' . $msg . ' en <a href="/revisions">Revisiones</a>
				</div>';
				}
			}
		});
	}
}

ConfigRevisiones::addActionAdminNotices();