<?php 
require_once(dirname(__FILE__).'\phpmailer\class.phpmailer.php');
class mail{
    private $host = "";
    private $username = "";
    private $password = "";
    private $port = "";
    private $mail;
    /**
     * @param string $host servidor de correo
     * @param string $username nombre de usuario
     * @param string $password Clave de acceso al correo
     * @param integer $port puerto smtp
     * @return boolean true on envio correcto, false if address already used
     */
    function __construct($host, $username, $password, $port){
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->mail = new PHPMailer();
    }

    /**
     * @param mixed $to string en destinatario unico, array multiplest detinatarios.
     * @param string $subject asunto del correo
     * @param string $body texto del correo 
     * @param string $file archivo ajdunto, si procede, optativo
     * @return boolean true envio correcto, false en error.
     */
    function send_mail($to, $subject, $body, $file = ''){
        if(is_array($destinatario)){
            foreach ($destinatario as $value) {
                $this->mail->AddAddress($value);
            }
        }else{
            $this->mail->AddAddress($destinatario);
        }

		$this->mail -> charSet = "UTF-8";
		$this->mail->SMTPDebug = 1;

		$this->mail->IsHTML(true);
		$this->mail->SMTPAuth = true;	
		$this->mail->IsSMTP();
        $this->mail->SMTPSecure = 'ssl';
        
		$this->mail->Host = $this->host;
		$this->mail->Username = $this->username;
		$this->mail->Sender = $this->username;
		$this->mail->Password = $this->password;
		$this->mail->From = $this->username;
		$this->mail->FromName	= $this->username;
		$this->mail->Port = $this->port;
		$this->mail->WordWrap = 0;
		$this->mail->Subject = $asunto;
		$mime_message = $this->mail->CreateBody(); 
	
		if ($adjunto!='')$this->mail->AddAttachment($adjunto);
				
		$this->mail->MsgHTML($contenido);
		if($this->mail->Send()){
            return true;
        }else{
            echo "Error: " . $this->mail->ErrorInfo;
            print_r($this->mail);
            return false;
        }
	}
}
?>