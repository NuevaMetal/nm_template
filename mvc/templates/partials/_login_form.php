<form name="loginform" id="loginform" action="/wp-login.php"
	method="post" role="form" >
	<div class="row">
		<div class="col-xs-4 ">
			<input type="text" name="log" id="user_login"
				class="form-control" value="" size="20" placeholder="User">
		</div>
		<div class="col-xs-4 ">
		 <input type="password" name="pwd" id="user_pass"
		 	class="form-control" value="" size="20"  placeholder="Password">
		</div>
		<div class="login-submit col-xs-4 ">
			<input type="submit" name="wp-submit" id="wp-submit"
				class="btn btn-default form-control" value="Acceder" >
			<input type="hidden" name="redirect_to" value="<?php echo get_home_url()?>" >
		</div>
	</div>
	<div class="ayuda row">
		  <div class="checkbox col-xs-3 col-sm-12">
		    <label><input name="rememberme" type="checkbox" id="rememberme"
					value="forever"/> Recuérdame</label>
		  </div>
		<div class="col-xs-12">
			<a href="/wp-login.php?action=lostpassword"><?php echo __('Contraseña perdida')?></a> | <a
				href="/wp-login.php?action=register"><?php echo __('Registrarse')?></a>
		</div>
	</div>
</form>