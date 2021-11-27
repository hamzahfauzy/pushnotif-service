<?php

require '../libs/JwtAuth.php';
require '../libs/Database.php';

$config = require '../config/main.php';

function config($key = false)
{
    global $config;
    if($key) return $config[$key];
    return $config;
}

function conn(){
    $database = config('database');

    return new mysqli(
        $database['host'],
        $database['username'],
        $database['password'],
        $database['dbname']
    );
}

function load_page($page)
{

    $action = load_action($page);
    $data = !is_array($action) ? [] : $action;
    load_templates($page,$data);
    return;
}

function load_action($action)
{
    if(file_exists('../actions/'.$action.'.php'))
        return require '../actions/'.$action.'.php';
    return [];
}

function load_templates($template, $data = [])
{    
    if(file_exists('../templates/'.$template.'.php'))
    {
        extract($data);
        require '../templates/'.$template.'.php';
    }
    else
        require '../templates/errors/404.php';
}

function startWith($str, $compare)
{
    return substr($str, 0, strlen($compare)) === $compare;
}

function base_url()
{
    return url(); // config('base_url');
}

function url(){
    $server_name = $_SERVER['SERVER_NAME'];

    if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {
        $port = ":$_SERVER[SERVER_PORT]";
    } else {
        $port = '';
    }

    if (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
        $scheme = 'https';
    } else {
        $scheme = 'http';
    }
    return $scheme.'://'.$server_name.$port;
}

function auth()
{
    // mode jwt
    return JwtAuth::get();
}

function stringContains($string,$val){
    if (strpos($string, $val) !== false) {
        return true;
    }

    return false;
}

function arrStringContains($string,$arr){

    $result = [];

    for($i = 0; $i < count($arr);$i++){
       $result[] = stringContains($string,$arr[$i]);
    }

    return in_array(true,$result);
}

function request($method = false)
{
    if(!$method)
        return $_SERVER['REQUEST_METHOD'];

    if(strtolower($method) == 'post')
        return $_POST;

    return $_GET;
}

function get_route()
{
    return $_GET['r']??'';
}

function startsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    return substr( $haystack, 0, $length ) === $needle;
}

function endsWith( $haystack, $needle ) {
   $length = strlen( $needle );
   if( !$length ) {
       return true;
   }
   return substr( $haystack, -$length ) === $needle;
}

function set_flash_msg($data)
{
    $_SESSION['flash'] = $data;
}

function get_flash_msg($key)
{
    if(isset($_SESSION['flash'][$key]))
    {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    return false;
}