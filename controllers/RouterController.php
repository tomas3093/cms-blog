<?php

class RouterController extends Controller 
{
    protected $controller;
    
    
    public function process($parameters)
    {
        $userManager = new UserManager();
        $articleManager = new ArticleManager();

        $parsedURL = $this->parseURL($parameters[0]);          //predanie URL do funkcie

        //zobrazenie uvodnej stranky
        if(empty($parsedURL[0]))
        {
            $frontPageContentManager = new frontPageContentManager();

            $this->data['user'] = $userManager->returnUserName();
            $this->data['title'] = 'Coding - Programovanie, Novinky, Software, Hardware';
            $this->data['key_words'] = 'Programovanie, Novinky, Software, Hardware, Blog, Spravodajstvo';
            $this->data['description'] = 'blog, články o programovaní, novinky zo sveta IT, rôzne zaujímavosti';
            $this->data['messages'] = $this->returnMessages();
            $this->data['shortMessages'] = $frontPageContentManager->returnShortMessages();
            $this->data['novinkyCategoryArticles'] = $frontPageContentManager->returnTopArticlesByCategory('novinky');
            $this->data['programovanieCategoryArticles'] = $frontPageContentManager->returnTopArticlesByCategory('programovanie');
            $this->data['hardwareCategoryArticles'] = $frontPageContentManager->returnTopArticlesByCategory('hardware');
            $this->data['softwareCategoryArticles'] = $frontPageContentManager->returnTopArticlesByCategory('software');
            $this->data['ostatneCategoryArticles'] = $frontPageContentManager->returnTopArticlesByCategory('ostatne');

            $this->view = 'frontPage';
        }
        //zobrazenie zakladneho layoutu
        else
        {
            $controllerClass = $this->camelCase(array_shift($parsedURL)) . 'Controller';    //spracovanie URL na parametre, volanie pozadovaneho kontroleru

            if (file_exists('controllers/' . $controllerClass . '.php')) //ak existuje kontroler z URL
                $this->controller = new $controllerClass;               //vytvor jeho instanciu
            else
                $this->redirect('chyba');                               //ak neexistuje, presmeruj na chybove hlasenie

            $this->controller->process($parsedURL);                 //spracovanie ostatnych parametrov vo vnorenom kontroleri

            //predanie premennych do hlavnej sablony
            $this->data['user'] = $userManager->returnUserName();
            $this->data['loggedUser'] = $userManager->returnUser();
            $this->data['title'] = $this->controller->head['title'];
            $this->data['key_words'] = $this->controller->head['key_words'];
            $this->data['description'] = $this->controller->head['description'];
            $this->data['messages'] = $this->returnMessages();
            $this->data['topArticles'] = $articleManager->returnTopArticles();

            $this->view = 'layout';         //nastavenie hlavnej sablony
        }
    }
    
    
    private function parseURL($url)
    {
        $parsedURL = parse_url($url);                           //parsuje url adresu
        $parsedURL['path'] = ltrim($parsedURL['path'], "/");    //odstrani uvodne lomitko
        $parsedURL['path'] = trim($parsedURL['path']);          //odstrani biele znaky
        
        $splittedPath = explode('/', $parsedURL['path']);       //rozdeli url na jednotlive parametre
        
        return $splittedPath;
    }  
    
    
    private function camelCase($text)
    {
        $sentence = str_replace('-', ' ', $text);               //vytvorenie vety z parametra, nahradenie pomlciek medzerami
        $sentence = ucwords($sentence);                         //velke mismeno v kazdom slove
        $sentence = str_replace(' ', '', $sentence);            //odstranenie medzier
        
        return $sentence;
    }
    
}