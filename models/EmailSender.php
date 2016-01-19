<?php

class EmailSender 
{
    //vytvori hlavicku a odosle email ako html dokument
    public function send($recipient, $subject, $message, $from)
    {
        $header = "From: " . $from;
        $header .= "\nMIME-Version: 1.0\n";                             //Multipurpose Internet Mail Extensions
        $header .= "Content-Type: text/html; charset=\"utf-8\"\n";      //Vytvorenie hlavicky emailu
        
        if(!mb_send_mail($recipient, $subject, $message, $header))
            throw new UserError('Email sa nepodarilo odoslať.');
    }
    
    
    public function sendWithAntispam($year, $recipient, $subject, $message, $from)
    {
        if($year != date('Y'))
            throw new UserError('Chybne vyplnený antispam.');
        $this->send($recipient, $subject, $message, $from);
    }
}