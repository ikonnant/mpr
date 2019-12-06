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
    private $noClassParameters = true;

    /**
     * Test
     *
     * @var boolean
     */
    private $isTest = false;

    /**
     * Color sheme
     *
     * @var array
     */
    private $arColorSheme = [
        'title_background' => '#DDD',
        'title_text'       => '#000',
        'body_background'  => '#282c34',
        'body_text'        => '#abb2bf',
        'array'            => '#e06c75',
        'object'           => '#c678dd',
        'class_method'     => '#61afef',
        'class_var'        => '#61afef',
        'error'            => '#e06c75',
        'string'           => '#61afef',
        'integer'          => '#98c379',
        'double'           => '#98c379',
        'boolean'          => '#d19a66',
        'NULL'             => '#d19a66',
    ];

    private function __construct() {
    }

    private function __clone() {
    }

    private function __wakeup() {
    }

    /**
     * Void singleton object
     *
     * @param array $arArgs
     *
     * @return object self::$instance
     */
    public static function getInstance($arArgs = []) {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        if ($arArgs) {
            self::$instance->setArgs($arArgs);
        }
        return self::$instance;
    }

    /**
     * Set noClear to true
     *
     * @return object
     */
    public function noClear() {
        $this->noClear = true;

        return self::$instance;
    }

    /**
     * Set noClassParameters to true
     *
     * @return object
     */
    public function showClassParameters() {
        $this->noClassParameters = false;

        return self::$instance;
    }

    /**
     * Set isTest to true
     *
     * @return object
     */
    public function isTest() {
        $this->isTest = true;

        return self::$instance;
    }

    /**
     * Set arguments
     *
     * @param array $arArgs
     *
     * @return object
     */
    public function setArgs($arArgs) {
        $this->arArgs = $arArgs;

        return self::$instance;
    }

    /**
     * get color sheme
     *
     * @param string $sType
     *
     * @return array $arColorSheme
     */
    public function getColorSheme($sType = '') {
        if (array_key_exists($sType, $this->arColorSheme)) {
            return [$sType => $this->arColorSheme[$sType]];
        } else {
            return $this->arColorSheme;
        }
    }

    /**
     * Set color sheme
     *
     * @param array $arColors
     *
     * @return object
     */
    public function setColorSheme($arColors) {
        foreach ($this->arColorSheme as $sType => $sColor) {
            if ((string)$arColors[$sType]) {
                $this->arColorSheme[$sType] = (string)$arColors[$sType];
            }
        }

        return self::$instance;
    }

    /**
     * Set margin
     *
     * @param integer $nMargin
     *
     * @return object
     */
    public function setMargin($nMargin) {
        if ((integer)$nMargin > 0) {
            $this->nMargin = (integer)$nMargin;
        }

        return self::$instance;
    }

    /**
     * Basic function
     *
     * @return void
     * @throws ReflectionException
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
     *
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
     *
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
     * @param array  $arData
     * @param string $sDebug
     *
     * @return void
     * @throws ReflectionException
     */
    private function printAll($arData, $sDebug) {
        if ($this->bJS) {
            ?><script>
                console.log(
                    "<?= ($this->sTitle ? $this->sTitle . ' - ' : '') . $sDebug ?>",
                    <?= json_encode($arData) ?>
                );
            </script><?
            return;
        }

        echo '<div class="mpr" style="color:' . $this->arColorSheme['title_text'] . ';border:5px solid ' . $this->arColorSheme['title_background'] . ';background-color:' . $this->arColorSheme['title_background'] . ';margin:15px auto;min-height:34px;clear:both;max-width:1000px;position:sticky;z-index:9999;">';
        if (strlen($this->sTitle) > 0) {
            echo '<span style="color:' . $this->arColorSheme['title_text'] . ';padding:5px 10px 10px;float:right;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . $this->sTitle . '</span>';
        }
        echo '<span style="padding:5px 10px 10px;float:left;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;">' . $sDebug . '</span>';
        echo '<pre style="line-height:1.5;background:' . $this->arColorSheme['body_background'] . ';color:' . $this->arColorSheme['body_text'] . ';border:0;border-radius:0;margin:29px 0 0;font-family:monospace;font-size:13px;font-weight:400;max-height:500px;overflow:auto;clear:both;padding:5px 8px;">';

        $this->printRow($arData);

        echo '</pre>';
        if ($this->bDie) {
            echo '<span style="clear:both;max-width:1000px;position:sticky;z-index:9999;display:block;background-color:' . $this->arColorSheme['body_background'] . ';line-height:3;text-align:center;color:' . $this->arColorSheme['error'] . ';"><hr style="position:absolute;left:4%;right:55%;margin:0;top:50%;border-color:' . $this->arColorSheme['error'] . ';">DIE<hr style="position:absolute;right:4%;left:55%;margin:0;top:50%;border-color:' . $this->arColorSheme['error'] . ';"></span>';
        }
        echo '</div>';

        if ($this->bDie) {
            die();
        }
    }

    /**
     * Print one row to result
     *
     * @param array   $arData
     * @param string  $key
     * @param boolean $isObject
     *
     * @return void
     * @throws ReflectionException
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
     *
     * @return void
     * @throws ReflectionException
     */
    private function printRowRec($arData) {
        if (!is_object($arData) && !is_array($arData)) {
            return;
        }
        $arData = $this->clearKey($arData);

        $isObject = is_object($arData);
        if ($isObject) { //fix vars value for object to array
            $obObject = $arData;
            $arData   = [];
            foreach (array_keys(get_object_vars((object)$obObject)) as $obKey) {
                $arData[$obKey] = $obObject->$obKey;
            }

            if (!$this->noClassParameters) {
                $this->printClassParameters((object)$obObject);
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
     *
     * @return void
     * @throws ReflectionException
     */
    private function printClassParameters($obObject) {
        $obReflection = new ReflectionClass($obObject);
        $arMethods    = $obReflection->getMethods();
        $arProperties = $obReflection->getProperties();

        if (count($arMethods) == 0) {
            echo '<div style="outline:none!important;opacity:0.5;display:inline-block;position:absolute;left:28px;">parameters {' . count($arMethods) . '}</div>';
            return;
        }
        echo '<details style="position:relative;margin-left:' . $this->nMargin . 'px">';
        echo '<summary style="outline:none!important;cursor:pointer;opacity:0.5;display:inline-block;position:absolute;">parameters {' . count($arMethods) . '}</summary>';
        echo '<br>{';
        foreach ($arProperties as $obProperty) {
            $sType = '';
            if ($obProperty->isPrivate()) {
                $sType = 'private';
            } elseif ($obProperty->isProtected()) {
                $sType = 'protected';
            } elseif ($obProperty->isPublic()) {
                $sType = 'public';
            }
            $sComment = str_replace('    ', '', $obProperty->getDocComment());
            $sName    = $obProperty->getName();
            $sValue   = $obReflection->getDefaultProperties()[$sName];
            $arType   = $this->getColorByType($sValue);

            echo '<div style="margin-left:' . $this->nMargin . 'px;margin-bottom:20px;">';
            echo '<div style="opacity:0.5;">' . $sComment . '</div>';
            echo '<div>Var [ <span style="color:' . $this->arColorSheme['object'] . ';">' . $sType . '</span> var <span style="color:' . $this->arColorSheme['class_var'] . ';">' . $sName . '</span> = <span style="color:' . $arType['COLOR'] . '">' . $sValue . '</span> <span style="opacity:0.5">(' . $arType['TYPE'] . $arType['CHARS'] . ')</span> ]</div>';
            echo '</div>';
        }
        foreach ($arMethods as $sMethod) {
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
            echo '<div>Method [ <span style="color:' . $this->arColorSheme['object'] . ';">' . $sType . '</span> method <span style="color:' . $this->arColorSheme['class_method'] . ';">' . $sName . '</span> ] {</div>';
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
     * @param array   $arData
     * @param string  $key
     * @param boolean $isObject
     *
     * @return void
     * @throws ReflectionException
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

        if (is_object($arData)) {
            echo $key . '<span style="color:' . $this->arColorSheme['object'] . ';">' . get_class($arData) . ' Object {' . count((array)$arData) . '}</span>';
        } else {
            echo $key . '<span style="color:' . $this->arColorSheme['array'] . '">Array [' . count($arData) . ']</span>';
        }

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
     * @param array   $arData
     * @param string  $key
     * @param boolean $isObject
     *
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
     *
     * @return array $arResult
     */
    private function getColorByType(&$arData) {
        $sType = gettype($arData);
        if ($arData === 'NO DATA!!!') {
            $sType = 'error';
        }
        $sColor = $this->arColorSheme[$sType];
        $sChars = '';
        switch ($sType) {
            case 'string':
                $arData = str_replace(chr(13), '', $arData); //del symbol CR
                $arData = str_replace(chr(10), '', $arData); //del symbol LF
                $sChars = ' <small>' . iconv_strlen($arData) . '</small>';
                if ($arData == '') {
                    $arData = "''";
                }
                break;
            case 'boolean':
                $arData = $arData ? 'TRUE' : 'FALSE';
                break;
            case 'NULL':
                $arData = 'NULL';
                break;
            case 'double':
            case 'integer':
            case 'error':
            default:
                break;
        }

        return [
            'COLOR' => $sColor,
            'CHARS' => $sChars,
            'TYPE'  => $sType,
        ];
    }
}