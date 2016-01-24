<?php

class EditorController extends Controller
{
    public function process($parameters)
    {
        //editor je pristupny iba pre admina
        $this->checkUser(true);

        $articleManager = new ArticleManager();
        $userManager = new UserManager();
        $validation = new Validation();

        $article = array(
            'article_id' => '',
            'title' => '',
            'thumbnail_img' => '',
            'content' => '',
            'url' => '',
            'category' => '',
            'description' => '',
            'key_words' => '',
            'author' => ''
        );

        //ak je odoslany formular
        if($_POST)
        {
            //ziskanie clanku z $_POST
            $keys = array('article_id', 'title', 'thumbnail_img', 'content', 'url', 'category', 'description', 'key_words', 'author', 'public');
            $article = array_intersect_key($_POST, array_flip($keys));

            //upload a spracovanie suboru
            $imageUpload = new upload($_FILES['image_field']);

            //nastavenie ID noveho clanku
            if(empty($article['article_id']))
                $article['article_id'] = $articleManager->returnLastArticleId() + 1;

            $targetDirectory = 'img/articles/' . $article['article_id'] . '/';
            $filePath = $targetDirectory . 'thumbnail.png';

            try
            {
                //vytvori novy adresar podla ID noveho clanku
                if(!file_exists($targetDirectory))
                    mkdir($targetDirectory, '0777', true);

                //ak bol obrazok nahraty
                if ($imageUpload->uploaded)
                {
                    $imageUpload->allowed = array('image/*');           //povolene formaty
                    $imageUpload->mime_check = true;                    //kontrola formatu zapnuta
                    $imageUpload->file_new_name_body = 'thumbnail';     //novy nazov suboru
                    $imageUpload->image_resize = true;                  //zmensenie
                    $imageUpload->image_convert = 'png';                //konvertovanie na png
                    $imageUpload->image_x = 120;                        //vysledna sirka 120px
                    $imageUpload->image_ratio_y = true;                 //vyska: auto

                    //zmazanie existujuceho nahladoveho obrazka
                    if(file_exists($filePath))
                        unlink($filePath);

                    $imageUpload->process($targetDirectory);            //uloz vysledny obrazok

                    //ak bol obrazok ulozeny
                    if ($imageUpload->processed)
                    {
                        //uloz cestu k obrazku do '$article'
                        $article['thumbnail_img'] = $filePath;
                        $imageUpload->clean();
                    }
                    else
                        throw new UserError($imageUpload->error);
                }
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
            }

            //ulozenie clanku do databazy
            try
            {
                //vytvorenie URL adresy z pola title
                $article['url'] = $validation->checkUrl($article['title']);

                //ulozenie clanku do databazy
                $articleManager->saveArticle($_POST['article_id'], $article);
                $this->createMessage('Článok bol úspešne uložený', 'success');

                //ak clanok este nebol publikovany, presmeruj na nepublikovane clanky
                if($article['public'] == '0')
                    $this->redirect('clanky/unpublished');
                else
                    $this->redirect('clanky');
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
                $this->redirect('editor');
            }
        }

        //ak je zadana URL adresa clanku na jeho editaciu
        if(!empty($parameters[0]))
        {
            $loadedArticle = $articleManager->returnArticle($parameters[0]);
            if($loadedArticle)
                $article = $loadedArticle;
            else
            {
                $this->createMessage('Článok sa nenašiel', 'warning');
                $this->redirect('chyba');
            }
            $this->data['article'] = $article;

            //hlavicka stranky
            $this->head = array(
                'title' => 'Editor - ' . $article['title'],
                'key_words' => 'coding.wz.sk - editor',
                'description' => 'Editor článkov'
            );
            $this->view = 'editor';
        }
        //pisanie noveho clanku
        else
        {
            $article['author'] = $userManager->returnUser()['name'];
            $this->data['article'] = $article;

            //hlavicka stranky
            $this->head = array(
                'title' => 'Editor - Nový článok',
                'key_words' => 'coding.wz.sk - editor',
                'description' => 'Editor článkov'
            );
            $this->view = 'editor';
        }
    }
}