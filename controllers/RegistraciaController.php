<?php

class RegistraciaController extends Controller
{
        public function process($parameters)
        {
            $userManager = new UserManager();
            $validation = new Validation();

            //hlavicka stranky
            $this->head['title'] = 'Registrácia';

            if($_POST)
            {
                //odstranenie skodliveho kodu z antispam pola
                $captchaAnswer = strip_tags($_POST['captchaAnswer']);

                try
                {
                    //validacia zadaneho uzivatelskeho mena
                    $validUsername = $validation->checkUsername($_POST['name']);

                    //ak bol spravne vyplneny antispam
                    if($validation->checkCaptcha($_POST['captchaNumber1'], $_POST['captchaNumber2'], $captchaAnswer))
                    {
                        $userManager->register($validUsername, $_POST['password'], $_POST['password2'], $_POST['email']);
                        $this->createMessage('Boli ste úspešne zaregistrovaný.', 'success');
                        $this->createMessage('Pokračujte tým, že sa prihlásite.', 'info');
                        $this->redirect('prihlasenie');
                    }
                    else
                        throw new UserError('Chybne vyplnený antispam');
                }
                catch(UserError $error)
                {
                    $this->createMessage($error->getMessage(), 'warning');
                    $this->redirect('registracia');
                }
            }
            //vytvorenie antispam otazky
            $this->data['captcha'] = $validation->returnCaptcha();
            //nastavenie sablony
            $this->view = 'registerForm';
        }
}