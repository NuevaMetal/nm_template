<?php

namespace Models;

use Models\Post;
use Models\User;

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
}