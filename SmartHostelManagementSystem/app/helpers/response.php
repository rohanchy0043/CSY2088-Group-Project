<?php
/**
 * Response helper functions
 */

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function jsonSuccess($data = null, $message = 'Success') {
    return jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

function jsonError($message = 'Error', $statusCode = 400, $data = null) {
    return jsonResponse([
        'success' => false,
        'message' => $message,
        'data' => $data
    ], $statusCode);
}

function view($viewPath, $data = []) {
    extract($data);
    $file = VIEWS_PATH . '/' . $viewPath . '.php';
    if (!file_exists($file)) {
        throw new Exception("View not found: {$viewPath}");
    }
    include $file;
}

function render($viewPath, $data = [], $layout = 'layouts/main') {
    // Start output buffering
    ob_start();
    view($viewPath, $data);
    $content = ob_get_clean();
    
    // If layout is provided, render it with content
    if ($layout) {
        $data['content'] = $content;
        view($layout, $data);
    } else {
        echo $content;
    }
}

function abort($code = 404, $message = 'Not Found') {
    http_response_code($code);
    echo "<h1>{$code} - {$message}</h1>";
    exit;
}
?>