<?php
require_once('config.php');
require_once('functions.php');
session_start();

// セッションがない状態でアクセスした場合、index.phpに遷移する
if (!isset($_SESSION['project'])) {
    header('Location: '.SITE_URL);
    exit;
}

$user_name = $_POST['user_name'];
$user_email= $_POST['user_email'];
$user_message= $_POST['user_message'];

$header = "From:".$user_email."\n";
$body = $user_name."さんからメッセージが届きました。\n".$user_message;

mb_language("Japanese");
mb_internal_encoding("UTF-8");

mb_send_mail(ADMIN_EMAIL, 'メッセージが届いています。[Group Scheduler]', $body);

echo $body;
?>
