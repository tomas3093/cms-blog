<?php

class ClankyController extends Controller
{
    public function process($parameters)
    {
        $articleManager = new ArticleManager();
        $userManager = new UserManager();
        $commentManager = new CommentManager();
        $validation = new Validation();

        $user = $userManager->returnUser();
        $this->data['admin'] = $user['admin'];

        //ak je zadane URL pre clanok, uloz clanok do premennej $article
        if(!empty($parameters[0]) && ($parameters[0] != 'page') && ($parameters[0] != 'unpublished'))
            $article = $articleManager->returnArticle($parameters[0]);

        //nie je zadane url clanku, tak vypise zoznam clankov
        if(empty($parameters[0]))
        {
            $articles = $articleManager->returnPublicArticles(0);
            $this->data['articles'] = $validation->statusOfArticles($articles);

            //zisti pocet clankov, a pripravi pocet stran
            $countArticles = sizeof($articles);
            $modulo = $countArticles % 5;
            if($modulo == 0)
                $this->data['pages'] = $countArticles / 5;
            else
                $this->data['pages'] = intval(($countArticles / 5) + 1);

            $this->data['currentPage'] = 1;         //aktualna strana

            //hlavicka stranky
            $this->head = array(
                'title' => 'Zverejnené články',
                'key_words' => 'coding.wz.sk - články',
                'description' => 'Zverejnené články'
            );
            $this->view = 'articles';
        }

        //ak je zadane URL pre zobrazenie nepublikovanych clankov
        if(!empty($parameters[0]) && $parameters[0] == 'unpublished')
        {
            $articles = $articleManager->returnUnpublishedArticles();
            $this->data['articles'] = $validation->statusOfArticles($articles);

            if(sizeof($articles) == 0)
                $this->createMessage('Žiadne články na zobrazenie', 'info');

            //hlavicka stranky
            $this->head = array(
                'title' => 'Nezverejnené články',
                'key_words' => 'coding.wz.sk',
                'description' => 'Nezverejnené články'
            );
            $this->view = 'articles';
        }

        //ak je zadane URL pre zobrazenie konkretnej strany
        if(!empty($parameters[0]) && $parameters[0] == 'page')
        {
            //ak je zadane cislo strany
            if(!empty($parameters[1]) && is_numeric($parameters[1]))
            {
                if($parameters[1] == 1)
                    $offset = 0;
                else
                    $offset = ($parameters[1] * 5) - 5;

                //zisti pocet clankov, a pripravi pocet stran
                $articles = $articleManager->returnPublicArticles(0);     //vsetky clanky
                $countArticles = sizeof($articles);
                $modulo = $countArticles % 5;
                if($modulo == 0)
                    $this->data['pages'] = $countArticles / 5;
                else
                    $this->data['pages'] = intval(($countArticles / 5) + 1);

                $this->data['currentPage'] = $parameters[1];        //aktualna strana

                //vratenie clankov s pozadovanym offsetom
                $articles = $articleManager->returnPublicArticles($offset);
                $this->data['articles'] = $validation->statusOfArticles($articles);

                //hlavicka stranky
                $this->head = array(
                    'title' => 'Zverejnené články - Strana ' . $parameters[1],
                    'key_words' => 'coding.wz.sk - články',
                    'description' => 'Zverejnené články'
                );
                $this->view = 'articles';
            }
            //ak nie je zadane cislo strany
            else
                $this->redirect('clanky');
        }

        //ak je zadane URL pre zmazanie clanku
        if((!empty($parameters[1]) && $parameters[1] == 'odstranit') && ($parameters[0] != 'page'))
        {
            //overi ci clanok z URL existuje
            if(!$article)
                $this->redirect('chyba');

            //overi ci je prihlaseny admin
            $this->checkUser(true);
            $articleManager->deleteArticle($parameters[0]);
            $this->createMessage('Článok bol odstránený', 'success');
            $this->redirect('clanky');
        }

        //ak je zadane URL pre publikovanie clanku
        if((!empty($parameters[1]) && $parameters[1] == 'publikovat') && ($parameters[0] != 'page'))
        {
            //overi ci clanok z URL existuje
            if(!$article)
                $this->redirect('chyba');

            //overi ci je prihlaseny admin
            $this->checkUser(true);
            $articleManager->publishArticle($article['url']);
            $this->createMessage('Článok bol publikovaný', 'success');
            $this->redirect('clanky');
        }

        //ak je zadane URL pre zmazanie komentara
        if(!empty($parameters[0]) && !empty($parameters[1]) && $parameters[1] == 'odstranit-komentar' && !empty($parameters[2]))
        {
            //overi ci clanok z URL existuje
            if(!$article)
                $this->redirect('chyba');

            $this->checkUser(true);     //overi ci je prihlaseny admin
            $commentManager->deleteComment($parameters[2]);
            $this->createMessage('Komentár bol odstránený', 'success');
        }

        //ak je zadane URL clanku
        if(!empty($parameters[0]) && $parameters[0] != 'page' && ($parameters[0] != 'unpublished'))
        {
            //ak nebol clanok na zadanej URL najdeny
            //alebo ak uzivatel nie je admin a clanok nie je publikovany
            //presmeruj na chybove hlasenie
            if(!$article || (($user['admin'] != '1') && ($article['public'] == '0')))
                $this->redirect('chyba');

            //ak bol odoslany komentar
            if($_POST)
            {
                //ak bol spravne vyplneny antispam
                if($_POST['year'] == date('Y'))
                {
                    //vyber udajov z $_POST a ich ulozenie do premennej $comment
                    $keys = array('article_id', 'comment', 'author');
                    $comment = array_intersect_key($_POST, array_flip($keys));
                    //ulozenie komentara do DB
                    $commentManager->saveComment($comment, $user['name']);
                    $this->createMessage('Váš komentár bol úspešne pridaný', 'success');
                    $this->redirect('clanky/' . $article['url']);
                }
                else
                {
                    $this->createMessage('Chybne vyplnený antispam', 'warning');
                    $this->redirect('clanky/' . $article['url']);
                }

            }

            //naplnenie premennych pre sablonu
            $this->data['article'] = $article;
            $this->data['category'] = $validation->returnCategoryName($article['category']);
            $this->data['user'] = $user['name'];
            //status clanku (publikovany/nepublikovany)
            $status = $validation->statusOfArticles(array($article));
            $this->data['article']['status'] = $status[0]['status'];
            //komentare k clanku
            $this->data['comments'] = $commentManager->returnCommentsById($article['article_id']);

            //priradenie avataru uzivatela do komentarov
            $i = 0;
            foreach($this->data['comments'] as $commentData)
            {
                $userData = $userManager->returnUserInfo($commentData['author']);
                $this->data['comments'][$i]['avatar'] = $userData['avatar'];
                $this->data['comments'][$i]['userRank'] = $validation->returnUserRank($userData['admin']);
                $i += 1;
            }

            //zaznamena navstevu clanku
            $articleManager->newVisit($article['article_id'], $article['visits']);

            //hlavicka stranky
            $this->head = array(
                'title' => $article['title'],
                'key_words' => $article['key_words'],
                'description' => $article['description']
            );
            $this->view = 'article';
        }
    }
}