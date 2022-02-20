<?php
require_once('config.php');
require_once('functions.php');
session_start();


$pdo = connectDb();
$id = $_GET['id'];

$sql = "DELETE FROM candidates WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":id" => $id));


unset($pdo);

// item_list.phpに画面遷移する。
header('Location: '.SITE_URL.'edit.php');
exit;

?>
