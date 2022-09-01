<?php


namespace Hcode;


use Rain\Tpl;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Mailer {

	
	private $mail;

	
	public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
	{

		$config = array(
					"tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/email/",
					"cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",
					"debug"         => false
				   );

		Tpl::configure( $config );

		$tpl = new Tpl;


		foreach ($data as $key => $value) {
			// configurar as variáveis dentro do template
			$tpl->assign($key, $value);
		}


		// nome do template ($tplName) e true para jogar na variável $html e não na tela diretamente
		$html = $tpl->draw($tplName, true);


		// Início do código do PHP Mailer v6.6^

		 //Create an instance; passing `true` enables exceptions
	    $this->mail = new PHPMailer(true);

	    try {
	        //Server settings
	        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
	        $this->mail->isSMTP();                                            //Send using SMTP
	        $this->mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
	        $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
	        $this->mail->Username   = 'felipemarini71188@gmail.com';                     //SMTP username
	        $this->mail->Password   = 'aycwxvlteesvmdlr';                               //SMTP password
	        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
	        $this->mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

	        //Recipients
	        $this->mail->setFrom('felipemarini71188@gmail.com', 'Mailer');
	        $this->mail->addAddress($toAddress, $toName);
	        // $this->mail->addReplyTo('felipemarini71188@gmail.com', 'Information');
	        

	        //Attachments
	        // $this->mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
	        // $this->mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

	        //Content
	        $this->mail->isHTML(true);                                  //Set email format to HTML
	        $this->mail->Subject = $subject;
	        
	        // $body = "Mensagem enviada, segue informações:<br>
	        //         Nome: "Felipe"<br>
	        //         Email: "felipe@email.com"<br>
	        //         Mensagem: <br>Funcionou!";

	        $this->mail->Body    = $html;
	        $this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	        $this->mail->send();
	        echo 'Message has been sent!';
	    } 
	    
	    
	    catch (Exception $e) 
	    {
	        echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
	    }

			

	}

}

?>