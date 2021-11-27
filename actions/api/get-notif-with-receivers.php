<?php

$conn  = conn();
$db    = new Database($conn);
$msg   = get_flash_msg('success');
$notifications  = $db->all('notifications');
foreach($notifications as $notification)
{
    $notification->receivers = $db->all('notification_receivers',[
        'notification_id' => $notification->id
    ]);
}

echo json_encode($notifications);
die();