<?php
/**
 * PHPMailer class
**/
function mfmailer($receiver_mail, $receiver_name, $subject, $message, $attachment) {
	
	## include mailer class
	include_once("../system/vendor/phpmailer/class.phpmailer.php");
	
	## generate mail
	$mail = new phpmailer();
	$mail->IsSMTP();
	$mail->Host			= MAILHOST;
	$mail->Port			= MAILPORT;
	$mail->SMTPSecure	= MAILSEC;
	$mail->SMTPAuth		= true;
	$mail->Username		= MAILUSER;
	$mail->Password		= MAILPASS;
	$mail->From			= MAILMAIL;
	$mail->FromName		= MAILNAME;
	$mail->AddAddress($receiver_mail, utf8_decode($receiver_name));
	$mail->SMTPKeepAlive = true;
	$mail->WordWrap = 500;
	if($attachment !== "") {
		$mail->addAttachment($attachment);
	}
	$mail->IsHTML(true);
	$mail->Subject		= utf8_decode($subject);
	$mail->Body			= utf8_decode($message);
	
	## send mail
	if(!$mail->Send()) {
		
		## return error
		return $mail->ErrorInfo;
		
	} else {
		
		## reset addresses
		$mail->clearAddresses();
		
		## return code 200
		return 200;
		
	}
	
}
?>