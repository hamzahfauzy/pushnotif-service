<?php

$conn  = conn();
$db    = new Database($conn);
$msg   = get_flash_msg('success');
$notifications  = $db->all('notifications');

echo json_encode($notifications);
die();