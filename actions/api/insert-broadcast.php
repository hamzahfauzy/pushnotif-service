<?php
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

$notif = $db->insert('notifications',$_POST);

echo json_encode([
    'status' => 'success',
    'data'   => $notif
]);
die();