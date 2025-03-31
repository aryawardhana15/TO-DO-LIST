<?php
require_once 'config.php';

// Search functionality
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$query = "SELECT * FROM todos WHERE title LIKE :search";
$params = ['search' => "%$search%"];

if ($filter === 'completed') {
    $query .= " AND is_completed = 1";
} elseif ($filter === 'active') {
    $query .= " AND is_completed = 0";
} elseif ($filter === 'today') {
    $query .= " AND due_date = CURDATE()";
}

$query .= " ORDER BY 
    CASE priority 
        WHEN 'high' THEN 1 
        WHEN 'medium' THEN 2 
        WHEN 'low' THEN 3 
        ELSE 4 
    END, due_date ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$todos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/scripts.js" defer></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 mb-2">
                   TO DO LIST 
                </h1>
                <p class="text-gray-600">Organize your life with ease</p>
            </div>
            
            <!-- Search and Filter -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <input type="text" id="searchInput" placeholder="Search tasks..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all"
                               value="<?= htmlspecialchars($search) ?>">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <select id="filterSelect" class="border border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Tasks</option>
                        <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Due Today</option>
                    </select>
                    <button id="searchBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                        Apply
                    </button>
                </div>
            </div>
            
            <!-- Add Task Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 transition-all duration-300 hover:shadow-xl">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-indigo-600"></i>
                    Add New Task
                </h2>
                <form action="create.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title*</label>
                            <input type="text" id="title" name="title" required 
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <input type="text" id="category" name="category" 
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                            <input type="date" id="due_date" name="due_date" 
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select id="priority" name="priority" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg shadow-md transition-all duration-300 transform hover:scale-[1.01]">
                        Add Task
                    </button>
                </form>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow p-6 text-center">
                    <h3 class="text-lg font-medium text-gray-500">Total Tasks</h3>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">
                        <?= count($todos) ?>
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 text-center">
                    <h3 class="text-lg font-medium text-gray-500">Completed</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        <?= count(array_filter($todos, fn($todo) => $todo['is_completed'])) ?>
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 text-center">
                    <h3 class="text-lg font-medium text-gray-500">Due Today</h3>
                    <p class="text-3xl font-bold text-orange-600 mt-2">
                        <?= count(array_filter($todos, fn($todo) => $todo['due_date'] === date('Y-m-d'))) ?>
                    </p>
                </div>
            </div>
            
            <!-- Task List -->
            <div class="space-y-4">
                <?php if (empty($todos)): ?>
                    <div class="text-center py-12 bg-white rounded-xl shadow">
                        <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-500">No tasks found</h3>
                        <p class="text-gray-400 mt-2">Try adding a task or changing your filters</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todos as $todo): ?>
                        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all duration-300 <?= $todo['is_completed'] ? 'border-l-4 border-green-500' : '' ?>">
                            <div class="flex flex-col md:flex-row md:items-start gap-4">
                                <div class="flex items-center mt-1 md:mt-0">
                                    <form action="complete.php" method="POST" class="todo-status-form">
                                        <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                        <input type="hidden" name="is_completed" value="<?= $todo['is_completed'] ? '0' : '1' ?>">
                                        <button type="submit" class="w-6 h-6 rounded-full border-2 flex items-center justify-center <?= $todo['is_completed'] ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-indigo-400' ?> transition-colors">
                                            <?php if ($todo['is_completed']): ?>
                                                <i class="fas fa-check text-xs"></i>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                        <h3 class="text-lg font-medium <?= $todo['is_completed'] ? 'text-gray-500 line-through' : 'text-gray-900' ?>">
                                            <?= htmlspecialchars($todo['title']) ?>
                                        </h3>
                                        <div class="flex items-center gap-2">
                                            <?php if ($todo['priority'] === 'high'): ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">High</span>
                                            <?php elseif ($todo['priority'] === 'medium'): ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Medium</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Low</span>
                                            <?php endif; ?>
                                            
                                            <?php if ($todo['category']): ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                                                    <?= htmlspecialchars($todo['category']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($todo['description'])): ?>
                                        <p class="mt-2 text-gray-600 <?= $todo['is_completed'] ? 'line-through' : '' ?>">
                                            <?= htmlspecialchars($todo['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                        <?php if ($todo['due_date']): ?>
                                            <div class="flex items-center gap-1 <?= $todo['due_date'] < date('Y-m-d') && !$todo['is_completed'] ? 'text-red-500' : '' ?>">
                                                <i class="far fa-calendar-alt"></i>
                                                <span><?= date('M j, Y', strtotime($todo['due_date'])) ?></span>
                                                <?php if ($todo['due_date'] < date('Y-m-d') && !$todo['is_completed']): ?>
                                                    <span class="ml-1 text-xs font-medium">(Overdue)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex items-center gap-1">
                                            <i class="far fa-clock"></i>
                                            <span><?= date('M j, Y g:i A', strtotime($todo['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <a href="update.php?id=<?= $todo['id'] ?>" 
                                       class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                       title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    
                                    <form action="delete.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                        <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                        <button type="submit" 
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('searchBtn').addEventListener('click', function() {
            const search = document.getElementById('searchInput').value;
            const filter = document.getElementById('filterSelect').value;
            window.location.href = `index.php?search=${encodeURIComponent(search)}&filter=${filter}`;
        });
    </script>
</body>
</html>