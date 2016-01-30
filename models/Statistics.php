<?php

//metody pre statistiky webu

class Statistics
{
    public function returnUsersByArticles()
    {
        return Database::querryAll('
          SELECT user_id, name, avatar, admin, registration_date, last_visit, comments, articles, sex, email
          FROM users
          ORDER BY articles DESC
        ');
    }

    public function returnArticlesByVisits()
    {
        return Database::querryAll('
          SELECT article_id, thumbnail_img, title, url, date, visits
          FROM articles
          ORDER BY visits DESC
        ');
    }
}