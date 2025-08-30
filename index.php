<?php
session_name('cn_dash');
session_start();
date_default_timezone_set("America/Cancun");


$env = $_GET['env'] ?? 'prod';
define('ENV', $env);

require_once 'app/core/Router.php';
require_once 'app/core/Controller.php';
require_once 'app/core/Auth.php';
require_once 'app/helpers/helpers.php';

require_once 'app/core/Middleware.php';
Middleware::blockMobile();


$router = new Router();
$router->route();