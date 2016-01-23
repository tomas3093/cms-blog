<?php

class KategoriaController extends Controller
{
    public function process($parameters)
    {
        $articleManager = new ArticleManager();
        $userManager = new UserManager();
        $validation = new Validation();

        $user = $userManager->returnUser();
        $this->data['admin'] = $user['admin'];


        //ak je zadana existujuca kategoria
        if(!empty($parameters[0]) && ($parameters[0] == 'novinky' || $parameters[0] == 'programovanie' || $parameters[0] == 'hardware' || $parameters[0] == 'software' || $parameters[0] == 'ostatne'))
        {
            //nazov kategorie
            $category = $validation->returnCategoryName($parameters[0]);

            //ak je zadane URL pre zobrazenie konkretnej strany
            if(!empty($parameters[1]) && $parameters[1] == 'page')
            {
                //ak je zadane cislo strany
                if(!empty($parameters[2]) && is_numeric($parameters[2]))
                {
                    if($parameters[2] == 1)
                        $offset = 0;
                    else
                        $offset = ($parameters[2] * 5) - 5;

                    //zisti pocet clankov, a pripravi pocet stran
                    $articles = $articleManager->returnArticlesByCategory($parameters[0], 0);   //vsetky clanky z danej kategorie
                    $countArticles = sizeof($articles);
                    if($countArticles == 0)
                        $this->redirect('chyba');

                    $modulo = $countArticles % 5;
                    if($modulo == 0)
                        $this->data['pages'] = $countArticles / 5;
                    else
                        $this->data['pages'] = intval(($countArticles / 5) + 1);

                    $this->data['currentPage'] = $parameters[2];                                    //aktualna strana
                    $this->data['category'] = $validation->returnCategoryName($parameters[0]);      //aktualna kategoria

                    //vratenie clankov s pozadovanym offsetom
                    $articles = $articleManager->returnArticlesByCategory($parameters[0], $offset);
                    $this->data['articles'] = $validation->statusOfArticles($articles);

                    //hlavicka stranky
                    $this->head = array(
                        'title' => $category,
                        'key_words' => 'Kategória - ' . $category,
                        'description' => 'Články z kategórie ' . $category
                    );
                    $this->view = 'category';
                }
                //ak nie je zadane cislo strany
                else
                    $this->redirect('kategoria/' . $parameters[0]);
            }
            else
            {
                $articles = $articleManager->returnArticlesByCategory($parameters[0], 0);
                $this->data['articles'] = $validation->statusOfArticles($articles);

                //zisti pocet clankov, a pripravi pocet stran
                $countArticles = sizeof($articles);
                if($countArticles == 0)
                    $this->redirect('chyba');

                $modulo = $countArticles % 5;
                if($modulo == 0)
                    $this->data['pages'] = $countArticles / 5;
                else
                    $this->data['pages'] = intval(($countArticles / 5) + 1);

                $this->data['currentPage'] = 1;             //aktualna strana
                $this->data['category'] = $category;        //aktualna kategoria

                //hlavicka stranky
                $this->head = array(
                    'title' => $category,
                    'key_words' => 'Kategória - ' . $category,
                    'description' => 'Články z kategórie ' . $category
                );
                $this->view = 'category';
            }

        }
        //ak kategoria neexistuje
        else
            $this->redirect('chyba');
    }
}