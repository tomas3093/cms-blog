<?php

class RegistraciaController extends Controller
{
        public function process($parameters)
        {
            $userManager = new UserManager();
            $validation = new Validation();

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
                        $this->createMessage('Pokračujte tým, že sa prihlásite.', 'info');
                        $this->createMessage('Boli ste úspešne zaregistrovaný.', 'success');
                        $this->redirect('prihlasenie');
                    }
                    else
                        throw new UserError('Chybne vyplnený antispam');
                }
                catch(UserError $error)
                {
                    $this->createMessage($error->getMessage(), 'warning');
                }
            }
            //ak bol odoslany formular, zachovanie vyplneneho mena a emailu
            $this->data['name'] = '';
            if(isset($_POST['name']))
                $this->data['name'] = $_POST['name'];
            $this->data['email'] = '@';
            if(isset($_POST['email']))
                $this->data['email'] = $_POST['email'];


            $this->data['captcha'] = $validation->returnCaptcha();  //antispam otazka

            $this->head['title'] = 'Registrácia';   //title
            $this->view = 'registerForm';           //sablona
        }
}