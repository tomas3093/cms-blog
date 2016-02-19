<?php

//metody pre statistiky webu

class Statistics
{
    public function returnArticlesByVisits()
    {
        return Database::querryAll('
          SELECT article_id, thumbnail_img, title, url, date, visits
          FROM articles
          ORDER BY visits DESC
        ');
    }
}