<?php
//
// class.Mail.php - Mail system
//
// Vers. 1.0a, YP 04/21/2017
//
//
// History:
// 1.0 , YP 12/08/2016 - initial release
// 1.0a, YP 04/21/2017 - add subject as parameter. added SMTPAutoTLS
//


class MailIt {

  public $err = '';

  function __construct($cfg) {
	  $this->CFG = $cfg;
  }

  //
  // MailIt - email message 
  // Call:	MailIt($subject,$msg);
  // Where:	$subject - message subject
  //		$msg - message to email
  //
  public function MailIt($subject,$msg) {
  	$this->err = '';
    $mail = new PHPMailer;
    //$mail->SMTPDebug = 3;                         // Enable verbose debug output
    $mail->isSMTP();                                // Set mailer to use SMTP
    $mail->Host = $this->CFG['MAIL_HOST'];  		// Specify main and backup SMTP servers
    $mail->SMTPAutoTLS = false;
    //$mail->Host = gethostbyname($this->CFG['MAIL_HOST']);
    if ($this->CFG['MAIL_SMTP_AUTH']) {
      $mail->SMTPAuth = $this->CFG['MAIL_SMTP_AUTH'];    // Enable SMTP authentication
      $mail->Username = $this->CFG['MAIL_USER'];         // SMTP username
      $mail->Password = $this->CFG['MAIL_PASS'];         // SMTP password
    }

    if ($this->CFG['MAIL_SMTP_SECURE']) {
		print "here for secure: ".$this->CFG['MAIL_SMTP_SECURE']."\n";
      $mail->SMTPSecure = $this->CFG['MAIL_SMTP_SECURE'];// Enable TLS encryption, `ssl` also accepted
    } 
    $mail->Port = $this->CFG['MAIL_PORT'];              // TCP port to connect to
 
    $mail->setFrom($this->CFG['MAIL_SEND_FROM'], $this->CFG['MAIL_SEND_FROM_NAME']);
    foreach ($this->CFG['MAIL_SEND_TO'] as $addr) {
      $mail->addAddress($addr);
    }
    //$mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $msg;

    if(!$mail->send()) {
      $this->err = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    } 

  }	



}
