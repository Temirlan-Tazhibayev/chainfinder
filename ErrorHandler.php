<?php

function a($aArr, $sKey, $mDef = '') {
    if (isset($aArr[$sKey]))
        return $aArr[$sKey];
    return $mDef;
}

function d($mValues)
{
    $iNumArgs = func_num_args();
    ?>
    <style>
        table.debug {
            width: 100%;
        }

        td.debug_title {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
            width: 10%;
            padding: 10pt;
        }

        td.debug_message {
            text-align: left;
            width: 90%;
            padding: 10pt;
        }

        td.debug_debug {
            text-align: left;
            font-weight: bold;
            padding: 10pt;
        }
    </style>
    
    <table border="1" bordercolor="#000000" cellpadding="0" cellspacing="0"
           width="100%">
        <tr>
            <td class="debug_debug" colspan="2">[ Debug: ]</td>
        </tr>
        <?php
        for ($i = 0; $i < $iNumArgs; $i++) {
            ?>
            <?php
            $mValue = func_get_arg($i) ?>
            <tr>
                <td class="debug_title">Debug[<?= $i ?>]:</td>
                <td class="debug_message"><i>(<?= ((is_object($mValue)) ? get_class($mValue) : gettype($mValue)) ?>)</i>
                    <pre><?php
                        ((is_null($mValue) || is_bool($mValue) ? var_dump($mValue) : print_r($mValue))) ?></pre>
                </td>
            </tr>
            <?php
        }
        ?>

        <tr>
            <td class="debug_title">Backtrace:</td>
            <td class="debug_message">
                <?php
                $aDebugBacktrace = debug_backtrace();
                ?>
                <?php
                for ($i = 0; $i < count($aDebugBacktrace); $i++) {
                    ?>
                    <div><b>
                            <?= isset ($aDebugBacktrace [$i] ["class"]) ? $aDebugBacktrace [$i] ["class"] . "::" : "" ?>
                            <?= $aDebugBacktrace [$i] ["function"] . "()" ?>
                        </b>
                        <?= isset ($aDebugBacktrace [$i] ["file"]) ? $aDebugBacktrace [$i] ["file"] : "somefile.php" ?>
                        <b>
                            <?= "#" . (isset ($aDebugBacktrace [$i] ["line"]) ? $aDebugBacktrace [$i] ["line"] : "someline") ?>
                        </b></div>
                    <?php
                }
                ?>

            </td>
        </tr>

    </table>
    <br/>
    <br/>
<?php }

function dd($mValues)
{
    $iNumArgs = func_num_args();
    ?>
    <style>
        table.debug {
            width: 100%;
        }

        td.debug_title {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
            width: 10%;
            padding: 10pt;
        }

        td.debug_message {
            text-align: left;
            width: 90%;
            padding: 10pt;
        }

        td.debug_debug {
            text-align: left;
            font-weight: bold;
            padding: 10pt;
        }
    </style>
    
    <table border="1" bordercolor="#000000" cellpadding="0" cellspacing="0"
           width="100%">
        <tr>
            <td class="debug_debug" colspan="2">[ Debug: ]</td>
        </tr>
        <?php
        for ($i = 0; $i < $iNumArgs; $i++) {
            ?>
            <?php
            $mValue = func_get_arg($i) ?>
            <tr>
                <td class="debug_title">Debug[<?= $i ?>]:</td>
                <td class="debug_message"><i>(<?= ((is_object($mValue)) ? get_class($mValue) : gettype($mValue)) ?>)</i>
                    <pre><?php
                        ((is_null($mValue) || is_bool($mValue) ? var_dump($mValue) : print_r($mValue))) ?></pre>
                </td>
            </tr>
            <?php
        }
        ?>

        <tr>
            <td class="debug_title">Backtrace:</td>
            <td class="debug_message">
                <?php
                $aDebugBacktrace = debug_backtrace();
                ?>
                <?php
                for ($i = 0; $i < count($aDebugBacktrace); $i++) {
                    ?>
                    <div><b>
                            <?= isset ($aDebugBacktrace [$i] ["class"]) ? $aDebugBacktrace [$i] ["class"] . "::" : "" ?>
                            <?= $aDebugBacktrace [$i] ["function"] . "()" ?>
                        </b>
                        <?= isset ($aDebugBacktrace [$i] ["file"]) ? $aDebugBacktrace [$i] ["file"] : "somefile.php" ?>
                        <b>
                            <?= "#" . (isset ($aDebugBacktrace [$i] ["line"]) ? $aDebugBacktrace [$i] ["line"] : "someline") ?>
                        </b></div>
                    <?php
                }
                ?>

            </td>
        </tr>

    </table>
    <br/>
    <br/>
    <?php exit;
}
?>