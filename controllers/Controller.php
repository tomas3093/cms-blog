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

    //vytvori spravu a prida ikonu
    public function createMessage($message, $style)
    {
        //pridanie ikony k sprave, podla '$style'
        switch($style)
        {
            case 'info':
                $message = '<i class="fa fa-info-circle"></i> ' . $message;
                break;
            case 'success':
                $message = '<i class="fa fa-check-circle-o"></i> ' . $message;
                break;
            case 'warning':
                $message = '<i class="fa fa-exclamation-triangle"></i> ' . $message;
                break;
        }

        //ulozenie spravy do $_SESSION
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
    
    //vrati aktualne spravy zo session a oznamy z databazy
    public static function returnMessages()
    {
        $noticeManager = new NoticeManager();

        //ak su ulozene spravy v SESSION
        if(isset($_SESSION['messages']))
        {
            //oznamy z databazy
            $notices = $noticeManager->returnNotices();
            //spravy zo SESSION
            $messages = $_SESSION['messages'];
            //ulozenie oznamov a sprav do '$messages'
            $messages = array_merge($messages, $notices);

            //zmazanie SESSION, aby sa neobjavovali stare spravy
            unset($_SESSION['messages']);

        }
        else
            //oznamy z databazy
            $messages = $noticeManager->returnNotices();

        if(!empty($messages))
            return array_reverse($messages);        //vrati pole v opacnom poradi (navrchu budu oznamy, dole spravy)
        else
            return array();
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