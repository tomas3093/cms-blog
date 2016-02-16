<?php

//trieda pre spravu obsahu uvodnej stranky

class frontPageContentManager
{
    //pridanie kratkej spravy (short_news)
    public function addNewShortMessage($title, $message)
    {
        //aktualny cas
        $date = new DateTime();
        $time = $date->getTimestamp();

        $values = array(
            'title' => $title,
            'content' => $message,
            'date' => $time
        );

        //pridanie kratkej spravy do DB
        Database::insert('short_news', $values);
    }

    //vratenie kratkej spravy z DB (short_news)
    public function returnShortMessage($id)
    {
        return Database::querryOne('
            SELECT id, title, content, date
            FROM short_news
            WHERE id = ?
        ', array($id));
    }

    //vratenie vsetkych kratkych sprav z DB (short_news)
    public function returnShortMessages()
    {
        return Database::querryAll('
                SELECT id, title, content, date
                FROM short_news
                ORDER BY id DESC
            ');
    }

    //vymazanie kratkej spravy z DB (short_news)
    public function deleteShortMessage($id)
    {
        Database::querry('DELETE FROM short_news WHERE id = ?', array($id));
    }

    //vrati najnovsie clanky
    public function returnTopArticles()
    {
        return Database::querryAll('SELECT article_id, thumbnail_img, title, url, category, author, date, public
                FROM articles
                WHERE public = ?
                ORDER BY article_id DESC LIMIT 10 OFFSET 0
            ', array(1));
    }

    //vrati najnovsie clanky z danej kategorie
    public function returnTopArticlesByCategory($category)
    {
        return Database::querryAll('SELECT article_id, thumbnail_img, title, url, category, author, date, public
                FROM articles
                WHERE public = ? and category = ?
                ORDER BY article_id DESC LIMIT 3 OFFSET 0
            ', array(1, $category));
    }
}