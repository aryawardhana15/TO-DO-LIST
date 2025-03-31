<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $category = !empty($_POST['category']) ? trim($_POST['category']) : null;
    
    $stmt = $pdo->prepare('INSERT INTO todos (title, description, priority, due_date, category) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$title, $description, $priority, $due_date, $category]);
    
    header('Location: index.php');
    exit;
}
?>