<?php


class SpravaController extends Controller
{
    public function process($parameters)
    {
        //iba prihlaseny uzivatelia
        $this->checkUser();

        $userManager = new UserManager();
        $messageManager = new MessageManager();

        $loggedUser = $userManager->returnUser();

        //ak je zadane URL pre vytvorenie novej spravy
        if(!empty($parameters[0]) && $parameters[0] == 'vytvorit')
        {
            $users = $userManager->returnUsers();
            
            //vytvorenie zdroja pre automaticke doplnovanie uzivatelov v poli 'prijemca'
            $autocompleteSource = '';
            foreach($users as $user)
            {
                $autocompleteSource = $autocompleteSource . "'" . $user['name'] . "'" . ', ';
            }
            $autocompleteSource = rtrim($autocompleteSource, ", ");
            $this->data['autocompleteSource'] = $autocompleteSource;

            //ak bol zadany prijemca v URL
            if(!empty($parameters[1]))
                $this->data['recipient_url'] = strip_tags($parameters[1]);
            else
                $this->data['recipient_url'] = '';

            $this->head['title'] = 'Nová správa';
            $this->view = 'messageForm';
        }

        //ak je zadane URL na odstranenie spravy
        if(!empty($parameters[1]) && $parameters[1] == 'odstranit')
        {
            $message = $messageManager->returnMessage($parameters[0]);
            if($message)
            {
                $messageManager->deleteMessage($parameters[0], $loggedUser);
                $this->createMessage('Správa bola odstránená', 'success');
                $this->redirect('panel');
            }
            else
                $this->redirect('chyba');
        }

        //ak je zadane URL na zobrazenie spravy
        if(!empty($parameters[1]) && $parameters[1] == 'zobrazit')
        {
            $message = $messageManager->returnMessage($parameters[0]);
            //ak sprava existuje a otvara ju prijimatel, alebo odosielatel
            if($message && (($message['sender'] == $loggedUser['name']) || ($message['recipient'] == $loggedUser['name'])))
            {
                //pri prvom otvoreni, oznac spravu ako precitanu
                if(($message['unread'] == 1) && ($message['recipient'] == $loggedUser['name']))
                    $messageManager->readMessage($message['message_id']);

                $this->head['title'] = 'Správa - ' . $message['subject'];
                $this->data['message'] = $message;

                $this->view = 'message';
            }
            else
                $this->redirect('chyba');
        }

        //ak bol odoslany formular pre odoslanie spravy
        if($_POST)
        {
            $sender = $userManager->returnUser();
            $recipient = strip_tags($_POST['recipient']);
            $subject = htmlspecialchars($_POST['subject']);
            $message = htmlspecialchars($_POST['message']);

            try
            {
                $recipient = $userManager->returnUserInfo($recipient);
                if(!$recipient)
                    throw new UserError('Užívateľ neexistuje');

                if($sender['name'] == $recipient['name'])
                    throw new UserError('Nemôžete poslať správu sám sebe');

                //odoslanie spravy do databazy
                $messageManager->sendMessage($sender['name'], $recipient['name'], $subject, $message);
                $this->createMessage('Vaša správa bola úspešne odoslaná', 'success');
                $this->redirect('panel');
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
            }
        }

        //zadane URL bez parametrov
        if(empty($parameters))
        {
            $this->redirect('chyba');
        }
    }
}