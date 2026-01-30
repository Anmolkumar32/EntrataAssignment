<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\AIHelper;

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$code = $input['code'] ?? '';
$language = $input['language'] ?? 'python';

if (empty($code)) {
    echo json_encode(['success' => false, 'error' => 'Code snippet is required.']);
    exit;
}

try {
    $aiHelper = new AIHelper();
    $result = $aiHelper->explainCode($code, $language);

    if ($result['success']) {
        // Add to history
        if (!isset($_SESSION['history'])) {
            $_SESSION['history'] = [];
        }

        $historyItem = [
            'code' => $code,
            'language' => $language,
            'explanation' => $result['explanation'],
            'key_parts' => $result['key_parts'] ?? [],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Prepend to history (newest first)
        array_unshift($_SESSION['history'], $historyItem);

        // Keep only last 10 items
        if (count($_SESSION['history']) > 10) {
            array_pop($_SESSION['history']);
        }

        echo json_encode([
            'success' => true,
            'explanation' => $result['explanation'],
            'key_parts' => $result['key_parts'] ?? [],
            'history' => $_SESSION['history']
        ]);
    } else {
        echo json_encode($result);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
