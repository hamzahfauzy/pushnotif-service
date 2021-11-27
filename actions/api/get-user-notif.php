<?php

$conn  = conn();
$db    = new Database($conn);
$msg   = get_flash_msg('success');

$query = "UPDATE notification_receivers SET receive_at='".date('Y-m-d H:i:s')."',status='received' WHERE user_id=$_GET[user_id] AND receive_at is NULL";
$db->query = $query;
$db->exec();

$receivers = $db->all('notification_receivers',[
    'user_id' => $_GET['user_id']
],[
    'receive_at' => 'desc'
]);

foreach($receivers as $receiver)
{
    $receiver->notification  = $db->single('notifications',[
        'id' => $receiver->notification_id
    ]);
}

echo json_encode($receivers);
die();