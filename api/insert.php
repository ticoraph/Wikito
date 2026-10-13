<?php

header('Content-Type: application/json; charset=utf-8');

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Si les données ne sont pas dans $_POST (ex: requête avec Content-Type: application/json)
if ($title === '' && $description === '') {
    $jsonBody = file_get_contents('php://input');
    $data = json_decode($jsonBody, true);
    if (is_array($data)) {
        $title = isset($data['title']) ? trim($data['title']) : '';
        $description = isset($data['description']) ? trim($data['description']) : '';
    }
}

if ($title === '' || $description === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le titre et la description sont obligatoires.']);
    exit;
}

$jsonFile = '../data/glossary.json';

// Check if file exists, if not, try to create it
if (!file_exists($jsonFile)) {
    // If directory doesn't exist, create it
    if (!is_dir(dirname($jsonFile))) {
        mkdir(dirname($jsonFile), 0755, true);
    }
    file_put_contents($jsonFile, '[]');
}

// Open file for read/write
$fp = fopen($jsonFile, 'c+');

if (!$fp) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible d\'ouvrir le fichier de données.']);
    exit;
}

// Acquire exclusive lock
if (flock($fp, LOCK_EX)) {
    // Read current content
    $filesize = filesize($jsonFile);
    $content = $filesize > 0 ? fread($fp, $filesize) : '[]';
    
    // Decode JSON
    $data = json_decode($content, true);
    if (!is_array($data)) {
        $data = [];
    }

    // Check for duplicates (case-insensitive)
    foreach ($data as $item) {
        if (strcasecmp($item['title'], $title) === 0) {
            flock($fp, LOCK_UN);
            fclose($fp);
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ce terme existe déjà dans le wiki.']);
            exit;
        }
    }

    // Add new entry
    $data[] = [
        'title' => $title,
        'description' => $description
    ];

    // Sort alphabetically
    usort($data, function($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });

    // Encode JSON
    $newContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($newContent === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'encodage JSON.']);
        exit;
    }

    // Write to file
    ftruncate($fp, 0); // clear file
    rewind($fp); // go to start
    fwrite($fp, $newContent);

    // Release lock and close
    flock($fp, LOCK_UN);
    fclose($fp);

    echo json_encode(['success' => true]);

} else {
    fclose($fp);
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Le fichier est actuellement verrouillé, veuillez réessayer.']);
}
