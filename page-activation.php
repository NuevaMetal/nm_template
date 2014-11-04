<?php
/**
 * Establecer la nueva password al usuario
 */
$user_nicename = $_GET['user'];
$key = $_GET['key'];

global $wpdb;
$user_id = $wpdb->get_var($wpdb->prepare('select ID
		from wp_users
		where user_nicename = %s', $user_nicename));

$user = User::find($user_id);
if ($user && wp_check_password($key, $user->getHashActivationKey())) {
	$user_pass = $user->setNewPassword();
} else {
	header('Location: /wp-login.php?newpass=error');
	return;
}
if (! Utils::esCadenavalida($user_pass)) {
	header('Location: /wp-login.php?newpass=error');
	return;
}
$user->setActivationKey('', true); // Borramos la clave de activación

$plantillaEmailRecuperarPass = I18n::trans('emails.password_reset_2', [
	'blogname' => get_option('blogname'),
	'blogurl' => home_url(),
	'user_login' => $user->getLogin(),
	'user_pass' => $user_pass,
	'user_email' => $user->getEmail(),
	'admin_email' => get_option('admin_email')
]);
$enviado = Correo::enviarCorreoGenerico([
	get_option('admin_email'),
	$user->getEmail()
], sprintf(__('[%s] Your username and password'), get_option('blogname')), $plantillaEmailRecuperarPass);

if (! $enviado) {
	Utils::info("FALLO al enviar correo generico 'plantillaEmailRecuperarPass'");
}
header('Location: /wp-login.php?newpass=success');