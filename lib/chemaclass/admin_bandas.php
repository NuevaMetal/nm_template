<?php
$nombreBanda = array(
	'name' => 'nombre',
	'blockTitle' => 'Nombre de la banda',
	'fields' => array(
		array(
			'name' => 'nombre',
			'labelTitle' => 'Nombre de la banda',
			'fieldType' => 'textField'
		)
	)
);

$genero = array(
	'name' => 'genero',
	'blockTitle' => 'Géneros',
	'fields' => array(
		array(
			'name' => 'genero',
			'labelTitle' => 'Género',
			'fieldType' => 'textField'
		)
	)
);

$pais = array(
	'name' => 'pais',
	'blockTitle' => 'País',
	'fields' => array(
		array(
			'name' => 'pais',
			'labelTitle' => 'País',
			'fieldType' => 'textField'
		),
		array(
			'name' => 'provincia',
			'labelTitle' => 'Provincia',
			'fieldType' => 'textField'
		)
	)
);

$redesSociales = array(
	'name' => 'redes_sociales',
	'blockTitle' => 'Redes Sociales',
	'fields' => array(
		array(
			'name' => 'twitter',
			'labelTitle' => 'Twitter',
			'fieldType' => 'textField'
		),
		array(
			'name' => 'facebook',
			'labelTitle' => 'Facebook',
			'fieldType' => 'textField'
		),
		array(
			'name' => 'otro',
			'labelTitle' => 'Otro',
			'fieldType' => 'textField'
		)
	)
);

$albumes = array(
	'name' => 'albumes',
	'blockTitle' => 'Álbumes',
	'fields' => array(
		array(
			'name' => 'albumes',
			'labelTitle' => 'Álbumes',
			'fieldType' => 'textarea'
		),
		array(
			'name' => 'portada',
			'labelTitle' => 'Imágen de su último trabajo',
			'fieldType' => 'imageUploader'
		)
	)
);

$bandasCustomPostType = array(
	'name' => 'banda',
	'displayName' => 'banda',
	'pluralDisplayName' => 'bandas',
	'enablePostThumbnailSupport' => true,
	'fieldBlocks' => array(
		$nombreBanda,
		$genero,
		$pais,
		$redesSociales,
		$albumes
	),
	/*'supports' => array(
		'editor' => false
	)*/
);

$adminSettings = array(
	'customPostTypes' => array(
		$bandasCustomPostType
	),
	'thumbnailsInStandardPosts' => true
);

$adminController = new ChesterAdminController($adminSettings);
