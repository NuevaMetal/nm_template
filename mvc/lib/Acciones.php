<?php
/**
 * Justo después de registrar un nuevo User vamos a generarle una clave aleatoria
 * y vamos a enviarsela por correo para que pueda acceder
 */
add_action('user_register', function ($user_id) {
	$user = get_user_by('id', $user_id);

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);
	$user_pass = wp_generate_password(12, false);
	wp_set_password($user_pass, $user_id);

	$emailAvisoAdminNuevoUser = I18n::trans('emails.aviso_admin_nuevo_user', [
		'user_login' => $user_login,
		'user_email' => $user_pass,
		'blogname' => get_option('blogname')
	]);

	$enviado = Correo::enviarCorreoGenerico([
		get_option('admin_email')
	], sprintf(__('[%s] New User Registration'), get_option('blogname')), $emailAvisoAdminNuevoUser);

	if (!$enviado) {
		Utils::info("Fallo al enviar el correo al User con ID: $user_id");
	}

	if (empty($user_pass))
		return;

	$emailNuevoUser = I18n::trans('emails.nuevo_user', [
		'user_login' => $user_login,
		'user_pass' => $user_pass,
		'admin_email' => get_option('admin_email')
	]);

	$enviado = Correo::enviarCorreoGenerico([
		$user_email
	], sprintf(__('[%s] Your username and password'), get_option('blogname')), $emailNuevoUser);
	if (!$enviado) {
		Utils::info("Fallo al enviar el correo al User con ID: $user_id");
	}
	header('Location: /wp-login.php');
});
/**
 * Enviar una nueva contraseña al email.
 */
add_action('login_init', function () {
	if ($_REQUEST ['action'] == 'lostpassword' && $_REQUEST ['wp-submit'] == 'Obtener una contraseña nueva') {
		$user_email = $_POST ['user_login'];
		$user = get_user_by('email', $user_email);

		$user_login = stripslashes($user->user_login);
		$user_pass = wp_generate_password(12, false);
		wp_set_password($user_pass, $user->ID);

		if (empty($user_pass) || !$user->ID)
			return;

		$emailNuevoUser = I18n::trans('emails.password_reset', [
			'user_login' => $user_login,
			'user_pass' => $user_pass,
			'admin_email' => get_option('admin_email')
		]);

		$enviado = Correo::enviarCorreoGenerico([
			$user_email
		], sprintf(__('[%s] Your username and password'), get_option('blogname')), $emailNuevoUser);
		if (!$enviado) {
			Utils::info("Fallo al enviar el correo al User con ID: $user_id");
		}
		header('Location: /wp-login.php');
	}
});

/**
 * Añado las redes sociales al perfil del User
 */
add_action('show_user_profile', function () {
	global $user_ID;
	require_once 'mvc/controllers/AutorController.php';
	$c = new AutorController();
	echo $c->getPerfilRedesSociales($user_ID);
});

/**
 * Actualizo las redes sociales del perfil del User
 */
add_action('personal_options_update', function () {
	global $user_ID;
	$user = User::find($user_ID);
	$user->setFacebook($_POST [User::KEY_USER_FACEBOOK]);
	$user->setTwitter($_POST [User::KEY_USER_TWITTER]);
	$user->setGooglePlus($_POST [User::KEY_USER_GOOGLE_PLUS]);
});

