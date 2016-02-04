<?php


class PanelController extends Controller
{
    public function process($parameters)
    {
        //do control panela maju pristup len prihlaseny uzivatelia
        $this->checkUser();

        //hlavicka stranky
        $this->head['title'] = 'Ovládací panel';

        $userManager = new UserManager();
        $noticeManager = new NoticeManager();

        //zadane URL pre odhlasenie
        if(!empty($parameters[0]) && $parameters[0] == 'odhlasit')
        {
            $userManager->logOut();
            $this->redirect('prihlasenie');
        }

        //zadane URL pre zobrazenie rozpisanych clankov redaktora alebo admina
        if(!empty($parameters[0]) && $parameters[0] == 'moje-clanky')
        {
            $loggedUser = $userManager->returnUser();
            //ak je prihlaseny redaktor alebo admin
            if(($loggedUser['admin'] == 1) || ($loggedUser['admin'] == 2))
            {
                $articleManager = new ArticleManager();
                $unpublishedArticles = $articleManager->returnUnpublishedArticles();

                //vybratie iba tych nepublikovanych clankov, ktorych autor je momentalne prihlaseny uzivatel
                $userArticles = array();
                foreach($unpublishedArticles as $article)
                {
                    if($article['author'] == $loggedUser['name'])
                        $userArticles[] = $article;
                }

                //ak nie su ziadne clanky na zobrazenie
                if(sizeof($userArticles) == 0)
                    $this->createMessage('Žiadne články na zobrazenie', 'info');

                $this->data['userArticles'] = $userArticles;
                $this->head['title'] = 'Moje články';

                $this->view = 'myArticles';
            }
        }

        //ak bol odoslany formular s oznameniami
        if($_POST)
        {
            //overenie ci je prihlaseny admin; nie su osetrene vstupy
            $this->checkUser(true);
            if(isset($_POST['noticeField']) && isset($_POST['noticeStyle']))
            {
                $noticeManager->addNotice($_POST['noticeField'], $_POST['noticeStyle']);
                $this->createMessage('Oznam bol úspešne uložený', 'success');
            }
        }

        //zadane URL pre odstranenie oznamu
        if(!empty($parameters[1]) && $parameters[1] == 'odstranit')
        {
            //overenie ci je prihlaseny admin
            $this->checkUser(true);
            //odstran oznam s danym ID
            $noticeManager->removeNotice($parameters[0]);
            $this->redirect('panel');
        }

        //zadane URL pre zobrazenie control panelu
        if(empty($parameters[0]))
        {
            $user = $userManager->returnUser();

            //oznamy
            $this->data['notices'] = $noticeManager->returnNotices();

            //data pre sablonu
            $this->data['admin'] = $user['admin'];
            $this->data['user'] = $user['name'];

            $messageManager = new MessageManager();
            $this->data['receivedMessages'] = $messageManager->returnReceivedMessages($user['name']);
            $this->data['sentMessages'] = $messageManager->returnSentMessages($user['name']);

            //nastavenie sablony
            $this->view = 'controlPanel';
        }

    }
}