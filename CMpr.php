<?php
class CMpr
{
    /**
     * Arguments in object
     *
     * @var array
     */
    private $arArgs;

    /**
     * Left margin for child items
     *
     * @var integer
     */
    private $margin = 20;

    /**
     * Title mpr print
     *
     * @var string
     */
    private $sTitle = '';

    /**
     * Die before print
     *
     * @var boolean
     */
    private $bDie = false;

    /**
     * Print to js console log
     *
     * @var boolean
     */
    private $bJS = false;

    /**
     * Not clear cache parameters in array
     *
     * @var boolean
     */
    private $noClear = false;

    /**
     * Test
     *
     * @var boolean
     */
    private $isTest = false;

    /**
     * Set noClear to true
     *
     * @return void
     */
    public function noClear() {
        $this->noClear = true;
    }

    /**
     * Set test
     *
     * @return void
     */
    public function isTest() {
        $this->isTest = true;
    }

    /**
     * Set arguments
     *
     * @param array $arArgs
     * @return void
     */
    public function setArgs($arArgs) {
        $this->arArgs = $arArgs;
    }

    /**
     * Set margin
     *
     * @param integer $nMargin
     * @return void
     */
    public function setMargin($nMargin) {
        if ((integer)$nMargin > 0) {
            $this->margin = $nMargin;
        }
    }

    /**
     * Basic function
     *
     * @return void
     */
    public function init() {

        if ($this->isTest && !isset($_GET['test'])) {
            return;
        }

        $arData = 'NO DATA!!!';

        if (count($this->arArgs) > 0) {
            $arData = $this->clearKey($this->arArgs[0]);

            if (count($this->arArgs) > 1) {
                unset($this->arArgs[0]);
                $this->bDie = $this->setAttribute('die');
                $this->bJS  = $this->setAttribute('js');

                $this->arArgs = array_values($this->arArgs);
                if (count($this->arArgs) > 0) { //if before unset into setAttribute(), arguments is not left
                    $this->sTitle = $this->arArgs[0];
                }
            }
        }

        //$arDebug = debug_backtrace()[1] ?? debug_backtrace()[0];
        if (isset(debug_backtrace()[1])) {
            $arDebug = debug_backtrace()[1];
        } else {
            $arDebug = debug_backtrace()[0];
        }

        $sDebug = str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug['file']) . ' [' . $arDebug['line'] . ']';

        $this->printAll($arData, $sDebug);
    }

    /**
     * Set value to attribute in array and unset it into $arArgs
     *
     * @param string $sArg
     * @return boolean $bArg
     */
    private function setAttribute($sArg) {
        $bArg = false;
        $nArg = array_search($sArg, $this->arArgs, true);
        if ((integer)$nArg > 0) {
            $bArg = (boolean)$this->arArgs[$nArg];
            unset($this->arArgs[$nArg]);
        }

        return $bArg;
    }

    /**
     * Delete cache items into array
     *
     * @param array $arData
     * @return array $arResult
     */
    private function clearKey($arData) {
        $arResult = $arData;

        if (is_array($arData) && !$this->noClear) {
            $arResult = array();
            foreach ($arData as $key => $val) {
                if (is_integer($key) || is_string($key) && $key[0] != '~') {
                    $arResult[$key] = $this->clearKey($val);
                }
            }
        }
        return $arResult;
    }

    /**
     * Print all result
     *
     * @param array $arData
     * @param string $sDebug
     * @return void
     */
    private function printAll($arData, $sDebug) {
        if($this->bJS) {
            ?><script>
                console.log(
                    "<?= ($this->sTitle ? $this->sTitle . ' - ' : '') . str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug['file']) . ' [' . $arDebug['line'] . ']' ?>",
                    <?= json_encode($arData) ?>
                );
            </script><?
            return;
        }

        echo '<div class="mpr" style="border:5px solid #DDD;background-color:#DDD;margin:15px auto;min-height:34px;clear:both;max-width:1000px;position:sticky;z-index:9999;">';
        if (strlen($this->sTitle) > 0) {
            echo '<span style="padding:5px 10px 10px;float:right;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . $this->sTitle . '</span>';
        }
        echo '<span style="padding:5px 10px 10px;float:left;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . $sDebug . '</span>';
            echo '<pre style="line-height:1.5;background:#282c34;color:#abb2bf;border:0;border-radius:0;margin:29px 0 0;font-family:monospace;font-size:13px;font-weight:400;max-height:500px;overflow:auto;clear:both;padding:5px 8px;">';

                $this->printRow($arData);

            echo '</pre>';
        echo '</div>';

        if ($this->bDie) {
            die('<span style="margin:-20px auto 20px;clear:both;max-width:1000px;position:sticky;z-index:9999;display:block;border:5px solid #DDD;border-top:0px;background-color:#282c34;line-height:3;text-align:center;color:#e06c75;"><hr style="position:absolute;left:4%;right:55%;margin:0;top:50%;border-color:#e06c75;">DIE<hr style="position:absolute;right:4%;left:55%;margin:0;top:50%;border-color:#e06c75;"></span>');
        }
    }

    /**
     * Print one row to result
     *
     * @param array $arData
     * @param string $key
     * @return void
     */
    private function printRow($arData, $key = '', $isObject = false) {
        if (is_object($arData) || is_array($arData)) {
            $this->printArrayRow($arData, $key, $isObject);
        } else {
            $this->printSimpleRow($arData, $key, $isObject);
        }
    }

    /**
     * Recursive print all rows
     *
     * @param array $arData
     * @return void
     */
    private function printRowRec($arData) {
        if (!is_object($arData) && !is_array($arData)) {
            return;
        }
        $arData = $this->clearKey($arData);

        $isObject = is_object($arData);
        if ($isObject) { //fix vars value for object to array
            $arObject = $arData;
            $arData = (array)$arData;
            foreach (array_keys(get_object_vars($arObject)) as $obKey) {
                $arData[$obKey] = $arObject->$obKey;
            }
        }

        echo '(';
        //array_unshift($arData, get_class_methods($arObject));
        foreach ($arData as $key => $val) {
            $this->printRow($val, $key, $isObject);
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
    private function printArrayRow($arData, $key, $isObject) {
        $sMargin = '';
        if ($key !== '') {
            if ($isObject) {
                $key = $key . ' : ';
            } else {
                $key = '[' . $key . '] => ';
            }
            $sMargin = ' style="margin-left:' . $this->margin . 'px"';
            echo '<div>';
        }
        if (count((array)$arData) > 0) {
            echo '<details open' . $sMargin . '>';
            echo '<summary style="outline:none!important;cursor:pointer">';
        } else {
            echo '<div style="margin-left:' . $this->margin . 'px">';
        }
        echo (is_object($arData)) ? $key . '<span style="color:#c678dd;">' . get_class($arData) . ' Object {' . count((array)$arData) . '}</span>' : $key . '<span style="color:#e06c75">Array [' . count($arData) . ']</span>';

        if (count((array)$arData) > 0) {
            echo '</summary>';
            $this->printRowRec($arData);
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
    private function printSimpleRow($arData, $key, $isObject) {
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
            if ($isObject) {
                echo '<div style="margin-left:' . $this->margin . 'px"><span>' . $key . '</span> : ';
            } else {
                echo '<div style="margin-left:' . $this->margin . 'px"><span>[' . $key . ']</span> => ';
            }
        }
        echo '<span style="display:inline-table;color:' . $sColor . '">' . $arData . '</span> <span style="opacity:0.5">(' . $sType . $sChars . ')</span>';
        if ($key !== '') {
            echo '</div>';
        }
    }
}
