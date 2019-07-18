<?php

class CMpr
{
    private static $margin = 20;

    public static function mpr() {  
        $nNumargs = func_num_args();
        $arArgs = func_get_args();
     
        $sTitle = '';
        $bDie = false;
        $bJS = false;
     
        if ($nNumargs < 1) {
            $arData = 'NO DATA!!!';
        } elseif ($nNumargs == 1) {
            $arData = self::__clearKey($arArgs[0]);
        } else {
            $arData = self::__clearKey($arArgs[0]);
            unset($arArgs[0]);
     
            $nDie = array_search('die', $arArgs, true);
            if ((boolean)$nDie > 0) {
                $bDie = (boolean)$arArgs[$nDie];
                unset($arArgs[$nDie]);
            }
     
            $nJS = array_search('js', $arArgs, true);
            if ((boolean)$nJS > 0) {
                $bJS = (boolean)$arArgs[$nJS];
                unset($arArgs[$nJS]);
            }
     
            $nTitle = array_search(true, $arArgs);
            $sTitle = (string)$arArgs[$nTitle];
        }
     
        $arDebug = debug_backtrace();
        $nLevel = (version_compare(PHP_VERSION, '7.0.0', '>=')) ? 1 : 2;
        $arDebug = $arDebug[$nLevel];
     
        if($bJS) {
            ?><script>console.log("<?= ($sTitle ? $sTitle . ' - ' : '') . str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug['file']) . ' [' . $arDebug['line'] . ']' ?>", <?= json_encode($arData) ?>);</script><?
            return;
        }
     
        self::__print($arData, $sTitle, $arDebug);
     
        if ($bDie) {
            die();
        }
    }

    private static function __clearKey($arData) {
        $arResult = $arData;
     
        if (is_array($arData)) {
            $arResult = array();
            foreach ($arData as $key => $val) {
                if (is_integer($key) || is_string($key) && $key[0] != '~') {
                    $arResult[$key] = self::__clearKey($val);
                }
            }
        }
        return $arResult;
    }

    private static function __print($arData, $sTitle, $arDebug) {
        echo '<div class="mpr" style="border:5px solid #DDD;background-color:#DDD;margin:15px 0;min-height:34px;clead:both;">';
        if (strlen($sTitle) > 0) {
            echo '<span style="padding:5px 10px 10px;float:right;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . $sTitle . '</span>';
        }
        echo '<span style="padding:5px 10px 10px;float:left;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug['file']) . ' [' . $arDebug['line'] . ']</span>';
            echo '<pre style="background:#282c34;color:#abb2bf;border:0;border-radius:0;margin:29px 0 0;font-family:monospace;font-size:13px;font-weight:400;max-height:500px;overflow:auto;clear:both;padding:5px;">';
                
                self::__printRow($arData);

            echo '</pre>';
        echo '</div>';
    }

    private static function __printRow($arData, $key = '', $isObject = false) {
        if (is_object($arData) || is_array($arData)) {
            self::__printArrayRow($arData, $key, $isObject);
        } else {
            self::__printSimpleRow($arData, $key, $isObject);
        }
    }
    
    private static function __printRowRec($arData) {
        if (!is_object($arData) && !is_array($arData)) {
            return;
        }
        $arData = self::__clearKey($arData);

        $isObject = is_object($arData);

        echo '(';
        foreach ($arData as $key => $val) {
            self::__printRow($val, $key, $isObject);
        }
        if (count($arData) == 0) {
            echo '<br>';
        }
        echo ')';
    }

    private static function __printArrayRow($arData, $key, $isObject) {
        $margin = '';
        if ($key) {
            if ($isObject) {
                $key = $key . ' : ';
            } else {
                $key = '[' . $key . '] => ';
            }
            $margin = ' style="margin-left:' . self::$margin . 'px"';
            echo '<div>';
        }
        echo '<details open' . $margin . '>';
            echo '<summary style="outline:none!important;cursor:pointer">';
                echo (is_object($arData)) ? $key . '<span style="color:#c678dd;">' . get_class($arData) . ' Object {' . count((array)$arData) . '}</span>' : $key . '<span style="color:#e06c75">Array [' . count($arData) . ']</span>';
            echo '</summary>';

            self::__printRowRec($arData);
            
        echo '</details>';
        if ($key) {
            echo '</div>';
        }
    }

    private static function __printSimpleRow($arData, $key, $isObject) {
        $sType = gettype($arData);
        if($arData === 'NO DATA!!!') {
            $sType = 'ERROR';
        }
        $sChars = '';
        $sColor = '';
        switch($sType) {
            case 'string':
                $sColor = '#61afef';
                $arData = str_replace(chr(13), '', $arData); //del symbol CR
                $arData = str_replace(chr(10), '', $arData); //del symbol LF
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

        if ($key) {
            if ($isObject) {
                echo '<div style="margin-left:' . self::$margin . 'px"><span>' . $key . '</span> : ';
            } else {
                echo '<div style="margin-left:' . self::$margin . 'px"><span>[' . $key . ']</span> => ';
            }
        }
        echo '<span style="display:inline-table;color:' . $sColor . '">' . $arData . '</span> <span style="opacity:0.5">(' . $sType . $sChars . ')</span>';
        if ($key) {
            echo '</div>';
        }

    }
}