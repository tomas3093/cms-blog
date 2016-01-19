<?php

//metody pre spravu komentarov k clankom

class CommentManager
{
    //pridanie komentara do DB
    public function saveComment($comment, $user)
    {
        //cas odoslania komentaru
        $date = new DateTime();
        $time = $date->getTimestamp();
        $comment['date'] = $time;

        //pridanie komentara do DB
        Database::insert('comments', $comment);

        //aktualizovanie poctu komentarov uzivatela
        $count = Database::querryOne('
            SELECT COUNT(*)
            FROM comments
            WHERE author = ?
            ', array($user));
        $values['comments'] = $count[0];
        $userManager = new UserManager();
        $userManager->updateUserData($user, $values);
    }

    //vrati komentare podla ID clanku
    public function returnCommentsById($article_id)
    {
        return Database::querryAll('SELECT comment_id, author, comment, date
        FROM comments
        WHERE article_id = ?
        ORDER BY article_id DESC', array($article_id));
    }

    //odstrani komentar
    public function deleteComment($comment_id)
    {
        //vrati autora komentaru
        $commentAuthor = Database::querryOne('
            SELECT author
            FROM comments
            WHERE comment_id = ?
        ', array($comment_id));

        //odstrani komentar
        Database::querry('DELETE FROM comments WHERE comment_id = ?', array($comment_id));

        //aktualizovanie poctu komentarov uzivatela - autora odstraneneho komentara
        $userManager = new UserManager();
        $userManager->getUserData($commentAuthor['author']);
    }
}