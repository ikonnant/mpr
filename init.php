<?php
//class autoload php
spl_autoload_register(function ($className) {
    include $className . '.php';
});

//class autoload Bitrix
CModule::AddAutoloadClasses(
	'',
	array(
        'CMpr' => '/local/php_interface/CMpr.php'
    )
);

//function short class call
function mpr() {
    call_user_func_array('CMpr::mpr', func_get_args());
}
