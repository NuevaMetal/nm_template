<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class Revision extends ModelBase {

	const ESTADO_PENDIENTE = 0;

	const ESTADO_CORREGIDO = 1;

	const ESTADO_BORRADO = 2;

	const USER_BANEADO = 1;

	const USER_DESBANEADO = 2;
	public static $table = "revisiones";
	public $user_id;
	public $post_id;

	/**
	 * Devuelve el número total de entradas por revisar
	 *
	 * @return integer Número total por revisar
	 */
	public static function getTotalPorRevisar() {
		global $wpdb;
		return ( int ) $wpdb->get_var('
			SELECT count(*) as total
			FROM (SELECT post_id FROM `' . $wpdb->prefix . 'revisiones`
					WHERE `status` = 0
					GROUP BY post_id) r
				');
	}

	/**
	 * Corregir una revisión.
	 * Marca su estado a corregido.
	 *
	 * @param integer $post_id
	 * @param integer $user_id
	 * @return string
	 */
	public static function corregir($post_id) {
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}revisiones
				SET status = 1
				WHERE post_id = %d
				AND status = 0;", $post_id));
		$post_title = '<a href="' . get_permalink($post_id) . '" target="_blank">
							' . get_the_title($post_id) . '</a>';
		return "Corregido el post con título: <strong>$post_title</strong>. ";
	}

	/**
	 * Borrar una revisión.
	 * Cambia su estado a borrado
	 *
	 * @param integer $post_id
	 * @param integer $user_id
	 * @return string
	 */
	public static function pendiente($post_id) {
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}revisiones
				SET status = 0
				WHERE post_id = %d
				AND status = 1;", $post_id));
		$post_title = '<a href="' . get_permalink($post_id) . '" target="_blank">
							' . get_the_title($post_id) . '</a>';
		return "Puesta en revisión el post con título: <strong>'$post_title'</strong>. ";
	}

	/**
	 * Borrar una revisión.
	 * Cambia su estado a borrado
	 *
	 * @param integer $post_id
	 * @param integer $user_id
	 * @return string
	 */
	public static function borrar($post_id) {
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}revisiones
				SET status = 2
				WHERE post_id = %d
				AND status = 1;", $post_id));
		$post_title = '<a href="' . get_permalink($post_id) . '" target="_blank">
							' . get_the_title($post_id) . '</a>';
		return "Borrada la revisión del post con título: <strong>'$post_title'</strong>. ";
	}

	/**
	 * Banear a un user en las revisiones
	 *
	 * @param integer $editor_id
	 *        El User que banea
	 * @param integer $user_id
	 *        El User baneado
	 * @return string
	 */
	public static function banear($editor_id, $user_id) {
		Utils::debug("> banear( $editor_id, $user_id)");
		global $wpdb;
		$user = User::find($user_id);
		// Primero comprobamos que no sea un admin
		if ($user->isAdmin()) {
			return "El usuario <strong>'{$user->user_login}'</strong> es un administrador.";
		}
		//Segundo comprobamos que si ya está baneado
		$isBan = ( int ) $wpdb->get_var('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . "revisiones_ban
					WHERE user_id = $user_id AND status = 1;");
		if ($isBan) {
			return "Usuario <strong>'{$user->user_login}'</strong> ya baneado.";
		}

		//De lo contrario crearemos su registro en la tabla de users baneados
		$result = $wpdb->query($wpdb->prepare("
	INSERT {$wpdb->prefix}revisiones_ban (editor_id, user_id, created_at, updated_at)
	VALUES (%d, %d, null, null);", $editor_id, $user_id));

		return "User baneado de las revisiones con éxito: <strong>'{$user->user_login}'</strong>.";
	}

	/**
	 * Quitar el baneo a un user de las revisiones
	 *
	 * @param integer $editor_id
	 *        El User que quita el baneo
	 * @param integer $user_id
	 *        El User a quitar el baneo
	 * @return string
	 */
	public static function desbanear($editor_id, $user_id) {
		global $wpdb;
		$user = get_userdata($user_id);

		// Comprobamos que si ya está baneado
		$isBan = ( int ) $wpdb->get_var('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . "revisiones_ban
					WHERE user_id = $user_id AND status = 1;");

		if (!$isBan) {
			return "Usuario <strong>'{$user->user_login}'</strong> no baneado.";
		}

		//De lo contrario crearemos su registro en la tabla de users baneados
		$result = $wpdb->query($wpdb->prepare("
			UPDATE {$wpdb->prefix}revisiones_ban
			SET status = 2 WHERE user_id = %d AND status = 1;", $user_id));

		return "Quitado el baneo del Usuario <strong>'{$user->user_login}'</strong> con éxito.";
	}

	/**
	 * Devuelve la lista total de Users baneados
	 *
	 * @return array
	 */
	public static function allBan() {
		global $wpdb;
		$status = self::USER_BANEADO;
		$query = "SELECT *
				FROM {$wpdb->prefix}revisiones_ban
				WHERE status = $status
				GROUP BY user_id, status";
		$result = $wpdb->get_results($query);
		return $result;
	}

}