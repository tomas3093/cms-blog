<?php


class StatistikyController extends Controller
{
    public function process($parameters)
    {
        $statistics = new Statistics();
        $validation = new Validation();
        $this->head['title'] = 'Štatistiky webu';

        $this->data['usersByArticles'] = $statistics->returnUsersByArticles();

        $this->data['articlesByVisits'] = array();
        $articles = $statistics->returnArticlesByVisits();
        //skratenie titulkov jednotlivych clankov na 35 znakov
        foreach($articles as $article)
        {
            $article['title'] = $validation->stringLimitLenght($article['title'], 35);
            $this->data['articlesByVisits'][] = $article;
        }
        $this->view = 'statistics';
    }
}