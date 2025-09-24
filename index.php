<?php

require_once 'config.php';
require_once 'database.php';
require_once 'models/Task.php';

// Включить CORS Обязательно прочитать некоторые инструкции что в корне.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json'); // Всегда возвращайте JSON

$db = Database::getConnection();

// Убедитесь, что таблица задач существует
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

// Удалить имя скрипта из сегментов, если оно есть.
if (isset($segments[0]) && $segments[0] === basename(__FILE__)) {
  array_shift($segments);
}

$resource = $segments[0] ?? null; // e.g., "задачи"
$id = $segments[1] ?? null; // e.g., "123"

//  обработка ID, если есть
if ($id !== null && !ctype_digit($id)) {
    http_response_code(400); // Плохой запрос
    echo json_encode(['message' => 'Не правильный задачный ID.']);
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
            echo json_encode(['message' => 'Ресурс не найден.']);
        }
        break;
    case 'POST':
        if ($resource == 'tasks') {
            createTask();
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ресурс не найден.']);
        }
        break;
    case 'PUT':
        if ($resource == 'tasks' && $id) {
            updateTask($id);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ресурс не найден.']);
        }
        break;
    case 'DELETE':
        if ($resource == 'tasks' && $id) {
            deleteTask($id);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ресурс не найден.']);
        }
        break;
    
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
        echo json_encode(['message' => 'Taskненайдена']);
    }
}

function createTask() {
    global $db;
    $input = json_decode(file_get_contents('php://input'), true); // Read from the bodyif (empty($input['title'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Title обязателен']);
    return;
}

if (strlen($input['title']) > 255) {
    http_response_code(400);
    echo json_encode(['message' => 'Title не можеи превышать 255']);
    return;
}

$title = $input['title'];
$description = $input['description'] ?? ''; // По умолчанию пустой
$status = $input['status'] ?? 'pending';  // По умолчанию пендинг статус

$stmt = $db->prepare("INSERT INTO tasks (title, description, status) VALUES (:title, :description, :status)");
$stmt->bindParam(':title', $title);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':status', $status);

try {
    $stmt->execute();
    $taskId = $db->lastInsertId();
    http_response_code(201); // Созданный
    echo json_encode(['message' => 'Task создана успешно', 'id' => $taskId]);

} catch (PDOException $e) {
    http_response_code(500); // Внутренняя сервеная ошибка
    echo json_encode(['message' => 'Failed созаднная task: ' . $e->getMessage()]);
}

            }

function updateTask($id) {
    global $db;
    $input = json_decode(file_get_contents('php://input'), true);if (empty($input['title'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Title обязателен']);
    return;
}

$title = $input['title'];
$description = $input['description'] ?? '';
$status = $input['status'] ?? 'pending';

$stmt = $db-&gt;prepare("UPDATE tasks SET title = :title, description = :description, status = :status WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->bindParam(':title', $title);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':status', $status);

try {
    $stmt->execute();
    http_response_code(200);
    echo json_encode(['message' => 'Task выполнена успешно']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed обновлениеtask: ' . $e->getMessage()]);
}
}

function deleteTask($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->bindParam(':id', $id);try {
    $stmt->execute();
    http_response_code(200);
    echo json_encode(['message' => 'Task уничтожена успешно']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed убрана task: ' . $e->getMessage()]);
}
}

?>
