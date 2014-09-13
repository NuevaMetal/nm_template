<?php
class Ajax {

	/**
	 * JSON para Morris
	 */
	public static function jsonParaMorris($result, $xKey, $yKeys, $labels) {
		$json = [
			'data' => $result,
			'xkey' => $xKey,
			'ykeys' => $yKeys,
			'labels' => $labels
		];
		return $json;
	}

}