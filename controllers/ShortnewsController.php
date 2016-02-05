<?php

class ShortnewsController extends Controller
{
    public function process($parameters)
    {
        $frontPageContentManager = new frontPageContentManager();
        $userManager = new UserManager();
        $validation = new Validation();

        $loggedUser = $userManager->returnUser();

        //ak je zadane URL na odstranenie spravy
        if(!empty($parameters[1]) && $parameters[1] == 'odstranit')
        {
            //overi ci je uzivatel admin
            $this->checkUser(true);

            $shortMessage = $frontPageContentManager->returnShortMessage($parameters[0]);
            if($shortMessage)
            {
                $frontPageContentManager->deleteShortMessage($parameters[0]);
                $this->createMessage('Krátka správa bola odstránená', 'success');
                $this->redirect('shortnews');
            }
            else
                $this->redirect('chyba');
        }

        //ak je zadane URL na zobrazenie spravy
        if(!empty($parameters[1]) && $parameters[1] == 'zobrazit')
        {
            $shortMessage = $frontPageContentManager->returnShortMessage($parameters[0]);
            if($shortMessage)
            {
                $this->data['shortMessage'] = $shortMessage;
                $this->data['loggedUser'] = $loggedUser;
                $this->head['title'] = $shortMessage['title'];
                $this->view = 'shortMessage';
            }
            else
                $this->redirect('chyba');
        }

        //ak je zadane URL na zobrazenie vsetkych kratkych sprav
        if(empty($parameters))
        {
            $this->data['shortMessages'] = array();
            $shortMessages = $frontPageContentManager->returnShortMessages();
            //skratenie popisov jednotlivych kratkych sprav na 80 znakov
            foreach($shortMessages as $shortMessage)
            {
                $shortMessage['content'] = $validation->stringLimitLenght($shortMessage['content'], 80);
                $this->data['shortMessages'][] = $shortMessage;
            }

            $this->data['loggedUser'] = $loggedUser;
            $this->head['title'] = 'Krátke správy';
            $this->view = 'shortMessages';
        }
    }
}