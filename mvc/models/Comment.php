<?php

namespace Models;

use Models\Post;
use Models\User;

/**
 *
 * @author chema
 */
class Comment extends ModelBase {
	static $table = "comments";
	static $PK = 'comment_ID';

	const MAX_LENGTH = 1000;

	const BORRAR_COMENTARIO = 'borrar-comentario';

	/**
	 * Borrar comentario
	 */
	public function borrar($force_delete = false) {
		wp_delete_comment($this->ID, $force_delete);
	}

	/**
	 *
	 * @return Post
	 */
	public function getPost() {
		return Post::find($this->comment_post_ID);
	}

	/**
	 *
	 * @return User
	 */
	public function getUser() {
		return User::find($this->user_id);
	}

	/**
	 * Guardar o modificar un comentario
	 */
	public function save() {
		global $wpdb;
		$existe = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
				FROM wp_comments
				WHERE comment_ID = %d', $this->comment_ID));
		$c = 'comment_';
		if ($existe) {
			// Actualizamos
			return $wpdb->query($wpdb->prepare("UPDATE wp_comments
					SET {$c}post_ID = %d, {$c}author = %s,
						{$c}author_email = %s, {$c}author_url = %s,
						{$c}author_IP = %s, {$c}date = %s,
						{$c}date_gmt = %s, {$c}content = %s,
						{$c}karma = %s, {$c}approved = %s,
						{$c}agent = %s, {$c}type = %s,
						{$c}parent = %s, user_id = %s
					WHERE comment_ID = %d", $this->comment_post_ID, $this->comment_author, $this->comment_author_email, $this->comment_author_url, $this->comment_author_IP, $this->comment_date, $this->comment_date_gmt, $this->comment_content, $this->comment_karma, $this->comment_approved, $this->comment_agent, $this->comment_type, $this->comment_parent, $this->user_id, $this->comment_ID));
		}
		// Creamos
		// return $wpdb->query($wpdb->prepare('INSERT INTO wp_comments'));
		throw new \Exception("TODO: impolementar Comment->save() para nuevo mensaje");
	}
}