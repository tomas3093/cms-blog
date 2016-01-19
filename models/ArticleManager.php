<?php

//metody na spravu clankov v redakcnom systeme

class ArticleManager
{
    //vrati clanok z databazy podla jeho url
    public function returnArticle($url)
    {
        return Database::querryOne('
            SELECT article_id, thumbnail_img, title, content, url, description, key_words, author, date, public, visits
            FROM articles
            WHERE url = ?
        ', array($url));
    }
    
    //vrati vsetky publikovane clanky z databazy
    public function returnPublicArticles($offset)
    {
        if($offset == 0)
            return Database::querryAll('SELECT article_id, thumbnail_img, title, url, description, author, date, public
                FROM articles
                WHERE public = ?
                ORDER BY article_id DESC
            ', array(1));
        else
            return Database::querryAll('SELECT article_id, thumbnail_img, title, url, description, author, date, public
                FROM articles
                WHERE public = ?
                ORDER BY article_id DESC LIMIT 5 OFFSET ?
            ', array(1, $offset));
    }

    //vrati vsetky nepublikovane clanky z databazy
    public function returnUnpublishedArticles()
    {
        return Database::querryAll('SELECT article_id, thumbnail_img, title, url, description, author, date, public
                FROM articles
                WHERE public = ?
                ORDER BY article_id DESC
            ', array(0));
    }


    //metoda ulozi novy clanok do databazy, pokial uz toto ID existuje, zmeni existujuci clanok
    public function saveArticle($id, $article)
    {
        //pisanie noveho clanku
        if(!$id)
        {
            //cas ulozenia clanku
            $date = new DateTime();
            $time = $date->getTimestamp();
            $article['date'] = $time;
            Database::insert('articles', $article);     //zapisanie clanku do databazy
        }
        //editacia existujuceho clanku
        else
            Database::update('articles', $article, 'WHERE article_id = ?', array($id));

        //aktualizovanie poctu clankov uzivatela
        $userManager = new UserManager();
        $userManager->getUserData($article['author']);

    }

    //metoda odstrani clanok s danou URL adresou
    public function deleteArticle($url)
    {
        //vrati autora clanku
        $articleAuthor = Database::querryOne('
            SELECT author
            FROM articles
            WHERE url = ?
        ', array($url));

        //odstrani clanok
        Database::querry('DELETE FROM articles WHERE url = ?', array($url));

        //aktualizovanie poctu clankov uzivatela - autora odstraneneho clanku
        $userManager = new UserManager();
        $userManager->getUserData($articleAuthor['author']);
    }

    //nova navsteva clanku -> zvysi o 1 pocet navstev clanku
    public function newVisit($article_id, $visits)
    {
        $values['visits'] = $visits + 1;
        Database::update('articles', $values, 'WHERE article_id = ?', array($article_id));
    }

    //vrati top 5 clankov z databazy
    public function returnTopArticles()
    {
        return Database::querryAll('SELECT * FROM articles ORDER BY visits DESC LIMIT 5');
    }
}