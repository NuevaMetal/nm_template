<?php

namespace Models;

/**
 * Que puede tener imágenes
 *
 * @author José María Valera Reales <@Chemaclass>
 */
abstract class Image extends ModelBase {

	/**
	 * Establecer la img del header.
	 * Si es false se borrará la actual
	 *
	 * @param string $keyImg
	 *        	Clave de la imagen a setear
	 * @param file $imgFile
	 *        	Imagen en cuestión
	 * @throws Exception
	 * @return void|string
	 */
	protected function _setImg($keyImg, $imgFile) {
		// Si es false se la quita y además es null la borrará del servidor
		if (! $imgFile || is_null($imgFile)) {
			return $this->_quitarImg($keyImg);
		}
		if (strpos($imgFile['name'], '.php') !== false) {
			throw new Exception('For security reasons, the extension ".php" cannot be in your file name.');
		}
		$avatar = wp_handle_upload($_FILES[$keyImg], array(
			'mimes' => array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png'
			),
			'test_form' => false,
			'unique_filename_callback' => function ($dir, $name, $ext) use($keyImg) {
				$name = $base_name = sanitize_file_name($this->user_login . '_' . $keyImg);
				$number = 1;
				while (file_exists($dir . "/$name$ext")) {
					$name = $base_name . '_' . $number;
					$number ++;
				}
				return $name . $ext;
			}
		));
		// Quitamos su anterior keyImg
		$this->_quitarImg($keyImg);

		$meta_value = array();

		$url_or_media_id = $avatar['url'];
		// Establecemos el nuevo meta
		if (is_int($url_or_media_id)) {
			$meta_value['media_id'] = $url_or_media_id;
			$url_or_media_id = wp_get_attachment_url($url_or_media_id);
		}
		$meta_value['full'] = $url_or_media_id;
		return update_user_meta($this->ID, $keyImg, $meta_value);
	}

	/**
	 * Devuelve la img del avatar o header
	 *
	 * @param string $keyImg
	 * @param int $sizeW
	 * @param int $sizeH
	 * @return string
	 */
	protected function _getImg($keyImg, $sizeW, $sizeH = false) {
		$sizeH = ($sizeH) ? $sizeH : $sizeW;
		// fetch local avatar from meta and make sure it's properly ste
		$local_avatars = get_user_meta($this->ID, $keyImg, true);
		if (empty($local_avatars['full'])) {
			return '';
		}
		// generate a new size
		if (! array_key_exists($sizeW, $local_avatars)) {
			$local_avatars[$sizeW] = $local_avatars['full']; // just in case of failure elsewhere
			$upload_path = wp_upload_dir();
			// get path for image by converting URL, unless its already been set, thanks to using media library approach
			if (! isset($avatar_full_path)) {
				$avatar_full_path = str_replace($upload_path['baseurl'], $upload_path['basedir'], $local_avatars['full']);
			}
			// generate the new size
			$editor = wp_get_image_editor($avatar_full_path);
			if (! is_wp_error($editor)) {
				$resized = $editor->resize($sizeW, $sizeH, true);
				if (! is_wp_error($resized)) {
					$dest_file = $editor->generate_filename();
					$saved = $editor->save($dest_file);
					if (! is_wp_error($saved)) {
						$local_avatars[$sizeW] = str_replace($upload_path['basedir'], $upload_path['baseurl'], $dest_file);
					}
				}
			}
			// save updated avatar sizes
			update_user_meta($user_id, $keyImg, $local_avatars);
		}
		if ('http' != substr($local_avatars[$sizeW], 0, 4)) {
			$local_avatars[$sizeW] = home_url($local_avatars[$sizeW]);
		}
		return esc_url($local_avatars[$sizeW]);
	}

	/**
	 * Quitar la ImgHeader y la elimina del server
	 *
	 * @param unknown $keyImg
	 */
	private function _quitarImg($keyImg) {
		// Para eliminar el fichero lo guardamos en una var temporal
		$_getImgPath = $this->_getImgPath($keyImg);
		$sizes = $this->_getTamanosABorrar();
		foreach ($sizes as $size) {
			$imgPath = $_getImgPath['virgen'] . "-{$size}x{$size}" . $_getImgPath['ext'];
			if (file_exists($imgPath)) {
				unlink($imgPath);
			}
		}

		if (file_exists($_getImgPath['base'])) {
			unlink($_getImgPath['base']);
		}

		if (file_exists($_getImgPath['actual'])) {
			unlink($_getImgPath['actual']);
		}

		// Y lo quitamos de su meta
		return delete_user_meta($this->ID, $keyImg);
	}

	/**
	 * Devuelvo el nombre de la img base del header y el nombre de la img actual del header.
	 * Ejemplo [
	 * 'actual' => 'Chemaclass_header-353x200.png',
	 * 'base' => 'Chemaclass_header.png',
	 * 'virgen' => 'Chemaclass_img_header',
	 * 'ext' => '.png',
	 * ];
	 *
	 * @return array<string> Lista con el nombre 'base' y 'actual'.
	 */
	private function _getImgPath($keyImg) {
		$upload_path = wp_upload_dir();
		$img = $this->_getImg($keyImg, User::AVATAR_SIZE_ICO);
		$path = str_replace($upload_path['baseurl'], $upload_path['basedir'], $img);
		$actual = $base = basename($path);
		if (strpos($base, '-') !== false) {
			preg_match('/\.[^\.]+$/i', $actual, $ext);
			$_base = substr($base, 0, strpos($base, "-")) . $ext[0];
			$pathBase = str_replace($actual, $_base, $path);
		}
		// y la ruta virgen
		$_base_virgen = substr($base, 0, strpos($base, "-"));
		$virgen = str_replace($actual, $_base_virgen, $path);
		return [
			'actual' => $path,
			'base' => $pathBase,
			'virgen' => $virgen,
			'ext' => $ext[0]
		];
	}
}