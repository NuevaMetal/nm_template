<?php
require_once 'ModelBase.php';
/**
 * Analítica
 *
 * @author chema
 *
 */
class Analitica extends ModelBase {

	const TOTAL_USERS = 'total-users';

	const TOTAL_VISITAS = 'total-visitas';

	const TOTAL_VISITAS_USERS = 'total-visitas-users';

	const TOTAL_VISITAS_POST = 'total-visitas-posts';

	const TOTAL_VISITAS_HORA = 'total-visitas-hora';
	public static $table = "analiticas";
	public $user_id;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Devuelve el User asociado a una analítica
	 *
	 * @return User Usuario de la analítica,
	 *         o NULL en caso de no tener user_id asociado
	 */
	public function getUser() {
		if ($this->user_id == null) {
			return null;
		}
		global $wpdb;
		$query = "SELECT *
				FROM {$wpdb->prefix}" . static::$table . "
				WHERE user_id = $this->user_id";
		return $wpdb->get_row($query);
	}

	/**
	 * Devuelve todos los seguimientos de una analítica
	 *
	 * @return array<Seguimiento>
	 */
	public function getSeguimientos() {
		if ($this->ID == null) {
			return null;
		}
		global $wpdb;
		$query = "SELECT *
				FROM {$wpdb->prefix}" . Seguimiento::$table . "
				WHERE analitica_id = $this->ID";
		return $wpdb->get_results($query);
	}

	/**
	 *
	 * @param string $ID
	 * @return NULL
	 */
	public function save() {
		global $wpdb, $post;
		$user = wp_get_current_user();
		//Comprobamos si existe
		$query = "SELECT * FROM {$wpdb->prefix}" . static::$table . "
		WHERE user_id = $user->ID AND DATE(created_at) = CURRENT_DATE;";
		//Utils::debug($query);
		$analitica = $wpdb->get_row($query);

		if ($analitica) {
			//Si existe actualizamos
			$wpdb->query($wpdb->prepare("
					UPDATE {$wpdb->prefix}" . static::$table . "
					SET updated_at = now()
					WHERE ID = %d", $analitica->ID));
			$this->ID = $analitica->ID;
			//dd($analitica);
		} else {
			//Si no existe lo creamos
			$result = $wpdb->query($wpdb->prepare("
				INSERT {$wpdb->prefix}" . static::$table . " (user_id, created_at, updated_at)
				VALUES (%d, null, null);", $user->ID));
			$this->ID = $wpdb->insert_id;
		}
		$seguimiento = new Seguimiento();
		$seguimiento->analitica_id = $this->ID;
		$seguimiento->post_id = $post->ID;
		$seguimiento->save();
	}

}
