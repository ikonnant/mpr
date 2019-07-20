<?php
//class autoload php
spl_autoload_register(function ($className) {
    include $className . '.php';
});

CModule::AddAutoloadClasses(
    '',
    ['CMpr' => '/local/php_interface/CMpr.php']
);

//function short class call
function mpr() {
    $mpr = new CMpr;
    $mpr->setArgs(func_get_args());
    //$mpr->isTest();
    //$mpr->noClear();
    //$mpr->setMargin(10);
    $mpr->init();
}
