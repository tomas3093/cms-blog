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
            SELECT message_id, sender, recipient, subject, message, date, unread, sender_del, recipient_del
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
        return Database::querryAll('SELECT message_id, sender, recipient, subject, message, date, unread, sender_del, recipient_del
                FROM messages
                WHERE recipient = ? AND recipient_del = ?
                ORDER BY message_id DESC
                ', array($recipient, 0));
    }

    //vrati vsetky odoslane spravy uzivatela
    public function returnSentMessages($sender)
    {
        return Database::querryAll('SELECT message_id, sender, recipient, subject, message, date, unread, sender_del, recipient_del
                FROM messages
                WHERE sender = ? AND sender_del = ?
                ORDER BY message_id DESC
                ', array($sender, 0));
    }

    //vymaze spravu z uzivatelovho control panela
    public function deleteMessage($message_id, $user)
    {
        $message = $this->returnMessage($message_id);

        //ak je uzivatel odosielatel
        if($message['sender'] == $user['name'])
        {
            if($message['recipient_del'] == 1)
            {
                Database::querry('DELETE FROM messages WHERE message_id = ?', array($message_id));
            }
            else
            {
                $values = array('sender_del' => 1);
                Database::update('messages', $values, 'WHERE message_id = ?', array($message_id));
            }
        }
        //ak je uzivatel prijimatel
        else
        {
            if($message['sender_del'] == 1)
            {
                Database::querry('DELETE FROM messages WHERE message_id = ?', array($message_id));
            }
            else
            {
                $values = array('recipient_del' => 1);
                Database::update('messages', $values, 'WHERE message_id = ?', array($message_id));
            }
        }
    }
}
