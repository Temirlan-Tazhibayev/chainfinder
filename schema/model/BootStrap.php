<?php

namespace schema\model;

class BootStrap
{
    public function __construct($aConfig)
    {
        try {
            \schema\model\Registry::set('cfg', $aConfig);
            \schema\model\Registry::set('db', new \schema\model\Database($aConfig));
            
            $oAutoUpdater = new \schema\model\system\AutoUpdater();
            $oAutoUpdater->update();
            $oAutoUpdater->executeTsvFiles();
            
            // echo \schema\model\Registry::get('cfg')["LOG_PATH"];
            
            session_start();
            $oController = new \schema\controller\NetworkController();
            $oController->handleRequest();


        } catch(\Exception $ex) {
            \schema\model\Logger::log($ex);
            // echo
            d($ex->getMessage() . $ex->getTraceAsString());
        }
    }
}

?>