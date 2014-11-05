<?php
return [
	'aviso_admin_nuevo_user' => '
		Nuevo usuario registrado en :blogname <br>
		<b>Nombre de usuario:</b> :user_login <br>
		<b>Pass temp:</b> :user_pass <br>
		<b>E-mail:</b>: :user_email <br>',

	'nuevo_user' => '¡Ey bro!,
		<br>
		¡Bienvenido a NuevaMetal! Aquí tienes cómo hacer:
			<a href="http://nuevametal.com/wp-login.php">login</a>:<br>
		<b>Nombre de usuario</b>: :user_login <br>
		<b>Contraseña</b>: :user_pass <br>
		<br>
		Si tienes algún problema, por favor contáctame :admin_email <br>
		¡Nos vemos!',

	'password_reset' => '
		<h2>Solicitado un cambio de contraseña en <a href=" :blogurl " target="_blank"> :blogname </a></h2>
		<b>Nombre de usuario:</b> :user_login <br>
		<b>E-mail:</b> :user_email <br>
		<b>Solicitar una nueva contraseña</b>:<br>
		<a href=" :user_key_url " target="_blank"> :user_key_url </a> <br><br>
		<p>Se ha solicitado un cambio de contraseña. Entra en este enlace para que se te envíe un correo con una nueva contraseña
		generada aleatoriamente. Posteriormente recuerda cambiarla desde tu panel de control de usuario.</p>
		<small>Si no has solicitado ningún cambio puedes omitir este correo.</small>',

	'password_reset_2' => '
		<h2>Recuperando tu password en <a href=" :blogurl " target="_blank"> :blogname </a></h2>
		<b>Nombre de usuario:</b> :user_login <br>
		<b>E-mail:</b> :user_email <br>
		<b>Contraseña:</b> :user_pass <br><br>
		<p>Aquí tienes la nueva contraseña. Recuerda cambiarla desde tu panel de control de usuario.<br>
		Si tienes algún problema, por favor contáctame :admin_email <br>
		¡Nos vemos!</p>',

];