<?php
include 'routes/Router.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dispatch the request to the router for further processing
$router = new Router();
$router->dispatchRequest();
?>
