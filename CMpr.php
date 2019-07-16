<?php

class CMpr
{
    static $margin = 20;

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

    private static function __printSimpleLine($value, $key, $isObject) {
        $sType = gettype($value);
        if($value === "NO DATA!!!") {
            $sType = "ERROR";
        }
        $sChars = "";
        $sColor = "";
        switch($sType) {
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
        echo '<span style="display:inline-table;color:' . $sColor . '">' . $value . '</span> <span style="opacity:0.5">(' . $sType . $sChars . ')</span>';
        if ($key) {
            echo '</div>';
        }

    }

    private static function __printArrayLine($value, $key, $isObject) {
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
                echo (is_object($value)) ? $key . '<span style="color:#c678dd;">' . get_class($value) . ' Object {' . count((array)$value) . '}</span>' : $key . '<span style="color:#e06c75">Array [' . count($value) . ']</span>';
            echo '</summary>';
            self::__printRec($value);
        echo '</details>';
        if ($key) {
            echo "</div>";
        }
    }
    
    private static function __print($arData, $key = '', $isObject = false) {
        if (is_object($arData) || is_array($arData)) {
            self::__printArrayLine($arData, $key, $isObject);
        } else {
            self::__printSimpleLine($arData, $key, $isObject);
        }
    }
    
    private static function __printRec($arData) {
        if (!is_object($arData) && !is_array($arData)) {
            return;
        }
        $arData = self::__clearKey($arData);

        $isObject = false;
        if (is_object($arData)) {
            $isObject = true;
        }

        echo '(';
        foreach ($arData as $key => $value) {
            self::__print($value, $key, $isObject);
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
            $arData = self::__clearKey($arArgs[0]);
        } else {
            $arData = self::__clearKey($arArgs[0]);
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
            self::__print($arData);
        echo "</pre>";
     
        echo "</div>";
     
        if ($bDie) {
            die();
        }
    }
}