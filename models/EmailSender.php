<?php

class EmailSender 
{
    //vytvori hlavicku a odosle email ako html dokument
    public function send($recipient, $subject, $message, $sender_fullname = "", $sender_email)
    {
        $header = "From: $sender_fullname" . "<" . strip_tags($sender_email) . ">";
        $header .= "\nMIME-Version: 1.0\n";                             //Multipurpose Internet Mail Extensions
        $header .= "Content-Type: text/html; charset=\"utf-8\"\n";      //Vytvorenie hlavicky emailu
        
        if(!mb_send_mail($recipient, $subject, $message, $header))
            throw new UserError('Niekde nastala neočakávaná chyba.');
    }
}