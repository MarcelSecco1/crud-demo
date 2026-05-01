<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dataFile = __DIR__ . '/../data/users.json';

if (!is_dir(dirname($dataFile))) {
    mkdir(dirname($dataFile), 0777, true);
}
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

function readUsers($file) {
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

function writeUsers($file, $users) {
    file_put_contents($file, json_encode(array_values($users), JSON_PRETTY_PRINT));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateName($name) {
    return strlen(trim($name)) >= 2;
}

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    // LIST
    case 'GET':
        $users = readUsers($dataFile);
        if ($id !== null) {
            $user = array_values(array_filter($users, fn($u) => $u['id'] === $id));
            if (empty($user)) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuário não encontrado']);
            } else {
                echo json_encode($user[0]);
            }
        } else {
            echo json_encode($users);
        }
        break;

    // CREATE
    case 'POST':
        if (empty($input['name']) || empty($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e e-mail são obrigatórios']);
            break;
        }
        if (!validateName($input['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'O nome deve ter ao menos 2 caracteres']);
            break;
        }
        if (!validateEmail($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de e-mail inválido']);
            break;
        }
        $users   = readUsers($dataFile);
        $newId   = empty($users) ? 1 : max(array_column($users, 'id')) + 1;
        $newUser = [
            'id'         => $newId,
            'name'       => htmlspecialchars(trim($input['name'])),
            'email'      => strtolower(trim($input['email'])),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $users[] = $newUser;
        writeUsers($dataFile, $users);
        http_response_code(201);
        echo json_encode($newUser);
        break;

    // UPDATE
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID necessário']);
            break;
        }
        if (isset($input['name']) && !validateName($input['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'O nome deve ter ao menos 2 caracteres']);
            break;
        }
        if (isset($input['email']) && !validateEmail($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de e-mail inválido']);
            break;
        }
        $users = readUsers($dataFile);
        $found = false;
        foreach ($users as &$user) {
            if ($user['id'] === $id) {
                if (!empty($input['name']))  $user['name']  = htmlspecialchars(trim($input['name']));
                if (!empty($input['email'])) $user['email'] = strtolower(trim($input['email']));
                $found   = true;
                $updated = $user;
                break;
            }
        }
        if (!$found) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
            break;
        }
        writeUsers($dataFile, $users);
        echo json_encode($updated);
        break;

    // DELETE
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID necessário']);
            break;
        }
        $users    = readUsers($dataFile);
        $filtered = array_filter($users, fn($u) => $u['id'] !== $id);
        if (count($filtered) === count($users)) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
            break;
        }
        writeUsers($dataFile, $filtered);
        echo json_encode(['message' => 'Usuário removido com sucesso']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
}
