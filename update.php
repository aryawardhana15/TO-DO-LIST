<?php
require_once 'config.php';

// Get task to edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = ?');
    $stmt->execute([$id]);
    $todo = $stmt->fetch();
    
    if (!$todo) {
        header('Location: index.php');
        exit;
    }
}

// Update task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $category = !empty($_POST['category']) ? trim($_POST['category']) : null;
    
    $stmt = $pdo->prepare('UPDATE todos SET title = ?, description = ?, priority = ?, due_date = ?, category = ? WHERE id = ?');
    $stmt->execute([$title, $description, $priority, $due_date, $category, $id]);
    
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
            <h2 class="text-xl font-semibold flex items-center gap-2">
                <i class="fas fa-edit"></i>
                Edit Task
            </h2>
        </div>
        
        <form action="update.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" value="<?= $todo['id'] ?>">
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title*</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($todo['title']) ?>" required 
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
            </div>
            
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($todo['category'] ?? '') ?>" 
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all"><?= htmlspecialchars($todo['description']) ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="<?= $todo['due_date'] ? htmlspecialchars($todo['due_date']) : '' ?>" 
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
                </div>
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select id="priority" name="priority" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
                        <option value="high" <?= $todo['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="medium" <?= $todo['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="low" <?= $todo['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <a href="index.php" class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-lg shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</body>
</html>