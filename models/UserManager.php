<?php

//spravca uzivatelov redakcneho systemu

class UserManager
{
    //vrati zasifrovane heslo
    public function returnHash($password)
    {
        $salt = '43t4v#$^@b02$';
        return hash('sha256', $password . $salt);
    }

    //registracia noveho uzivatela do systemu
    public function register($name, $password, $password2, $email)
    {
        if($password != $password2)
            throw new UserError('Heslo sa nezhoduje.');

        //overenie spravneho tvaru hesla
        $validation = new Validation();
        $validation->checkPasswordLength($password);

        //aktualny cas
        $date = new DateTime();
        $time = $date->getTimestamp();

        $user = array(
            'name' => $name,
            'password' => $this->returnHash($password),
            'registration_date' => $time,
            'last_visit' => $time,
            'email' => $email
        );
        try
        {
            Database::insert('users', $user);
        }
        catch(PDOException $error)
        {
            throw new UserError('Zadané meno sa už používa.');
        }
    }

    //prihlasi uzivatela do systemu
    public function logIn($name, $password)
    {
        $user = Database::querryOne('
            SELECT user_id, name, avatar, admin, registration_date, last_visit, comments, articles, sex, email
            FROM users
            WHERE name = ? AND password = ?
            ', array($name, $this->returnHash($password)));
        if(!$user)
            throw new UserError('Neplatné meno alebo heslo.');

        //ulozenie prihlaseneho uzivatela do SESSION
        $_SESSION['user'] = $user;

        //Zapisanie casu prihlasenia do databazy
        $date = new DateTime();
        $time = $date->getTimestamp();
        $lastLogIn = array('last_visit' => $time);
        Database::update('users', $lastLogIn, 'WHERE name = ?', array($user['name']));
    }

    //odhlasi uzivatela
    public function logOut()
    {
        unset($_SESSION['user']);
    }

    //vrati aktualne prihlaseneho uzivatela
    public function returnUser()
    {
        if(isset($_SESSION['user']))
            return $_SESSION['user'];
        return null;
    }

    //vrati meno aktualne prihlaseneho uzivatela a pripravi bocny panel
    public function returnUserName()
    {
        if(isset($_SESSION['user']))
        {
            $userName = $_SESSION['user'][1];
            $userAvatar = $_SESSION['user']['avatar'];
            $string = "Prihlaseny: <strong>" . $userName . "</strong><p><img src='/" . $userAvatar . "'></p><a href='/panel'><i class='fa fa-clone'></i> Ovládací panel</a><br>"
                . "</strong><a href='/panel/odhlasit'><i class='fa fa-sign-out'></i> Odhlásiť</a>";
        }
        else
            $string = "<a href='/prihlasenie'><i class='fa fa-sign-in'></i> Prihlásenie</a>";
        return $string;
    }

    //vrati vsetkych registrovanych uzivatelov
    public function returnUsers()
    {
        return Database::querryAll('
          SELECT user_id, name, avatar, admin, registration_date, last_visit, comments, articles, sex, email
          FROM users
          ORDER BY user_id ASC
        ');
    }

    //vrati udaje o pozadovanom uzivatelovi
    public function returnUserInfo($user)
    {
        return Database::querryOne('
            SELECT user_id, name, avatar, admin, registration_date, last_visit, comments, articles, sex, email
            FROM users
            WHERE name = ?
        ', array($user));
    }

    //vymaze uzivatela z databazy
    public function deleteUser($user)
    {
        Database::querry('DELETE FROM users WHERE name = ?', array($user));
    }

    //overi heslo aktualne prihlaseneho uzivatela
    public function checkPassword($name, $password)
    {
        $querry = Database::querryOne('
            SELECT user_id
            FROM users
            WHERE name = ? AND password = ?
            ', array($name, $this->returnHash($password)));
        if(!$querry)
        {
            throw new UserError('Nesprávne heslo.');
        }
    }

    //ulozi aktualizovane udaje uzivatela
    public function updateUserData($name, $values = array())
    {
        Database::update('users', $values, 'WHERE name = ?', array($name));

        //ak su to udaje aktualne prihlaseneho uzivatela, tak obnov udaje v #_SESSION
        $loggedUser = $this->returnUser();
        if($loggedUser['name'] == $name)
        {
            $user = Database::querryOne('
            SELECT user_id, name, avatar, admin, registration_date, last_visit, comments, articles, sex, email
            FROM users
            WHERE name = ?
            ', array($name));
            $_SESSION['user'] = $user;
        }
    }

    //aktualizuje pocet clankov a komentarov uzivatela
    public function getUserData($user)
    {
        //ziska pocet clankov
        $count = Database::querryOne('
            SELECT COUNT(*)
            FROM articles
            WHERE author = ?
            ', array($user));
        $values['articles'] = $count[0];

        //ziska pocet komentarov
        $count = Database::querryOne('
            SELECT COUNT(*)
            FROM comments
            WHERE author = ?
            ', array($user));
        $values['comments'] = $count[0];

        //zapise data do databazy
        $this->updateUserData($user, $values);
    }
}