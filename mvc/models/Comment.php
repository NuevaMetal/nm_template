<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 */
class Comment extends ModelBase {
	public static $table = "comments";

	const BORRAR_COMENTARIO = 'borrar-comentario';

	/**
	 * Borrar comentario
	 */
	public function borrar($force_delete = false) {
		wp_delete_comment($this->ID, $force_delete);
	}
}