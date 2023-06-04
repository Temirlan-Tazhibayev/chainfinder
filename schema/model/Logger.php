<?php

namespace schema\model;

class Logger
{
    public static function log(\Exception $oE)
    {
        $FilePath = vsprintf(
            '%s/%s.log', array(
                \schema\model\Registry::get('cfg')["LOG_PATH"],
                    date('Ymd')
            )
        );
        $Log_String = date('Y-m-d H:i:s') . " " . $oE->getMessage() . $oE->getTraceAsString();
        $Log_String =  trim(preg_replace('/\s+/', ' ', $Log_String));
        $Log_String .= PHP_EOL;
        file_put_contents($FilePath, $Log_String, FILE_APPEND);
    }
}

?>