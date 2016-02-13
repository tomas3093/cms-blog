<?php

class PrihlasenieController extends Controller
{
    public function process($parameters)
    {
        $userManager = new UserManager();
        if($userManager->returnUser())
            $this->redirect('panel');

        if($_POST)
        {
            try
            {
                $userManager->logIn($_POST['name'], $_POST['password']);
                $this->createMessage('Boli ste úspešne prihlásený.', 'success');
                $this->redirect('panel');
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
            }
        }

        $this->head['title'] = 'Prihlásenie';   //title
        $this->view = 'logIn';                  //sablona
    }
}