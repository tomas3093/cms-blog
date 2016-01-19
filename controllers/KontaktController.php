<?php

class KontaktController extends Controller
{
    public function process($parameters)
    {
        $this->head = array(
            'title' => 'Kontaktný formulár',
            'key_words' => 'kontakt, email, formulár',
            'description' => 'Kontaktný formulár blogu.'
        );
        
        if($_POST)
        {
            try
            {
                $emailSender = new EmailSender();
                $emailSender->sendWithAntispam($_POST['year'], 'info@coding.wz.sk', 'Správa z webu', $_POST['message'], $_POST['email']);
                $this->createMessage('Správa bola úspešne odoslaná', 'success');
                $this->redirect('kontakt');
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
            }
        }
        $this->view = 'contactForm';
    }
}