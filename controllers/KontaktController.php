<?php

class KontaktController extends Controller
{
    public function process($parameters)
    {
        $emailSender = new EmailSender();
        $validation = new Validation();

        $this->head = array(
            'title' => 'Kontaktný formulár',
            'key_words' => 'kontakt, email, formulár',
            'description' => 'Kontaktný formulár blogu.'
        );
        
        if($_POST)
        {
            //odstranenie skodliveho kodu z formularovych poli
            $captchaAnswer = strip_tags($_POST['captchaAnswer']);
            $message = htmlspecialchars($_POST['message']);

            try
            {
                //ak bol spravne vyplneny antispam
                if($validation->checkCaptcha($_POST['captchaNumber1'], $_POST['captchaNumber2'], $captchaAnswer))
                {
                    $emailSender->send('info@coding.wz.sk', 'Správa z webu', $message, $_POST['email']);
                    $this->createMessage('Správa bola úspešne odoslaná', 'success');
                    $this->redirect('');
                }
                else
                    throw new UserError('Chybne vyplnený antispam.');
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
            }
        }
        //vytvorenie antispam otazky
        $this->data['captcha'] = $validation->returnCaptcha();
        //nastavenie sablony
        $this->view = 'contactForm';
    }
}