<?php

class EditorController extends Controller
{
    public function process($parameters)
    {
        //editor je pristupny iba pre admina
        $this->checkUser(true);

        $this->head['title'] = 'Editor článkov';

        //vytvorenie instancie spravcu clankov
        $articleManager = new ArticleManager();
        $userManager = new UserManager();
        $validation = new Validation();

        $article = array(
            'article_id' => '',
            'thumbnail_img' => '',
            'title' => '',
            'content' => '',
            'url' => '',
            'description' => '',
            'key_words' => '',
            'author' => ''
        );

        //ak je odoslany formular
        if($_POST)
        {
            //ziskanie clanku z $_POST
            $keys = array('title', 'thumbnail_img', 'content', 'url', 'description', 'key_words', 'author', 'public');
            $article = array_intersect_key($_POST, array_flip($keys));

            //ak bol oznaceny checkbox public, tak nastav clanok ako publikovany
            if(isset($_POST['public']))
                $article['public'] = '1';
            else
                $article['public'] = '0';

            //ak nebol nastaveny nahladovy obrazok, nastav defaultny
            if(empty($_POST['thumbnail_img']))
                $article['thumbnail_img'] = 'img/articles/no_thumb.jpg';

            try
            {
                //vytvorenie URL adresy z pola title
                $article['url'] = $validation->checkUrl($article['title']);

                //ulozenie clanku do databazy
                $articleManager->saveArticle($_POST['article_id'], $article);
                $this->createMessage('Článok bol úspešne uložený', 'success');
                $this->redirect('clanky/' . $article['url']);
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
                $this->redirect('editor');
            }
        }

        //ak je zadana URL adresa clanku na jeho editaciu
        elseif(!empty($parameters[0]))
        {
            $loadedArticle = $articleManager->returnArticle($parameters[0]);
            if($loadedArticle)
                $article = $loadedArticle;
            else
            {
                $this->createMessage('Článok sa nenašiel', 'warning');
                $this->redirect('chyba');
            }
        }

        $this->data['authors'] = $userManager->returnUsers();
        $this->data['article'] = $article;
        //sablona
        $this->view = 'editor';
    }
}