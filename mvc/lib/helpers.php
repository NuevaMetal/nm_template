<?php
if (!function_exists('dd')) {

	/**
	 * Dump the passed variables and end the script.
	 *
	 * @param
	 *        dynamic mixed
	 * @return void
	 */
	function dd() {
		die(call_user_func_array('var_dump', func_get_args()));
	}
}
