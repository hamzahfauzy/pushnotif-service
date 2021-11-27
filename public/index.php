<?php
date_default_timezone_set('Asia/Jakarta');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

session_start();
require '../vendor/autoload.php';
require '../functions.php';

// do before action
$beforeAction = require '../before-actions/index.php';
if($beforeAction)
{
    if(isset($_GET['action']))
        load_action($_GET['action']);
    else
    {
        $page = 'default/index';
    
        if(isset($_GET['r'])) // r stand for route
        {
            $page = $_GET['r'];
        }
        
        load_page($page);
    }
}
else
{
    load_page('errors/403');
}
die();