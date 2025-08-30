<?php

$KEY_SECRECT = __dir__."/KEY_SECRECT.ini";
$KEY = parse_ini_file($KEY_SECRECT, true);
define('__START__','¡');
define('__END__','&');
define('__SPACE__','&::&::&');
define('__CONST__',$KEY['__CONST__']);
define('__RANG__',array('INICIO' => 0 , 'FIN' => 99));