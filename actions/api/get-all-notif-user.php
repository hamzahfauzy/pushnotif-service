<?php

$conn  = conn();
$db    = new Database($conn);

$query = "SELECT * FROM notifications WHERE user_id=$_GET[user_id] or user_id is NULL";
$db->query = $query;
$notifications = $db->exec('all');
echo json_encode($notifications);
die();