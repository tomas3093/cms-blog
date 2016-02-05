<?php

//trieda trieda s validacnymi a dalsimi metodami

class Validation
{
    //vrati hodnost uzivatela
    public function returnUserRank($value)
    {
        $rank = '';

        switch($value)
        {
            case 0:
                $rank = 'Člen';
                break;
            case 1:
                $rank = 'Administrátor';
                break;
            case 2:
                $rank = 'Redaktor';
                break;
        }
        return $rank;
    }

    //vrati nazov kategorie
    public function returnCategoryName($value)
    {
        $category = '';

        switch($value)
        {
            case 'novinky':
                $category = 'Novinky';
                break;
            case 'programovanie':
                $category = 'Programovanie';
                break;
            case 'hardware':
                $category = 'Hardware';
                break;
            case 'software':
                $category = 'Software';
                break;
            case 'ostatne':
                $category = 'Ostatné';
                break;
        }
        return $category;
    }

    //overi minimalnu dlzku hesla
    public function checkPasswordLength($password)
    {
        if(strlen($password) < 5)
            throw new UserError('Heslo je príliš krátke. Zadajte aspoň 5 znakov.');
    }

    //vrati status vsetkych clankov (publikovany/nepublikovany)
    public function statusOfArticles($articles = array())
    {
        $step = 0;
        foreach($articles as $article)
        {
            if($article['public'] == '0')
                $articles[$step]['status'] = '<a href="' . $article['url'] . '/publikovat" title="Publikovať"><i class="fa fa-eye-slash"></i> Nepublikovaný</a>';
            else
                $articles[$step]['status'] = '<i class="fa fa-eye"></i> Publikovaný';
            $step += 1;
        }
        return $articles;
    }

    //vrati spravny tvar URL adresy
    public function checkUrl($url)
    {
        if(empty($url))
            throw new UserError('Vyplňte titulok!');

        $url = strip_tags($url);                                                    //odstrani HTML a PHP tagy
        $url = mb_strtolower($url);                                                 //zmeni velke pismena na male
        $url = trim($url);                                                          //odstrani biele znaky
        $url = str_replace(Array(" ", "_"), "-", $url);                             //nahradi medzery a podtrzitka pomlckami
        $url = iconv('utf-8', 'ascii//TRANSLIT', $url);                             //odstrani diakritiku
        $url = str_replace(Array("(",")",".","!","?",",","\"","'",":",";", "/"), "", $url);     //odstrani /().!,"'?:;

        if(strlen($url) > 90)
            throw new UserError('Príliš dlhý titulok!');

        if(strlen($url) < 2)
            throw new UserError('Krátky titulok! Minimálna dĺžka je 2 znaky.');

        return $url;
    }

    //validacia uzivatelskeho mena
    public function checkUsername($username)
    {
        if(empty($username))
            throw new UserError('Zadajte používateľské meno!');

        $username = strip_tags($username);

        //test retazca pomocou regularneho vyrazu (1. musi zacinat pismenom, 2. dlzka 4 - 32 znakov, 3. obsahuje iba pismena a cisla)
        if(!preg_match('/^[A-Za-z][A-Za-z0-9]{3,31}$/', $username))
            throw new UserError('Používateľské meno obsahuje nepovolené znaky alebo je v nesprávnom tvare!');

        return $username;
    }

    //vygeneruje antispam otazku
    public function returnCaptcha()
    {
        $digits = array('nula', 'jeden', 'dva', 'tri', 'štyri', 'päť', 'šesť', 'sedem', 'osem', 'deväť');
        $number1 = rand(0, 9);
        $number2 = rand(0, 9);
        $captcha = $digits[$number1] . ' + ' . $digits[$number2] . ' = ';

        $values = array(
            'number1' => $number1,
            'number2' => $number2,
            'captcha' => $captcha
        );

        return $values;
    }

    //zisti ci je spravne zodpovedana antispam otazka
    public function checkCaptcha($number1, $number2, $answer)
    {
        $result = $number1 + $number2;

        if($result == $answer)
            return true;
        else
            return false;
    }

    //skrati retazec na zadany pocet znakov a prida na koniec '...'
    public function stringLimitLenght($string, $maxLenght)
    {
        if (strlen($string) > $maxLenght)
            $string = substr($string, 0, $maxLenght) . '...';

        return $string;
    }
}