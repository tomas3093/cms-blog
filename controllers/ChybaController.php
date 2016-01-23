<?php

class ChybaController extends Controller
{
    public function process($parameters)
    {
        header("HTTP/1.0 404 Not Found");               //hlavicka poziadavky
        $this->head['title'] = 'Stránka sa nenašla';    //hlavicka stranky
        $this->view = 'error';                          //nastavenie sablony
    }
}