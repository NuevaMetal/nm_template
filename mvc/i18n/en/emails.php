<?php
return [
	'aviso_admin_nuevo_user' => '
		New user registered in :blogname <br>
		<b>Username</b>: :user_login <br>
		<b>Pass temp: </b>: :user_pass <br>
		<b>E-mail</b>: :user_email <br>',

	'aviso_admin_password_reset' => '¡Ey bro!,
		User  en :blogname <br>
		<b>Username: </b>: :user_login <br>
		<b>Pass temp: </b>: :user_pass <br>
		<b>E-mail</b>: :user_email <br>
		<b>Key URL: </b>: <a href=" :user_key_url " target="_blank"> :user_key_url </a> <br>',

	'nuevo_user' => 'Hi bro,
		<br>
		Welcome to NuevaMetal! Heres how to
			<a href="http://nuevametal.com/wp-login.php"> log in</a>:<br>
		<b>Username</b>: :user_login <br>
		<b>Password</b>: :user_pass <br>
		<br>
		If you have any problems, please contact me at :admin_email <br>
		See you!',

	'password_reset' => 'Hi man,
		<br>
			Here you have your new password. Don\'t forget that you can do login
			<a href="http://nuevametal.com/wp-login.php">here</a>:<br>
		<b>Nombre de usuario</b>: :user_login <br>
		<b>Contraseña</b>: :user_pass <br>
		<br>
		If you have any problems, please contact me at :admin_email <br>
		See you!'
];