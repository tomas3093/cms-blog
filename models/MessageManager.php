<?php


class MessageManager
{
    //vytvori spravu
    public function sendMessage($sender, $recipient, $subject, $message)
    {
        $date = new DateTime();
        $time = $date->getTimestamp();

        Database::insert('messages', array(
                    'sender' => $sender,
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'message' => $message,
                    'date' => $time
        ));
    }

    //vrati spravu z databazy
    public function returnMessage($message_id)
    {
        return Database::querryOne('
            SELECT message_id, sender, recipient, subject, message, date, unread
            FROM messages
            WHERE message_id = ?
        ', array($message_id));
    }

    //oznaci spravu ako precitanu
    public function readMessage($message_id)
    {
        Database::update('messages', array('unread' => 0), 'WHERE message_id = ?', array($message_id));
    }

    //vrati vsetky prijate spravy uzivatela
    public function returnReceivedMessages($recipient)
    {
        return Database::querryAll('SELECT message_id, sender, recipient, subject, message, date, unread
                FROM messages
                WHERE recipient = ?
                ORDER BY message_id DESC
                ', array($recipient));
    }

    //vrati vsetky odoslane spravy uzivatela
    public function returnSentMessages($sender)
    {
        return Database::querryAll('SELECT message_id, sender, recipient, subject, message, date, unread
                FROM messages
                WHERE sender = ?
                ORDER BY message_id DESC
                ', array($sender));
    }

    //vymaze spravu
    public function deleteMessage($message_id)
    {
        Database::querry('DELETE FROM messages WHERE message_id = ?', array($message_id));
    }
}
