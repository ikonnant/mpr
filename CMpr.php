<?php
class CMpr
{
    /**
     * Singleton object
     *
     * @var object
     */
    protected static $instance;

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
    private $nMargin = 20;

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
     * Not print class parameters in object listing
     *
     * @var boolean
     */
    private $noClassParameters = false;

    /**
     * Test
     *
     * @var boolean
     */
    private $isTest = false;

    private function __construct() {
    }

    private function __clone() {
    }

    private function __wakeup() {
    }

    /**
     * Void singleton object
     *
     * @return object self::$instance
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Set noClear to true
     *
     * @return void
     */
    public function noClear() {
        $this->noClear = true;
    }

    /**
     * Set noClassParameters to true
     *
     * @return void
     */
    public function noClassParameters() {
        $this->noClassParameters = true;
    }

    /**
     * Set isTest to true
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
            $this->nMargin = $nMargin;
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

        $arDebug = debug_backtrace()[1] ?? debug_backtrace()[0];
        $sDebug  = str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug['file']) . ' [' . $arDebug['line'] . ']';

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
            $arResult = [];
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
     * @param boolean $isObject
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
            $obObject = $arData;
            $arData = [];
            foreach (array_keys(get_object_vars($obObject)) as $obKey) {
                $arData[$obKey] = $obObject->$obKey;
            }

            if (!$this->noClassParameters) {
                $this->printClassParameters($obObject);
            }
        }

        echo '(';
        foreach ($arData as $key => $val) {
            $this->printRow($val, $key, $isObject);
        }
        if (count($arData) == 0) {
            echo '<br>';
        }
        echo ')';
    }

    /**
     * Print all class parameters
     *
     * @param object $obObject
     * @return void
     */
    private function printClassParameters($obObject) {
        $obReflection = new ReflectionClass($obObject);
        $arMethods = $obReflection->getMethods();
        $arProperties = $obReflection->getProperties();

        echo '<details style="position:relative;margin-left:' . $this->nMargin . 'px">';
            echo '<summary style="outline:none!important;cursor:pointer;opacity:0.5;display:inline-block;position: absolute;">parameters {' . count($arMethods) . '}</summary>';
            echo '<br>{';
            foreach ($obReflection->getProperties() as $obProperty) {
                $sType     = '';
                if ($obProperty->isPrivate()) {
                    $sType = 'private';
                } elseif ($obProperty->isProtected()) {
                    $sType = 'protected';
                } elseif ($obProperty->isPublic()) {
                    $sType = 'public';
                }
                $sComment  = str_replace('    ', '', $obProperty->getDocComment());
                $sName     = $obProperty->getName();
                $sValue    = $obReflection->getDefaultProperties()[$sName];
                $arType    = $this->getColorByType($sValue);

                echo '<div style="margin-left:' . $this->nMargin . 'px;margin-bottom:20px;">';
                    echo '<div style="opacity:0.5;">' . $sComment . '</div>';
                    echo '<div>Var [ <span style="color:#c678dd;">' . $sType . '</span> var <span style="color:#61afef;">' . $sName . '</span> = <span style="color:' . $arType['COLOR'] . '">' . $sValue . '</span> <span style="opacity:0.5">(' . $arType['TYPE'] . $arType['CHARS'] . ')</span> ]</div>';
                echo '</div>';
            }
            foreach($arMethods as $sMethod) {
                $sMethod = strip_tags($sMethod);
                preg_match("/\/\*\*([\s\S]*?)\*\//", $sMethod, $arComment);
                preg_match("/Method \[ ([\s\S]*?) method/", $sMethod, $arType);
                preg_match("/@@ (.*)/", $sMethod, $arDebug);
                preg_match("/method (.*?) ]/", $sMethod, $arName);
                preg_match_all("/Parameter.*? \[ (.*) ]/", $sMethod, $arParameters);

                $sComment     = str_replace('    ', '', $arComment[0]);
                $sType        = trim($arType[1]);
                $sDebug       = str_replace($_SERVER['DOCUMENT_ROOT'], '', $arDebug[1]) ?? 'This is internal (built-in) PHP functions';
                $sName        = $arName[1];
                $arParameters = $arParameters[1];

                echo '<div style="margin-left:' . $this->nMargin . 'px;margin-bottom:20px;">';
                    echo '<div style="opacity:0.5;">' . $sComment . '</div>';
                    echo '<div>Method [ <span style="color:#c678dd;">' . $sType . '</span> method <span style="color:#61afef;">' . $sName . '</span> ] {</div>';
                    echo '<div style="margin-left:' . $this->nMargin . 'px;">@@ ' . $sDebug . '</div>';

                    if (count($arParameters)) {
                        echo '<br><div style="margin-left:' . $this->nMargin . 'px;">- Parameters [' . count($arParameters) . '] {';
                            foreach ($arParameters as $num => $sParamater) {
                                echo '<div style="margin-left:' . $this->nMargin . 'px;">Parameter #' . $num . ' [ ' . trim($sParamater) . ' ]</div>';
                            }
                        echo '}</div>';
                    }
                    echo '}';
                echo '</div>';
            }
            echo '}';
        echo '</details>';
        echo '<div style="clear:both;"></div>';
    }

    /**
     * Print array and object
     *
     * @param array $arData
     * @param string $key
     * @param boolean $isObject
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
            $sMargin = ' style="margin-left:' . $this->nMargin . 'px;"';
            echo '<div>';
        }
        if (count((array)$arData) > 0) {
            echo '<details open' . $sMargin . '>';
            echo '<summary style="outline:none!important;cursor:pointer;display:inline-block;">';
        } else {
            echo '<div style="margin-left:' . $this->nMargin . 'px">';
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
     * @param boolean $isObject
     * @return void
     */
    private function printSimpleRow($arData, $key, $isObject) {
        $arType = $this->getColorByType($arData);

        if ($key !== '') {
            if ($isObject) {
                echo '<div style="margin-left:' . $this->nMargin . 'px"><span>' . $key . '</span> : ';
            } else {
                echo '<div style="margin-left:' . $this->nMargin . 'px"><span>[' . $key . ']</span> => ';
            }
        }
        echo '<span style="display:inline-table;color:' . $arType['COLOR'] . '">' . $arData . '</span> <span style="opacity:0.5">(' . $arType['TYPE'] . $arType['CHARS'] . ')</span>';
        if ($key !== '') {
            echo '</div>';
        }
    }

    /**
     * Get color by type value
     *
     * @param array $arData
     * @return array $arResult
     */
    private function getColorByType(&$arData) {
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
                if ($arData == '') {
                    $arData =  "''";
                }
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

        $arResult = [
            'COLOR' => $sColor,
            'CHARS' => $sChars,
            'TYPE'  => $sType
        ];

        return $arResult;
    }
}