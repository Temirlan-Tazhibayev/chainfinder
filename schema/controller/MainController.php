<?php

namespace schema\controller;

abstract class MainController
{
    public $aErrors = [];
    public function __construct()
    {
    }


    public function view()
    {
        
    }

    public function edit()
    {

    }

    public function canView()
    {
        return false;
    }

    public function canEdit()
    {
        return false;
    }

    public function addError($sError){
        $this->aErrors[] = $sError;
    }

    public function hasErrors(){
        return end($this->aErrors);
    }

}

?>