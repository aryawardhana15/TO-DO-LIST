<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['is_completed'])) {
    $id = $_POST['id'];
    $is_completed = $_POST['is_completed'];
    
    $stmt = $pdo->prepare('UPDATE todos SET is_completed = ? WHERE id = ?');
    $stmt->execute([$is_completed, $id]);
    
    header('Location: index.php');
    exit;
}
?>