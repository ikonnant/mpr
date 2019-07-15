<?php

class CMpr
{
    private static function __mprClearKey($arData) {
        $arResult = $arData;
     
        if (is_array($arData)) {
            $arResult = array();
            foreach ($arData as $key => $val) {
                if (is_integer($key) || is_string($key) && $key[0] != '~') {
                    $arResult[$key] = self::__mprClearKey($val);
                }
            }
        }
        return $arResult;
    }
    
    private static function __mprPrint($arData) {
        if (is_object($arData) || is_array($arData)) {
            echo '<div>';
                echo '<details open>';
                    echo '<summary style="outline:none!important;cursor:pointer">';
                        echo (is_object($arData)) ? '<span style="color:#c678dd;">' . get_class($arData) . ' Object {' . count((array)$arData) . '}</span>' : '<span style="color:#e06c75">Array [' . count($arData) . ']</span>';
                    echo '</summary>';
                    self::__mprPrintRec($arData);
                echo '</details>';
            echo '</div>';
        } else {
            $sType = gettype($arData);
            if($arData === "NO DATA!!!") {
                $sType = "ERROR";
            }
            $sChars = "";
            $sColor = "";
            switch($sType) {
                case 'string':
                    $sColor = '#61afef';
                    $arData = str_replace(chr(13), '', $arData);
                    $arData = str_replace(chr(10), '', $arData);
                    $sChars = ' <small>' . iconv_strlen($arData) . '</small>';
                break;
                case 'integer':
                    $sColor = '#98c379';
                break;
                case 'double':
                    $sColor = '#98c379';
                break;
                case 'boolean':
                    $sColor = '#d19a66';
                    $arData = $arData ? 'TRUE' : 'FALSE';
                break;
                case 'NULL':
                    $sColor = '#d19a66';
                    $arData = 'NULL';
                break;
                case 'ERROR':
                    $sColor = '#e06c75';
                break;
            }
            echo '<span style="color:' . $sColor . '">' . $arData . '</span> <span style="opacity:0.5">(' . $sType . $sChars . ')</span>';
        }
    }
    
    private static function __mprPrintRec($arData, $margin = 20){
        if (!is_object($arData) && !is_array($arData)) {
            return;
        }
        $arData = self::__mprClearKey($arData);
     
        echo '(';
        foreach ($arData as $key => $value) {
            if (is_object($value) || is_array($value)) {
                echo '<details open style="margin-left:' . $margin . 'px">';
                    echo '<summary style="outline:none!important;cursor:pointer">';
                        echo (is_object($value)) ? '[' . $key . '] => <span style="color: #c678dd;">' . get_class($value) . ' Object {' . count((array)$value) . '}' : '[' . $key . ']</span> => <span style="color: #e06c75">Array [' . count($value) . ']</span>';
                    echo '</summary>';
                    self::__mprPrintRec($value, $margin + 10);
                echo '</details>';
            } else {
                $sType = gettype($value);
                $sChars = "";
                $sColor = "";
                switch ($sType) {
                    case 'string':
                        $sColor = '#61afef';
                        $value = str_replace(chr(13), '', $value);
                        $value = str_replace(chr(10), '', $value);
                        $sChars = ' <small>' . iconv_strlen($value) . '</small>';
                    break;
                    case 'integer':
                        $sColor = '#98c379';
                    break;
                    case 'double':
                        $sColor = '#98c379';
                    break;
                    case 'boolean':
                        $sColor = '#d19a66';
                        $value = $value ? 'TRUE' : 'FALSE';
                    break;
                    case 'NULL':
                        $sColor = '#d19a66';
                        $value = 'NULL';
                    break;
                }
                echo '<div style="margin-left:' . $margin . 'px"><span>[' . $key . ']</span> => <span style="display:inline-table;color:' . $sColor . '">' . $value . '</span> <span style="opacity: 0.5">(' . $sType . $sChars . ')</span></div>';
            }
        }
        if (count($arData) == 0) {
            echo '<br>';
        }
        echo ')';
    }
    
    public static function mpr() {  
        $nNumargs = func_num_args();
        $arArgs = func_get_args();
     
        $sTitle = "";
        $bDie = false;
        $bJS = false;
     
        if ($nNumargs < 1) {
            $arData = "NO DATA!!!";
        } elseif ($nNumargs == 1) {
            $arData = self::__mprClearKey($arArgs[0]);
        } else {
            $arData = self::__mprClearKey($arArgs[0]);
            unset($arArgs[0]);
     
            $nDie = array_search("die", $arArgs, true);
            if ((boolean)$nDie > 0) {
                $bDie = (boolean)$arArgs[$nDie];
                unset($arArgs[$nDie]);
            }
     
            $nJS = array_search("js", $arArgs, true);
            if ((boolean)$nJS > 0) {
                $bJS = (boolean)$arArgs[$nJS];
                unset($arArgs[$nJS]);
            }
     
            $nTitle = array_search(true, $arArgs);
            $sTitle = (string)$arArgs[$nTitle];
        }
     
        $arDebug = debug_backtrace();

        $level = 2;
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $level --;
        }
        $arDebug = $arDebug[$level];
     
        if($bJS) {
            ?>
                <script>console.log("<?=($sTitle ? $sTitle . " - " : '') . str_replace($_SERVER["DOCUMENT_ROOT"], '', $arDebug['file']) . " [" . $arDebug['line'] . "]";?>", <?=json_encode($arData);?>);</script>
            <?
            return;
        }
     
        echo "<div class='mpr' style='border:5px solid #DDD;background-color:#DDD;margin:15px 0;min-height:34px;'>";
        if (strlen($sTitle) > 0) {
            echo "<span style='padding:5px 10px 10px;float:right;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;'>" . $sTitle . "</span>";
        }
     
        echo "<span style='padding:5px 10px 10px;float:left;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;'>" . str_replace($_SERVER["DOCUMENT_ROOT"], '', $arDebug['file']) . " [" . $arDebug['line'] . "]</span>";
     
        echo "<pre style='background:#282c34;color:#abb2bf;border:0;border-radius:0;margin:29px 0 0;font-family:monospace;font-size:13px;font-weight:400;max-height:500px;overflow:auto;clear:both;padding:5px;'>";
            self::__mprPrint($arData);
        echo "</pre>";
     
        echo "</div>";
     
        if ($bDie) {
            die();
        }
    }
}