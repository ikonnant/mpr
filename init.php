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
    CMpr::getInstance(func_get_args())
//    ->isTest()
//    ->noClear()
//    ->setMargin(10)
//    ->showClassParameters()
//    ->setColorSheme(
//        [
//            'title_background' => '#DDD',
//            'title_text'       => '#000',
//            'body_background'  => '#282c34',
//            'body_text'        => '#abb2bf',
//            'array'            => '#e06c75',
//            'object'           => '#c678dd',
//            'class_method'     => '#61afef',
//            'class_var'        => '#61afef',
//            'error'            => '#e06c75',
//            'string'           => '#61afef',
//            'integer'          => '#98c379',
//            'double'           => '#98c379',
//            'boolean'          => '#d19a66',
//            'NULL'             => '#d19a66',
//        ]
//    )
    ->init();
}
