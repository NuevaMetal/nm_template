<?php

namespace Models;

use I18n\I18n;
use Libs\Utils;
use Models\Post;
use Models\User;

/**
 * Vista de los géneros por Post
 *
 * @author chema
 */
class VGenerosPost extends ModelBase {
	public static $table = "v_generos_posts";

	/*
	 * Miembros
	 */
	public $term_id;
	public $term_name;
	public $term_slug;
	public $taxonomy_id;
	public $taxonomy_name;
	public $taxonomy_count;
	public $post_id;
	public $post_title;
	public $post_date;
	public $post_modified;
	public $post_author;
	public $post_content;

	/**
	 * Devuelve una lista de posts conociendo sus etiquetas
	 *
	 * @param array<Term> $tags
	 *        	Nombre de las tags a buscar
	 * @param integer $limit
	 *        	Límite
	 * @param integer $cat_id
	 *        	Identificador de la categoría. Por defecto false.
	 * @return array<Post> Devuelve
	 */
	public static function getPostsRandomByEtiquetas($tags = [], $limit = false, $cat_id = false, $notInList = []) {
		global $wpdb;
		if (empty($tags)) {
			return [];
		}
		// Primero sacamos la primera etiqueta
		$tag = array_shift($tags);
		$sql = 'SELECT distinct g.post_id
				from wp_v_generos_posts g ';
		if ($cat_id !== false) { // Si se especificó la categoría
			$sql .= ' JOIN ( SELECT distinct post_id
							FROM wp_v_generos_posts
							where taxonomy_name = "category"
							and term_id = ' . $cat_id . ') p
				ON (g.post_id = p.post_id) ';
		}

		$sql .= 'WHERE (g.taxonomy_name = "post_tag"
				AND ( g.term_id = ' . $tag->term_id . ' ';
		// Después recorremos el resto de etiquetas
		foreach ($tags as $tag) {
			$sql .= ' OR g.term_id = ' . $tag->term_id . ' ';
		}
		$sql .= ' )) ';
		if (is_array($notInList) && count($notInList)){
			foreach($notInList as $notIn){
				$sql .= ' AND g.post_id <> ' . $notIn.' ';
			}
		}
		$sql .= ' ORDER BY RAND() ';
		if ($limit !== false) {
			$sql .= ' LIMIT ' . $limit;
		}

		//debug($sql);
		$posts_id = $wpdb->get_col($sql);
		$posts = [];
		foreach ($posts_id as $post_id) {
			$posts[] = Post::find($post_id);
		}
		return $posts;
	}

	/**
	 * Crear vista en la bbdd
	 */
	private static function _crearVista() {
		$sql = 'CREATE OR REPLACE VIEW wp_v_generos_posts AS
		SELECT te.term_id, te.name AS term_name, te.slug AS term_slug,
				ta.term_taxonomy_id as taxonomy_id, taxonomy as taxonomy_name, ta.count AS taxonomy_count,
				object_id AS post_id, post_title, post_date, post_modified, post_author, post_content
		FROM wp_terms te
		JOIN wp_term_taxonomy ta ON ( te.term_id = ta.term_id )
		JOIN wp_term_relationships rel ON ( ta.term_taxonomy_id = rel.term_taxonomy_id )
		JOIN wp_posts p ON ( rel.object_id = p.ID )
		WHERE post_status = "publish"
		AND post_type = "post"
		ORDER BY post_id';
	}
}