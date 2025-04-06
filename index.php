<?php
require_once 'config.php';

// Search functionality
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$sort = $_GET['sort'] ?? 'priority';

$query = "SELECT * FROM todos WHERE title LIKE :search";
$params = ['search' => "%$search%"];

if ($filter === 'completed') {
    $query .= " AND is_completed = 1";
} elseif ($filter === 'active') {
    $query .= " AND is_completed = 0";
} elseif ($filter === 'today') {
    $query .= " AND due_date = CURDATE()";
} elseif ($filter === 'overdue') {
    $query .= " AND due_date < CURDATE() AND is_completed = 0";
}

// Sorting options
if ($sort === 'priority') {
    $query .= " ORDER BY 
        CASE priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
            ELSE 4 
        END, due_date ASC";
} elseif ($sort === 'due_date') {
    $query .= " ORDER BY due_date ASC, 
        CASE priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
            ELSE 4 
        END";
} elseif ($sort === 'created') {
    $query .= " ORDER BY created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$todos = $stmt->fetchAll();

// Count statistics
$total_todos = count($todos);
$completed_todos = count(array_filter($todos, fn($todo) => $todo['is_completed']));
$today_todos = count(array_filter($todos, fn($todo) => $todo['due_date'] === date('Y-m-d')));
$overdue_todos = count(array_filter($todos, fn($todo) => $todo['due_date'] < date('Y-m-d') && !$todo['is_completed']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenTask | Modern To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                        success: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        warning: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .task-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .task-card.completed {
            border-left-color: #22c55e;
            background-color: #f8fafc;
        }
        .task-card.overdue:not(.completed) {
            border-left-color: #ef4444;
        }
        .priority-high {
            border-left-color: #ef4444 !important;
        }
        .priority-medium {
            border-left-color: #f59e0b !important;
        }
        .priority-low {
            border-left-color: #22c55e !important;
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e2e8f0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #38bdf8, #8b5cf6);
            transition: width 0.5s ease;
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center p-3 rounded-full bg-gradient-to-r from-primary-100 to-secondary-100 shadow-lg mb-4">
                    <i class="fas fa-check-circle text-4xl text-gradient bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent"></i>
                </div>
                <h1 class="text-5xl font-bold text-gradient bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent mb-3">
                    ZenTask
                </h1>
                <p class="text-slate-600 max-w-lg mx-auto">Your modern productivity companion. Stay organized, stay focused.</p>
            </div>
            
            <!-- Progress Overview -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-800">Progress Overview</h2>
                        <p class="text-slate-500">Track your productivity journey</p>
                    </div>
                    <div class="w-full md:w-64">
                        <div class="flex justify-between text-sm text-slate-600 mb-1">
                            <span><?= $completed_todos ?> of <?= $total_todos ?> tasks</span>
                            <span><?= $total_todos > 0 ? round(($completed_todos / $total_todos) * 100) : 0 ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $total_todos > 0 ? ($completed_todos / $total_todos) * 100 : 0 ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filter -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search tasks..." 
                               class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all"
                               value="<?= htmlspecialchars($search) ?>">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <select id="filterSelect" class="border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-200 focus:border-primary-500 text-slate-700">
                            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Tasks</option>
                            <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Due Today</option>
                            <option value="overdue" <?= $filter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        </select>
                        
                        <select id="sortSelect" class="border border-slate-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-200 focus:border-primary-500 text-slate-700">
                            <option value="priority" <?= $sort === 'priority' ? 'selected' : '' ?>>Sort by Priority</option>
                            <option value="due_date" <?= $sort === 'due_date' ? 'selected' : '' ?>>Sort by Due Date</option>
                            <option value="created" <?= $sort === 'created' ? 'selected' : '' ?>>Sort by Created</option>
                        </select>
                    </div>
                    
                    <button id="searchBtn" class="bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 text-white px-6 py-2 rounded-lg transition-all shadow-md hover:shadow-lg">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full bg-primary-100 text-primary-600">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-500">Total Tasks</h3>
                            <p class="text-2xl font-bold text-slate-800"><?= $total_todos ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full bg-success-100 text-success-600">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-500">Completed</h3>
                            <p class="text-2xl font-bold text-slate-800"><?= $completed_todos ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full bg-warning-100 text-warning-600">
                            <i class="fas fa-calendar-day text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-500">Due Today</h3>
                            <p class="text-2xl font-bold text-slate-800"><?= $today_todos ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full bg-danger-100 text-danger-600">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-slate-500">Overdue</h3>
                            <p class="text-2xl font-bold text-slate-800"><?= $overdue_todos ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Add Task Card -->
                <div class="lg:col-span-1 order-1 lg:order-1">
                    <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-plus-circle text-primary-600"></i>
                            Create New Task
                        </h2>
                        <form action="create.php" method="POST" class="space-y-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-slate-700 mb-1">Title*</label>
                                <input type="text" id="title" name="title" required 
                                       class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all">
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                                <textarea id="description" name="description" rows="3"
                                          class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-slate-700 mb-1">Due Date</label>
                                    <input type="date" id="due_date" name="due_date" 
                                           class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all">
                                </div>
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
                                    <select id="priority" name="priority" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all">
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                                <select id="category" name="category" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all">
                                    <option value="">Select a category</option>
                                    <option value="Work">Work</option>
                                    <option value="Personal">Personal</option>
                                    <option value="Health">Health</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Education">Education</option>
                                </select>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 text-white font-medium py-3 px-4 rounded-lg shadow-md transition-all duration-300 transform hover:scale-[1.01] mt-2">
                                <i class="fas fa-plus mr-2"></i> Add Task
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Task List -->
                <div class="lg:col-span-2 order-2 lg:order-2">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-slate-800">
                                <?php 
                                    if ($filter === 'completed') echo 'Completed Tasks';
                                    elseif ($filter === 'active') echo 'Active Tasks';
                                    elseif ($filter === 'today') echo 'Tasks Due Today';
                                    elseif ($filter === 'overdue') echo 'Overdue Tasks';
                                    else echo 'All Tasks';
                                ?>
                            </h2>
                            <span class="text-sm text-slate-500"><?= count($todos) ?> items</span>
                        </div>
                        
                        <?php if (empty($todos)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-clipboard-list text-5xl text-slate-300 mb-4"></i>
                                <h3 class="text-xl font-medium text-slate-500">No tasks found</h3>
                                <p class="text-slate-400 mt-2">Try adding a task or adjusting your filters</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($todos as $todo): 
                                    $isOverdue = $todo['due_date'] && $todo['due_date'] < date('Y-m-d') && !$todo['is_completed'];
                                    $priorityClass = '';
                                    if (!$todo['is_completed']) {
                                        $priorityClass = 'priority-' . $todo['priority'];
                                    }
                                ?>
                                    <div class="task-card bg-white rounded-lg border border-slate-200 p-5 <?= $todo['is_completed'] ? 'completed' : '' ?> <?= $isOverdue ? 'overdue' : '' ?> <?= $priorityClass ?>">
                                        <div class="flex items-start gap-4">
                                            <div class="flex items-center mt-1">
                                                <form action="complete.php" method="POST" class="todo-status-form">
                                                    <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                                    <input type="hidden" name="is_completed" value="<?= $todo['is_completed'] ? '0' : '1' ?>">
                                                    <button type="submit" class="w-5 h-5 rounded-full border-2 flex items-center justify-center <?= $todo['is_completed'] ? 'bg-success-500 border-success-500 text-white' : 'border-slate-300 hover:border-primary-400' ?> transition-colors">
                                                        <?php if ($todo['is_completed']): ?>
                                                            <i class="fas fa-check text-xs"></i>
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </div>
                                            
                                            <div class="flex-1">
                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                                    <h3 class="text-lg font-medium <?= $todo['is_completed'] ? 'text-slate-500 line-through' : 'text-slate-800' ?>">
                                                        <?= htmlspecialchars($todo['title']) ?>
                                                    </h3>
                                                    <div class="flex items-center gap-2">
                                                        <?php if ($todo['priority'] === 'high'): ?>
                                                            <span class="px-2 py-1 text-xs font-medium bg-danger-100 text-danger-800 rounded-full flex items-center gap-1">
                                                                <i class="fas fa-arrow-up"></i> High
                                                            </span>
                                                        <?php elseif ($todo['priority'] === 'medium'): ?>
                                                            <span class="px-2 py-1 text-xs font-medium bg-warning-100 text-warning-800 rounded-full flex items-center gap-1">
                                                                <i class="fas fa-equals"></i> Medium
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="px-2 py-1 text-xs font-medium bg-success-100 text-success-800 rounded-full flex items-center gap-1">
                                                                <i class="fas fa-arrow-down"></i> Low
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($todo['category']): ?>
                                                            <span class="px-2 py-1 text-xs font-medium bg-secondary-100 text-secondary-800 rounded-full">
                                                                <?= htmlspecialchars($todo['category']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($todo['description'])): ?>
                                                    <p class="mt-2 text-slate-600 <?= $todo['is_completed'] ? 'line-through' : '' ?>">
                                                        <?= htmlspecialchars($todo['description']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                                    <?php if ($todo['due_date']): ?>
                                                        <div class="flex items-center gap-1 <?= $isOverdue ? 'text-danger-600 font-medium' : '' ?>">
                                                            <i class="far fa-calendar-alt"></i>
                                                            <span><?= date('M j, Y', strtotime($todo['due_date'])) ?></span>
                                                            <?php if ($isOverdue): ?>
                                                                <span class="ml-1 text-xs font-medium animate-pulse">(Overdue)</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="flex items-center gap-1">
                                                        <i class="far fa-clock"></i>
                                                        <span><?= date('M j, Y g:i A', strtotime($todo['created_at'])) ?></span>
                                                    </div>
                                                    
                                                    <?php if ($todo['is_completed']): ?>
                                                        <div class="flex items-center gap-1 text-success-600">
                                                            <i class="fas fa-check-circle"></i>
                                                            <span>Completed on <?= date('M j, Y', strtotime($todo['completed_at'] ?? $todo['updated_at'])) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-2">
                                                <a href="update.php?id=<?= $todo['id'] ?>" 
                                                   class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                                   title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                
                                                <form action="delete.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                    <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                                    <button type="submit" 
                                                            class="p-2 text-danger-600 hover:bg-danger-50 rounded-lg transition-colors"
                                                            title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-semibold text-slate-800 mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <form action="complete_all.php" method="POST" class="col-span-1">
                                <button type="submit" class="w-full bg-success-100 hover:bg-success-200 text-success-800 font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-check-circle mr-2"></i> Complete All
                                </button>
                            </form>
                            <form action="clear_completed.php" method="POST" class="col-span-1">
                                <button type="submit" class="w-full bg-danger-100 hover:bg-danger-200 text-danger-800 font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-trash-alt mr-2"></i> Clear Completed
                                </button>
                            </form>
                            <a href="export.php" class="col-span-1">
                                <button class="w-full bg-primary-100 hover:bg-primary-200 text-primary-800 font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-file-export mr-2"></i> Export
                                </button>
                            </a>
                            <a href="import.php" class="col-span-1">
                                <button class="w-full bg-secondary-100 hover:bg-secondary-200 text-secondary-800 font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-file-import mr-2"></i> Import
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply filters
            document.getElementById('searchBtn').addEventListener('click', function() {
                const search = document.getElementById('searchInput').value;
                const filter = document.getElementById('filterSelect').value;
                const sort = document.getElementById('sortSelect').value;
                window.location.href = index.php?search=${encodeURIComponent(search)}&filter=${filter}&sort=${sort};
            });
            
            // Allow pressing Enter in search input to apply filters
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('searchBtn').click();
                }
            });
            
            // Add animation to task cards when they come into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fadeIn');
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.task-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>