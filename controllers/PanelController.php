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
        $messageManager = new MessageManager();

        //zadane URL pre odhlasenie
        if(!empty($parameters[0]) && $parameters[0] == 'odhlasit')
        {
            $userManager->logOut();
            $this->redirect('prihlasenie');
        }

        //ak bol odoslany formular s oznameniami
        if($_POST)
        {
            if(isset($_POST['noticeField']) && isset($_POST['noticeStyle']))
            {
                $noticeManager->addNotice($_POST['noticeField'], $_POST['noticeStyle']);
                $this->createMessage('Oznam bol úspešne uložený', 'success');
            }
        }

        //zadane URL pre odstranenie oznamu
        if(!empty($parameters[1]) && $parameters[1] == 'odstranit')
        {
            //odstran oznam s danym ID
            $noticeManager->removeNotice($parameters[0]);
            $this->redirect('panel');
        }

        $user = $userManager->returnUser();

        //oznamy
        $this->data['notices'] = $noticeManager->returnNotices();

        //data pre sablonu
        $this->data['admin'] = $user['admin'];
        $this->data['user'] = $user['name'];
        $this->data['receivedMessages'] = $messageManager->returnReceivedMessages($user['name']);
        $this->data['sentMessages'] = $messageManager->returnSentMessages($user['name']);

        //nastavenie sablony
        $this->view = 'controlPanel';
    }
}