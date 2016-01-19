<?php

class RegistraciaController extends Controller
{
        public function process($parameters)
        {
            //hlavicka stranky
            $this->head['title'] = 'Registrácia';

            if($_POST)
            {
                $userManager = new UserManager();
                $validation = new Validation();

                try
                {
                    //validacia zadaneho uzivatelskeho mena
                    $validUsername = $validation->checkUsername($_POST['name']);

                    $userManager->register($validUsername, $_POST['password'], $_POST['password2'], $_POST['email'], $_POST['year']);
                    $this->createMessage('Boli ste úspešne zaregistrovaný.', 'success');
                    $this->createMessage('Pokračujte tým, že sa prihlásite.', 'info');
                    $this->redirect('prihlasenie');
                }
                catch(UserError $error)
                {
                    $this->createMessage($error->getMessage(), 'warning');
                    $this->redirect('registracia');
                }
            }
            //nastavenie sablony
            $this->view = 'registerForm';
        }
}