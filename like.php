<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// Connexion à la base de données
$host   = '127.0.0.1';
$dbname = 'smarthome';
$user   = 'root';
$pass   = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion']);
    exit;
}

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$annonceId = (int)($_POST['annonce_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$annonceId || !in_array($action, ['like', 'unlike'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    if ($action === 'like') {
        // Vérifier que la favori n'existe pas déjà
        $stmt = $pdo->prepare('SELECT id FROM favoris WHERE utilisateur_id = ? AND annonce_id = ?');
        $stmt->execute([$userId, $annonceId]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare('INSERT INTO favoris (utilisateur_id, annonce_id) VALUES (?, ?)');
            $stmt->execute([$userId, $annonceId]);
        }
        echo json_encode(['success' => true, 'liked' => true]);
    } else {
        // Supprimer le favori
        $stmt = $pdo->prepare('DELETE FROM favoris WHERE utilisateur_id = ? AND annonce_id = ?');
        $stmt->execute([$userId, $annonceId]);
        echo json_encode(['success' => true, 'liked' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
