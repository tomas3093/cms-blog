<?php

class Database 
{
    private static $connection;
    
    private static $dbConfig = array(                               //nastavenie spojenia s databazou
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false,
    );
    
    
    public static function connect($host, $user, $password, $db)
    {
        if(!isset(self::$connection))                               //ak nie je nadviazane spojenie, tak sa pripoj
        {
            self::$connection = @new PDO(
                "mysql:host=$host; dbname=$db",
                $user,
                $password,
                self::$dbConfig
            ); 
        }
    }
    
    
    public static function querryOne($querry, $parameters = array())
    {
        $return = self::$connection->prepare($querry);              //pripravi text poziadavky so zastupnymi znakmi
        $return->execute($parameters);                              //pripoji pole parametrov a vykona poziadavku
        return $return->fetch();                                    //vrati 1 riadok z tabulky
    }
    
    
    public static function querryAll($querry, $parameters = array())
    {
        $return = self::$connection->prepare($querry);              //pripravi text poziadavky so zastupnymi znakmi
        $return->execute($parameters);                              //pripoji pole parametrov a vykona poziadavku
        return $return->fetchAll();                                 //vrati vsetky riadky z poziadavky
    }

    //vykona sql prikaz a vrati pocet ovplyvnenych riadkov v databaze
    public static function querry($querry, $parameters = array())
    {
        $return = self::$connection->prepare($querry);
        $return->execute($parameters);
        return $return->rowCount();
    }

    //vrati SQL prikaz pre vlozenie noveho zaznamu do databazy
    public static function insert($table, $parameters = array())
    {
        return self::querry("INSERT INTO `$table` (`".
            implode('`, `', array_keys($parameters)).
            "`) VALUES (".str_repeat('?,', sizeOf($parameters)-1)."?)",
            array_values($parameters));
    }

    //vrati SQLprikaz pre zmenu existujuceho zaznamu v databaze
    public static function update($table, $values = array(), $expression, $parameters = array())
    {
        return self::querry("UPDATE `$table` SET `".
            implode('` = ?, `', array_keys($values)).
            "` = ? " . $expression,
            array_merge(array_values($values), $parameters));
    }

    //vrati id posledneho vlozeneho zaznamu
    public static function getLastId()
    {
        return self::$connection->lastInsertId();
    }
    
}