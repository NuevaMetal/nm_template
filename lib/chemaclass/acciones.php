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

	$message = sprintf(__('New user registration on %s:'), get_option('blogname')) . "<br><br>";
	$message .= sprintf(__('Username: %s'), $user_login) . "<br>";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "<br>";

	$emailAvisoAdminNuevoUser = I18n::trans('emails.aviso_admin_nuevo_user', [
		'user_login' => $user_login,
		'user_email' => $user_pass,
		'blogname' => get_option('blogname')
	]);

	$enviado = Correo::enviarCorreoGenerico([
		get_option('admin_email')
	], sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

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

	return "<p>Ok. Vuelve a la web (http://nuevametal.com) y haz login cuando recibas la contraseña en tu correo</p>";
}, 10, 1);