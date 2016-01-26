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
        $validation = new Validation();

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
        $this->data['userRank'] = $validation->returnUserRank($user['admin']);
        $this->data['user'] = $user['name'];
        $this->data['avatar'] = $user['avatar'];
        $this->data['registrationDate'] = $user['registration_date'];
        $this->data['lastVisit'] = $user['last_visit'];
        $this->data['comments'] = $user['comments'];
        $this->data['articles'] = $user['articles'];
        $this->data['sex'] = $user['sex'];
        $this->data['email'] = $user['email'];

        //nastavenie sablony
        $this->view = 'controlPanel';
    }
}