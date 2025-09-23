<?php

require_once 'config.php';
require_once 'database.php';
require_once 'models/Task.php';

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json'); // Always return JSON

$db = Database::getConnection();

// Ensure the tasks table exists
$db->exec("CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    status TEXT
)");

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// Remove the script name from segments if present
if (isset($segments[0]) && $segments[0] === basename(__FILE__)) {
  array_shift($segments);
}

$resource = $segments[0] ?? null; // e.g., "tasks"
$id = $segments[1] ?? null; // e.g., "123"

// Sanitize ID if present
if ($id !== null && !ctype_digit($id)) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Invalid task ID.']);
    exit;
}

switch ($request_method) {
    case 'GET':
        if ($resource == 'tasks') {
            if ($id) {
                getTask($id);
            } else {
                getTasks();
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Resource not found.']);
        }
        break;
    case 'POST':
        if ($resource == 'tasks') {
            createTask();
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Resource not found.']);
        }
        break;
    case 'PUT':
        if ($resource == 'tasks' && $id) {
            updateTask($id);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Resource not found.']);
        }
        break;
    case 'DELETE':
        if ($resource == 'tasks' && $id) {
            deleteTask($id);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Resource not found.']);
        }
        break;
    case 'OPTIONS': // Handle preflight requests
        http_response_code(200);
        break;
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => 'Method not allowed.']);
}

function getTasks() {
    global $db;
    $stmt = $db->query("SELECT * FROM tasks");
    $tasks = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tasks[] = new Task($row['id'], $row['title'], $row['description'], $row['status']);
    }
    $taskArrays = array_map(function($task) { return $task->toArray(); }, $tasks);
    echo json_encode($taskArrays);
}

function getTask($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM tasks WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $task = new Task($row['id'], $row['title'], $row['description'], $row['status']);
        echo json_encode($task->toArray());
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Task not found']);
    }
}

function createTask() {
    global $db;
    $input = json_decode(file_get_contents('php://input'), true); // Read from the bodyif (empty($input['title'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Title is required']);
    return;
}

if (strlen($input['title']) > 255) {
    http_response_code(400);
    echo json_encode(['message' => 'Title cannot exceed 255 characters']);
    return;
}

$title = $input['title'];
$description = $input['description'] ?? ''; // Default to empty string if not provided
$status = $input['status'] ?? 'pending';  // Default to "pending" if not provided

$stmt = $db->prepare("INSERT INTO tasks (title, description, status) VALUES (:title, :description, :status)");
$stmt->bindParam(':title', $title);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':status', $status);

try {
    $stmt->execute();
    $taskId = $db->lastInsertId();
    http_response_code(201); // Created
    echo json_encode(['message' => 'Task created successfully', 'id' => $taskId]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Failed to create task: ' . $e->getMessage()]);
}

            }

function updateTask($id) {
    global $db;
    $input = json_decode(file_get_contents('php://input'), true);if (empty($input['title'])) {
    http_response_code(400);
    echo json_encode(['message' =&gt; 'Title is required']);
    return;
}

$title = $input['title'];
$description = $input['description'] ?? '';
$status = $input['status'] ?? 'pending';

$stmt = $db-&gt;prepare("UPDATE tasks SET title = :title, description = :description, status = :status WHERE id = :id");
$stmt-&gt;bindParam(':id', $id);
$stmt-&gt;bindParam(':title', $title);
$stmt-&gt;bindParam(':description', $description);
$stmt-&gt;bindParam(':status', $status);

try {
    $stmt-&gt;execute();
    http_response_code(200);
    echo json_encode(['message' =&gt; 'Task updated successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' =&gt; 'Failed to update task: ' . $e-&gt;getMessage()]);
}
}

function deleteTask($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->bindParam(':id', $id);try {
    $stmt-&gt;execute();
    http_response_code(200);
    echo json_encode(['message' =&gt; 'Task deleted successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' =&gt; 'Failed to delete task: ' . $e-&gt;getMessage()]);
}
}

?>
