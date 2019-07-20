<?php

class CMpr
{
    /**
     * Left margin for child items
     *
     * @var integer
     */
    private static $margin = 20;

    /**
     * Check object for child items
     *
     * @var boolean
     */
    private static $isObject = false;

    /**
     * Basic function
     *
     * @return void
     */
    public static function mpr() {  
        $nNumargs = func_num_args();
        $arArgs   = func_get_args();
     
        self::$isObject = $sTitle = $bDie = $bJS = false;
     
        if ($nNumargs == 0) {
            $arData = 'NO DATA!!!';
        } else {
            $arData = self::__clearKey($arArgs[0]);
            unset($arArgs[0]);

            if ($nNumargs == 1) {
                goto ifend;
            }
    
            $bDie = __setAttribute('die', $arArgs);
            $bJS  = __setAttribute('js', $arArgs);
            
            $arArgs = array_values($arArgs);
            $sTitle = $arArgs[0];
        }

        ifend:
     
        $nLevel = (version_compare(PHP_VERSION, '7.0.0', '>=')) ? 1 : 2;
        $arDebug = debug_backtrace()[$nLevel];
        $sDebug = str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug['file']) . ' [' . $arDebug['line'] . ']';
     
        if($bJS) {
            ?><script>console.log("<?= ($sTitle ? $sTitle . ' - ' : '') . str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug['file']) . ' [' . $arDebug['line'] . ']' ?>", <?= json_encode($arData) ?>);</script><?
            return;
        }
     
        self::__print($arData, $sTitle, $sDebug);
     
        if ($bDie) {
            die();
        }
    }
    
    /**
     * Set value to attribute in array
     *
     * @param string $sArg
     * @param array $arArgs
     * @return boolean $bArg
     */
    private static function __setAttribute($sArg, &$arArgs) {
        $bArg = false;
        $nArg = array_search($sArg, $arArgs, true);
        if ((integer)$nArg > 0) {
            $bArg = (boolean)$arArgs[$nArg];
            unset($arArgs[$nArg]);
        }
        
        return $bArg;
    }

    /**
     * Delete cache items into array
     *
     * @param array $arData
     * @return array $arResult
     */
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

    /**
     * Print all result
     *
     * @param array $arData
     * @param string $sTitle
     * @param string $sDebug
     * @return void
     */
    private static function __print($arData, $sTitle, $sDebug) {
        echo '<div class="mpr" style="line-height:1.5;border:5px solid #DDD;background-color:#DDD;margin:15px auto;min-height:34px;clear:both;max-width:1000px;position:sticky;z-index:9999;">';
        if (strlen($sTitle) > 0) {
            echo '<span style="padding:5px 10px 10px;float:right;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . $sTitle . '</span>';
        }
        echo '<span style="padding:5px 10px 10px;float:left;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . $sDebug . '</span>';
            echo '<pre style="background:#282c34;color:#abb2bf;border:0;border-radius:0;margin:29px 0 0;font-family:monospace;font-size:13px;font-weight:400;max-height:500px;overflow:auto;clear:both;padding:5px;">';
                
                self::__printRow($arData);

            echo '</pre>';
        echo '</div>';
    }

    /**
     * Print one row to result
     *
     * @param array $arData
     * @param string $key
     * @return void
     */
    private static function __printRow($arData, $key = '') {
        if (is_object($arData) || is_array($arData)) {
            self::__printArrayRow($arData, $key);
        } else {
            self::__printSimpleRow($arData, $key);
        }
    }
    
    /**
     * Recursive print all rows
     *
     * @param array $arData
     * @return void
     */
    private static function __printRowRec($arData) {
        if (!is_object($arData) && !is_array($arData)) {
            return;
        }
        $arData = self::__clearKey($arData);

        self::$isObject = is_object($arData);

        echo '(';
        foreach ($arData as $key => $val) {
            self::__printRow($val, $key);
        }
        if (count($arData) == 0) {
            echo '<br>';
        }
        echo ')';
    }

    /**
     * Print array and object
     *
     * @param array $arData
     * @param string $key
     * @return void
     */
    private static function __printArrayRow($arData, $key) {
        $sMargin = '';
        if ($key !== '') {
            if (self::$isObject) {
                $key = $key . ' : ';
            } else {
                $key = '[' . $key . '] => ';
            }
            $sMargin = ' style="margin-left:' . self::$margin . 'px"';
            echo '<div>';
        }

        if (count((array)$arData) > 0) {
            echo '<details open' . $sMargin . '>';
            echo '<summary style="outline:none!important;cursor:pointer">';
        } else {
            echo '<div style="margin-left:' . self::$margin . 'px">';
        }

        echo (is_object($arData)) ? $key . '<span style="color:#c678dd;">' . get_class($arData) . ' Object {' . count((array)$arData) . '}</span>' : $key . '<span style="color:#e06c75">Array [' . count($arData) . ']</span>';
        
        if (count((array)$arData) > 0) {
            echo '</summary>';
            self::__printRowRec($arData);   
            echo '</details>';
        } else {
            echo "</div>";
        }
        if ($key !== '') {
            echo '</div>';
        }
    }

    /**
     * Print simple item
     *
     * @param array $arData
     * @param string $key
     * @return void
     */
    private static function __printSimpleRow($arData, $key) {
        $sType = gettype($arData);
        if($arData === 'NO DATA!!!') {
            $sType = 'ERROR';
        }
        $sChars = $sColor = '';
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

        if ($key !== '') {
            if (self::$isObject) {
                echo '<div style="margin-left:' . self::$margin . 'px"><span>' . $key . '</span> : ';
            } else {
                echo '<div style="margin-left:' . self::$margin . 'px"><span>[' . $key . ']</span> => ';
            }
        }
        echo '<span style="display:inline-table;color:' . $sColor . '">' . $arData . '</span> <span style="opacity:0.5">(' . $sType . $sChars . ')</span>';
        if ($key !== '') {
            echo '</div>';
        }

    }
}
