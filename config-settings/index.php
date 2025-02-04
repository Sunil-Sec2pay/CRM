<?php
spl_autoload_register(function ($class_name) {
    $class_file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
});
if (!isset($_POST['action']) || empty($_POST['action'])) {
    http_response_code(400);
    echo json_encode(["error" => "Action parameter is required"]);
    exit;
}
$action = strtolower($_POST['action']);

list($class, $method) = explode('.', $action);

$class = ucfirst($class);
$class_file = __DIR__ . "/$class.php";
if (!file_exists($class_file)) {
    http_response_code(404);
    echo json_encode(["error" => "Class $class not found"]);
    exit;
}

require_once $class_file;
if (!class_exists($class)) {
    http_response_code(404);
    echo json_encode(["error" => "Class $class does not exist"]);
    exit;
}

$object = new $class();
if (!method_exists($object, $method)) {
    http_response_code(404);
    echo json_encode(["error" => "Method $method not found in class $class"]);
    exit;
}

try {
    $params = $_POST;
    if (empty($params)) {
        $rawInput = file_get_contents('php://input');
            $params = $rawInput;
    }
    unset($params['action']);
    $response =  call_user_func_array(array($object, $method), array($object));
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
