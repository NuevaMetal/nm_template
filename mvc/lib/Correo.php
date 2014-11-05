<?php
/**
 *
 * @author chema
 */
class Correo {

	/**
	 * Devuelve una nueva instancia del PHPMailer
	 *
	 * @param string|array $email
	 * @return PHPMailer
	 */
	private function getNuevoMailer($email = '') {
		$mail = new PHPMailer();
		$mail->isSMTP(); // Set mailer to use SMTP
		$mail->Host = 'smtp.mandrillapp.com'; // Specify main and backup SMTP servers
		$mail->SMTPAuth = true; // Enable SMTP authentication
		$mail->Username = 'chemaclass@outlook.es'; // SMTP username
		$mail->Password = '33ct-NhoNPVIa3Ay92_E2g'; // SMTP password
		$mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587; // TCP port to connect to
		$mail->CharSet = 'utf-8';

		$mail->From = 'nuevametal@outlook.com';
		$mail->FromName = 'NuevaMetal';

		if (is_string($email)) {
			$mail->addAddress($email);
		} else if (is_array($email)) {
			foreach ($email as $e) {
				$mail->addAddress($e);
			}
		}
		return $mail;
	}

	/**
	 * Enviar un correo genérico
	 *
	 * @param array|string $email
	 *        	Destino
	 * @param string $subject
	 *        	Asunto
	 * @param string $bodyHtml
	 * @param string $bodyHtmlAlt
	 * @return boolean
	 */
	public static function enviarCorreoGenerico($email, $subject, $bodyHtml, $bodyHtmlAlt = '') {
		$mail = self::getNuevoMailer($email);

		$mail->WordWrap = 50; // Set word wrap to 50 characters
		$mail->isHTML(true); // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body = $bodyHtml;
		$mail->AltBody = $bodyHtmlAlt;
		return $mail->send();
	}
}