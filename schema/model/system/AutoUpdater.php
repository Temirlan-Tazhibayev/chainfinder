<?php

namespace schema\model\system;

class AutoUpdater
{
    private $conn;

    public function __construct()
    {
        $this->conn = \schema\model\Registry::get('db');
    }

    public function update() 
    {
        $aFiles = scandir(WWW_PATH . '/schema/model/system/updates');
        foreach ($aFiles as $sFile) {
            if (strpos($sFile, 'upd') !== false && !file_exists(WWW_PATH . '/schema/model/system/updates_executed/' . substr($sFile, 0, -4))) {
                $sFilePath = WWW_PATH . '/schema/model/system/updates\\' . $sFile;
                $sFile = str_replace('.php', '', $sFile);
                
                require_once $sFilePath;

                $o = new $sFile();
                echo "Hi";
                try {
                    fopen(WWW_PATH . '/schema/model/system/updates_executed/' . $sFile, "w");
                    $o->execute();
                } catch (\Exception $ex) {
                    \schema\model\Logger::log($ex);
                    
                    d($ex->getMessage() . $ex->getTraceAsString());
                }
            }
        }
    }

    public function executeTsvFiles()
    {

        $inputDir = WWW_PATH . '/schema/model/system/tsv/input/';
        $executedInputDir = WWW_PATH . '/schema/model/system/tsv_executed/';

        $inputFiles = scandir($inputDir);

        foreach ($inputFiles as $inputFile) {
            if ($inputFile !== '.' && $inputFile !== '..') {
                $executedFile = $executedInputDir . $inputFile;
                if (!file_exists($executedFile)) {
                    $inputFilePath = $inputDir . $inputFile;
                    $fileHandle = fopen($inputFilePath, 'r');
                    if ($fileHandle) {
                        $sql = "INSERT INTO inputs 
                        (block_id, transaction_hash, index, time, value, value_usd, recipient, type, script_hex, 
                        is_from_coinbase, is_spendable, spending_block_id, spending_transaction_hash, spending_index, spending_time, 
                        spending_value_usd, spending_sequence, spending_signature_hex, spending_witness, lifespan, cdd) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        $shouldSkipRow = true;
                        $rowCount = 0;

                        while (($row = fgets($fileHandle)) !== false) {
                            if ($shouldSkipRow == true) {
                                $shouldSkipRow = false;
                                continue;
                            }

                            $variables = explode("\t", trim($row));
                            echo $variables[0] . '<br>';

                            $this->conn->add($sql)->execute($variables);

                        }

                        fclose($fileHandle);
                        touch($executedFile);
                    }
                }
            }
        }

        $outputDir = WWW_PATH . '/schema/model/system/tsv/output/';
        $outputFiles = scandir($outputDir);
        foreach ($outputFiles as $oututFile) {
            if ($oututFile !== '.' && $oututFile !== '..') {
                $executedFile = $executedInputDir . $oututFile;
                if (!file_exists($executedFile)) {
                    $outputFilePath = $outputDir . $oututFile;
                    $fileHandle = fopen($outputFilePath, 'r');
                    if ($fileHandle) {
                        $sql = "INSERT INTO outputs 
                        (block_id, transaction_hash, index, time, value, value_usd, recipient, type, script_hex, is_from_coinbase, is_spendable) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        $shouldSkipRow = true;
                        $rowCount = 0;
                        
                        while (($row = fgets($fileHandle)) !== false) {
                            if ($shouldSkipRow == true) {
                                $shouldSkipRow = false;
                                continue;
                            }

                            $variables = explode("\t", trim($row));
                            $this->conn->add($sql)->execute($variables);
                    }

                        fclose($fileHandle);
                        touch($executedFile);
                    }
                }
            }
        }
    }
    
}