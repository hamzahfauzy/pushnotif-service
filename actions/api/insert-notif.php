<?php
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

$factory = (new Factory)->withServiceAccount('pushnotif-332914-0af7675355ad.json');
/*
Insert notification via POST request
endpoint : index.php?action=api/insert-notif
body : 
    contents  -> text
    sent_at   -> datetime
    url       -> url
    user_id   -> integer
    user_name -> text
return :
    status -> success
    data   -> record data
*/

$conn  = conn();
$db    = new Database($conn);

$messaging = $factory->createMessaging();
$message = CloudMessage::fromArray([
    'topic' => 'topic_'.$_POST['user_id'],
    'notification' => [
        'title'=>'Notifikasi Baru',
        'body' =>$_POST['contents']
    ],
    'data' => [
        'url' => $_POST['url']
    ]
]);
    
$messaging->send($message);

$notif = $db->insert('notifications',$_POST);

echo json_encode([
    'status' => 'success',
    'data'   => $notif
]);
die();