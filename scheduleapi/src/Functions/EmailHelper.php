<?php

namespace App\Functions;

class EmailHelper
{
    public $mail;
    /**
     * string FromName
     * string FromEmail
     */
    public function __construct($FromName, $FromEmail)
    {
        $this->mail = new \WHMCS\Mail($FromName, $FromEmail);
    }
    public function setSubject($subject = '')
    {
        $this->mail->Subject = $subject;

    }
    public function setMessage($msg = '')
    {
        $message_text = str_replace("</p>", "\n\n", $msg);
          $message_text = str_replace("<br>", "\n", $message_text);
          $message_text = str_replace("<br />", "\n", $message_text);
          $message_text = strip_tags($message_text);
          $this->mail->Body = $message_text;
          $this->mail->AltBody = $message_text;
    }

    public function addAttachments($attachments = [])
    {
        foreach($attachments as $a)
        {
            $this->mail->AddAttachment($a['path'], $a['name']);
        }
    }
    public function send()
    {
        return $this->mail->Send();
    }
    public function setFromEmail($email = '')
    {
        $this->mail->From = $email;
    }
    public function setFromName($name = '')
    {
        $this->mail->FronName = $name;
    }
    public function AddAddress($address='')
    {
        $this->mail->AddAddress($address);
    }
}
