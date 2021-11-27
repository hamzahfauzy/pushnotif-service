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
    $type = $database['driver'];
    if($type=='PDO')
    {
        // Connect using UNIX sockets
        if($database['socket'])
        {
            $dsn = sprintf(
                'mysql:dbname=%s;unix_socket=%s',
                $database['dbname'],
                $database['socket']
            );
        }
        else
        {
            $dsn = sprintf(
                'mysql:dbname=%s;host=%s',
                $database['dbname'],
                $database['host']
            );
        }

        // Connect to the database.
        $conn = new PDO($dsn, $database['username'], $database['password']);

        return $conn;
    }
    else
    {
        return new mysqli(
            $database['host'],
            $database['username'],
            $database['password'],
            $database['dbname'],
            $database['port'],
            $database['socket']
        );
    }

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

/**
 * Wrapper for easy cURLing
 *
 * @author Viliam Kopecký
 *
 * @param string HTTP method (GET|POST|PUT|DELETE)
 * @param string URI
 * @param mixed content for POST and PUT methods
 * @param array headers
 * @param array curl options
 * @return array of 'headers', 'content', 'error'
 */
function simple_curl($uri, $method='GET', $data=null, $curl_headers=array(), $curl_options=array()) {
	// defaults
	$default_curl_options = array(
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 3,
	);
	$default_headers = array();

	// validate input
	$method = strtoupper(trim($method));
	$allowed_methods = array('GET', 'POST', 'PUT', 'DELETE');

	if(!in_array($method, $allowed_methods))
		throw new \Exception("'$method' is not valid cURL HTTP method.");

	if(!empty($data) && !is_string($data))
		throw new \Exception("Invalid data for cURL request '$method $uri'");

	// init
	$curl = curl_init($uri);

	// apply default options
	curl_setopt_array($curl, $default_curl_options);

	// apply method specific options
	switch($method) {
		case 'GET':
			break;
		case 'POST':
			if(!is_string($data))
				throw new \Exception("Invalid data for cURL request '$method $uri'");
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case 'PUT':
			if(!is_string($data))
				throw new \Exception("Invalid data for cURL request '$method $uri'");
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case 'DELETE':
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			break;
	}

	// apply user options
	curl_setopt_array($curl, $curl_options);

	// add headers
	curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($default_headers, $curl_headers));

	// parse result
	$raw = rtrim(curl_exec($curl));
	$lines = explode("\r\n", $raw);
	$headers = array();
	$content = '';
	$write_content = false;
	if(count($lines) > 3) {
		foreach($lines as $h) {
			if($h == '')
				$write_content = true;
			else {
				if($write_content)
					$content .= $h."\n";
				else
					$headers[] = $h;
			}
		}
	}
	$error = curl_error($curl);

	curl_close($curl);

	// return
	return array(
		'raw' => $raw,
		'headers' => $headers,
		'content' => $content,
		'error' => $error
	);
}

function pushNotification($to, $data = [])
{
    $apiKey = config('firebase_api_key');
    $fields = ['to'=>$to,'notification'=>$data];
    $headers = ['Authorication: key='.$apiKey,'Content-Type: application/json'];

    $url = 'https://fcm.googleapis.com/fcm/send';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELD, json_encode($fields));
    $result = curl_exec($ch);
    curl_close();
    return json_decode($result);
}

// egovtest-3c158-d23be64eff7a.json