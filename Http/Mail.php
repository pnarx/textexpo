<?php 

namespace Http;

use Http\Models\Model;
use PHPMailer\PHPMailer\PHPMailer;

class Mail {
    private $Host;
    private $DisplayName;
    private $SMTPAuth = true;
    private $Password;
    private $SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    private $Port;
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer;

        $sql = "select * from smtp_config where status = :status";
        $qry = (new Model())->db->prepare($sql);
        $qry->bindValue(':status', 'active');
        $qry->execute();
        $smtp = $qry->fetch(\PDO::FETCH_OBJ);
        
        if ($smtp) {
            $this->Host = $smtp->host;
            $this->DisplayName = $smtp->username;
            $this->Port = $smtp->port;
            $this->Password = $smtp->password;
        }
    }
    public function mail() {
        $this->mail->isSMTP();
        $this->mail->Host = $this->Host;
        $this->mail->SMTPAuth = $this->SMTPAuth;
        $this->mail->Username = $this->DisplayName;
        $this->mail->Password  = $this->Password;
        $this->mail->SMTPSecure = $this->SMTPSecure;
        $this->mail->Port  = $this->Port;
        $this->mail->CharSet = 'UTF-8';
        
        $this->mail->setFrom($this->DisplayName, $this->DisplayName);

        return $this;
    }

    public function addAddress(string $email, string $name) {
        $this->mail->addAddress($email, $name);

        return $this;
    }

    public function body(array $mail) {
        $this->mail->isHTML(true);
        $this->mail->Subject = $mail['subject'];
        $this->mail->Body = $mail['html'];
    
        return $this;
    }

    public function addAttachment(string $path) {
        $this->mail->addAttachment($path);

        return $this;
    }

    public function send() {
        try {
            $this->mail->send();
        } catch (\Throwable $th) {
            
        }
    }
}