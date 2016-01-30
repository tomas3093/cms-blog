<?php

class UzivateliaController extends Controller
{
    public function process($parameters)
    {
        $userManager = new UserManager();
        $validation = new Validation();

        $user = $userManager->returnUser();

        //ak bol odoslany formular pre ulozenie udajov uzivatela
        //udaje z formularu sa ukladaju do premennej $data, z ktorej sa nakoniec vyberu a zapisu do DB vsetky udaje
        //v celom bloku try sa odchytavaju vsetky vynimky
        if(isset($_POST['submit']))
        {
            try
            {
                $this->checkUser();

                //polia pre zmenu hesla
                //ak bolo vyplnene aspon jedno z poli
                if(!empty($_POST['old']) || !empty($_POST['password'] != '') || !empty($_POST['password2'] != ''))
                {
                    $userManager->checkPassword($user['name'], $_POST['old']);      //over stare heslo

                    if(!empty($_POST['password']) && !empty($_POST['password2']))   //ak su obidve polia vyplnene
                    {
                        if($_POST['password'] == $_POST['password2'])
                        {
                            $validation->checkPasswordLength($_POST['password']);   //overi minimalnu dlzku hesla
                            $data['password'] = $userManager->returnHash($_POST['password']);  //pripravi pole a zasifruje heslo
                            $this->createMessage('Heslo bolo úspešne zmenené.', 'success');
                        }
                        else
                            throw new UserError('Heslá sa nezhodujú.');
                    }
                    else
                        throw new UserError('Nevyplnené pole.');
                }

                //radio button pohlavie
                if($_POST['sex'] == 'male')
                    $data['sex'] = 'muž';
                else
                    $data['sex'] = 'žena';

                //pole pre email
                $data['email'] = $_POST['email'];

                $userManager->updateUserData($user['name'], $data);    //zapis udajov uzivatela do DB
                $this->createMessage('Nastavenia boli úspešne uložené.', 'success');
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
            }

        }

        //ak bol odoslany formular pre upload profiloveho obrazka
        if(isset($_POST['uploadImage']))
        {
            $imageUpload = new upload($_FILES['image_field']);

            $targetDirectory = 'img/users/' . $user['name'] . '/';
            $filePath = $targetDirectory . 'user_avatar.gif';

            try
            {
                //ak neexistuje uzivatelov adresar, vytvor ho
                if(!file_exists($targetDirectory))
                    mkdir($targetDirectory, '0777', true);

                //ak bol obrazok nahraty
                if ($imageUpload->uploaded)
                {
                    $imageUpload->allowed = array('image/*');           //povolene formaty
                    $imageUpload->mime_check = true;                    //kontrola formatu zapnuta
                    $imageUpload->file_new_name_body = 'user_avatar';   //novy nazov suboru
                    $imageUpload->image_resize = true;                  //zmensenie
                    $imageUpload->image_convert = 'gif';                //konvertovanie na gif
                    $imageUpload->image_x = 100;                        //vysledna sirka 100px
                    $imageUpload->image_ratio_y = true;                 //vyska: auto

                    //zmazanie existujuceho avataru
                    if(file_exists($filePath))
                        unlink($filePath);

                    $imageUpload->process($targetDirectory);            //uloz vysledny obrazok

                    //ak bol obrazok ulozeny
                    if ($imageUpload->processed)
                    {
                        //uloz avatar do databazy
                        $userManager->updateUserData($user['name'], array('avatar' => $filePath));
                        $imageUpload->clean();
                    }
                    else
                        throw new UserError($imageUpload->error);

                    $this->createMessage('Váš obrázok bol úspešne uložený.', 'success');
                }
                else
                    throw new UserError('Obrázok sa nenahral');
            }
            catch(UserError $error)
            {
                $this->createMessage($error->getMessage(), 'warning');
            }
        }

        //ak je zadana URL pre odstranenie uzivatela
        if(!empty($parameters[1]) && $parameters[1] == 'odstranit')
        {
            $this->checkUser(true);
            $userManager->deleteUser($parameters[0]);
            $this->createMessage('Užívateľ<strong> ' . $parameters[0] .' </strong>bol odstránený', 'success');
            $this->redirect('uzivatelia');
        }

        //ak je zadana URL pre zmenu opravnenia uzivatela
        if(!empty($parameters[1]) && $parameters[1] == 'zmenit-opravnenie')
        {
            $this->checkUser(true);
            $requiredUser = $userManager->returnUserInfo($parameters[0]);
            //ak ma uzivatel hodnost 'Clen', zmen ho na 'Redaktor'
            if($requiredUser['admin'] == 0)
            {
                $value = array('admin' => 2);
                $userManager->updateUserData($parameters[0], $value);
                $this->createMessage('Užívateľovi ' . $parameters[0] . ' bola priradená hodnosť Redaktor', 'success');
                $this->redirect('uzivatelia');
            }

            //ak ma uzivatel hodnost 'Redaktor', zmen ho na 'Clen'
            if($requiredUser['admin'] == 2)
            {
                $value = array('admin' => 0);
                $userManager->updateUserData($parameters[0], $value);
                $this->createMessage('Užívateľovi ' . $parameters[0] . ' bola priradená hodnosť Člen', 'success');
                $this->redirect('uzivatelia');
            }
        }

        //ak je zadane URL profilu uzivatela
        if(!empty($parameters[0]))
        {
            $user = $userManager->returnUserInfo($parameters[0]);

            //ak pouzivatel nebol najdeny, presmeruj na chybove hlasenie
            if(!$user)
                $this->redirect('chyba');

            //premenne pre sablonu
            $this->head['title'] = 'Užívateľský profil - ' . $user['name'];
            $this->data['userRank'] = $validation->returnUserRank($user['admin']);
            $this->data['user'] = $user['name'];
            $this->data['avatar'] = $user['avatar'];
            $this->data['registrationDate'] = $user['registration_date'];
            $this->data['lastVisit'] = $user['last_visit'];
            $this->data['comments'] = $user['comments'];
            $this->data['articles'] = $user['articles'];
            $this->data['sex'] = $user['sex'];
            $this->data['email'] = $user['email'];
            $this->data['loggedUser'] = $userManager->returnUser();

            //sablona
            $this->view = 'profile';
        }

        //nie je zadane URL ziadneho profilu, tak vypis zoznam uzivatelov
        else
        {
            $this->checkUser(true);
            //premenne pre sablonu
            $this->head['title'] = 'Správa užívateľov';
            $this->data['admin'] = $user && $user['admin'];
            $this->data['users'] = $userManager->returnUsers();
            $this->data['loggedUser'] = $userManager->returnUser();

            $index = 0;
            foreach($this->data['users'] as $userData)
            {
                $this->data['users'][$index]['rank'] = $validation->returnUserRank($userData['admin']);
                $index += 1;
            }

            //sablona
            $this->view = 'users';
        }

    }
}