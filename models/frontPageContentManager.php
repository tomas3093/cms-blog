<?php

//trieda pre spravu obsahu uvodnej stranky

class frontPageContentManager
{

    //vrati najnovsie clanky
    public function returnLastArticles()
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