<?php

$conn  = conn();
$db    = new Database($conn);
$msg   = get_flash_msg('success');

$query = "SELECT * FROM notification_receivers WHERE user_id=$_GET[user_id] AND receive_at is NULL";
$db->query = $query;
$receivers = $db->exec('all');

foreach($receivers as $receiver)
{
    $receiver->notification  = $db->single('notifications',[
        'id' => $receiver->notification_id
    ]);
}

echo json_encode($receivers);
die();