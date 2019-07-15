<?php

CModule::AddAutoloadClasses(
	'',
	array(
        'CMpr' => '/local/php_interface/CMpr.php'
    )
);

function mpr() {
    $arArgs = func_get_args();
    call_user_func_array('CMpr::mpr', $arArgs);
}