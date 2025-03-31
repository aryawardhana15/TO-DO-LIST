<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $stmt = $pdo->prepare('DELETE FROM todos WHERE id = ?');
    $stmt->execute([$id]);
    
    header('Location: index.php');
    exit;
}
?>