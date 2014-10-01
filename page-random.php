<?php
// http://codex.wordpress.org/Class_Reference/WP_Query
$args = array(
	'post_status' => 'publish',
	'post_type' => 'post',
	'showposts' => 1,
	'orderby' => 'rand',
	'category__in' => [
		get_cat_ID('bandas')
	],
	'date_query' => [
		'after' => [
			'year' => '2012'
		]
	]
);

$posts = get_posts($args);
$post = Post::find($posts [0]->ID);
header("Location: {$post->getUrl()}");