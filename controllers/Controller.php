<?php 

abstract class Controller 
{
    
    protected $data = array();
    protected $view = "";
    protected $head = array('title' => '', 'key_words' => '', 'description' => '');
    
    abstract function process($parameters);

    //nastavenie spravnej sablony
    public function outputView() 
    {
        if($this->view) 
        {
            extract($this->data);
            require("views/" . $this->view . ".phtml");
        }
    }
    
    //presmerovanie
    public function redirect($url)                              
    {
        header("Location: /$url");
		header("Connection: close");
        exit;
    }

    //vytvori spravu
    public function createMessage($message, $style)
    {
        if(isset($_SESSION['messages']))
        {
            $index = sizeof($_SESSION['messages']);
            $_SESSION['messages'][$index] = array($message, $style);
        }
        else
        {
            $_SESSION['messages'][0] = array($message, $style);
        }
    }
    
    //vrati aktualne spravy zo session
    public static function returnMessages()
    {
        if(isset($_SESSION['messages']))
        {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);                   //zmazanie session, aby sa neobjavovali stare spravy
            return $messages;
        }
        else
            return array();
    }

    //vrati najcitanejsie clanky
    public function topArticles()
    {
        $articleManager = new ArticleManager();
        return $articleManager->returnTopArticles();
    }

    //overuje ci je uzivatel prihlaseny, pripadne ci je administrator - pomocou nepovinneho parametera
    public function checkUser($admin = false)
    {
        $userManager = new UserManager();
        $user = $userManager->returnUser();
        if(!$user || ($admin && ($user['admin'] != '1')))
        {
            $this->createMessage('Nemáte dostatočné oprávnenie.', 'info');
            $this->redirect('prihlasenie');
        }
    }
        

}