<?php
session_start();                                            //zapnutie uvodnej session
mb_internal_encoding("UTF-8");                              //interne php kodovanie UTF-8

function autoloadFunction($class) 
{
    #konci nazov triedy na "controler" ? 
    if(preg_match('/Controller$/', $class))
        require("controllers/" . $class . ".php");
        
    else
        require("models/" . $class . ".php");
}

spl_autoload_register('autoloadFunction');                  //funkcia autoload


Database::connect("127.0.0.1", "root", "", "cms_db");       //pripojenie sa k databaze

$router = new RouterController();                           //vytvorenie smerovaca URL adries
$router->process(array($_SERVER['REQUEST_URI']));           //spracovanie aktualnej URL

$router->outputView();										//vyrenderovanie sablony