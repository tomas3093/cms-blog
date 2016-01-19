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
            try
            {
                //adresar pre ulozenie obrazkov
                $targetDirectory = 'img/users/' . $user['name'] . '/';
                $targetFile = $targetDirectory . basename($_FILES['fileToUpload']['name']);
                $imageFileType = pathinfo($targetFile, PATHINFO_EXTENSION);
                $targetFile = $targetDirectory . $user['user_id'] . '.' . $imageFileType;

                //ak neexistuje uzivatelov adresar, vytvor ho
                if(!file_exists($targetDirectory))
                    mkdir($targetDirectory, '0777', true);

                //ak bol nahraty obrazok
                if(!empty($_FILES['fileToUpload']['tmp_name']))
                {
                    //skontroluje ci subor je naozaj obrazok
                    $check = getimagesize($_FILES['fileToUpload']['tmp_name']);
                    if ($check == false)
                        throw new UserError('Súbor nie je obrázok');
                }
                else
                    throw new UserError('Nenahrali ste žiadny obrázok');

                if($_FILES['fileToUpload']['size'] > 512000)
                    throw new UserError('Maximálna veľkosť obrázka je 0,5 MB.');

                if($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg' && $imageFileType != 'gif')
                    throw new UserError('Nepovolený formát obrázku1');

                if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile))
                    $this->createMessage('Váš obrázok bol úspešne uložený.', 'success');
                else
                    throw new UserError('Pri nahrávaní obrázka sa vyskytla chyba.');

                //zapisanie avataru do databazy
                $userManager->updateUserData($user['name'], array('avatar' => $user['name'] . '/' . $user['user_id'] . '.' . $imageFileType));
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