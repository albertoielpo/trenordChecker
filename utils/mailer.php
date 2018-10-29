<?php
class Mailer {

    var $to;
    var $subject;
    var $message;
    var $headers;

    function __construct($to, $subject, $message, $headers){
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->headers = $headers;
    }

    function send_mail(){
        mail($this->to, $this->subject, $this->message, $this->headers);
    }


}