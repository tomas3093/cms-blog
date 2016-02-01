<?php


class StatistikyController extends Controller
{
    public function process($parameters)
    {
        $statistics = new Statistics();
        $this->head['title'] = 'Štatistiky webu';

        $this->data['usersByArticles'] = $statistics->returnUsersByArticles();
        $this->data['articlesByVisits'] = $statistics->returnArticlesByVisits();

        $this->view = 'statistics';
    }
}