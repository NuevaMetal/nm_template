<?php
$genero = array(
	'name' => 'genero',
	'blockTitle' => 'Género',
	'fields' => array(
		array(
			'name' => 'genero',
			'labelTitle' => 'País',
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

$bandasCustomPostType = array(
	'name' => 'banda',
	'displayName' => 'banda',
	'pluralDisplayName' => 'bandas',
	'enablePostThumbnailSupport' => true,
	'fieldBlocks' => array(
		$genero,
		$pais,
		$redesSociales
	)
);

$adminSettings = array(
	'customPostTypes' => array(
		$bandasCustomPostType
	)
);

$adminController = new ChesterAdminController($adminSettings);
