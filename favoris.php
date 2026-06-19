<?php
// favoris.php — liste des annonces mises en favoris par l'utilisateur
// - nécessite une session utilisateur
// - récupère la table `favoris` et affiche les annonces correspondantes
session_start();

// Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: connexion.php');
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
    die("Erreur de connexion à la base de données.");
}

$userId = $_SESSION['user_id'];
$message = '';

// Traitement de la suppression d'un favori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $annonceId = (int)($_POST['annonce_id'] ?? 0);
    if ($annonceId > 0) {
        $stmt = $pdo->prepare('DELETE FROM favoris WHERE utilisateur_id = ? AND annonce_id = ?');
        $stmt->execute([$userId, $annonceId]);
        $message = 'Annonce supprimée de vos favoris.';
    }
}

// Récupérer les favoris de l'utilisateur
$stmt = $pdo->prepare('
    SELECT a.*, v.nom AS ville_nom, v.code_postal,
           CONCAT(u.prenom, " ", u.nom) AS proprietaire_nom,
           u.telephone AS proprietaire_telephone
    FROM favoris f
    JOIN annonces a ON f.annonce_id = a.id
    LEFT JOIN villes v ON a.ville_id = v.id
    LEFT JOIN utilisateurs u ON a.proprietaire_id = u.id
    WHERE f.utilisateur_id = ? AND a.statut = "disponible"
    ORDER BY f.cree_le DESC
');
$stmt->execute([$userId]);
$favoris = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Favoris — SmartHome</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'DM Sans', sans-serif;
      background-color: #f4f7f6;
      color: #0d1f1c;
    }
    .favoris-container {
      max-width: 1200px;
      margin: 120px auto 60px;
      padding: 0 20px;
    }
    .favoris-header {
      text-align: center;
      margin-bottom: 3rem;
    }
    .favoris-header h1 {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .favoris-header p {
      color: #637470;
      font-size: 1rem;
    }
    .message {
      padding: 0.95rem 1rem;
      border-radius: 14px;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
    }
    .message-success {
      background: #d4f5ea;
      color: #085041;
      border: 1px solid #b9e7d6;
    }
    .empty-state {
      text-align: center;
      padding: 3rem;
      background: #fff;
      border-radius: 20px;
      border: 1px solid #e2e8f0;
    }
    .empty-state h2 {
      font-family: 'Syne', sans-serif;
      font-size: 1.5rem;
      color: #0d1f1c;
      margin-bottom: 0.5rem;
    }
    .empty-state p {
      color: #637470;
      margin-bottom: 1.5rem;
    }
    .btn-explore {
      display: inline-block;
      background: #00b894;
      color: #fff;
      padding: 0.8rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.3s ease;
    }
    .btn-explore:hover {
      background: #00a383;
    }
    .favoris-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 30px;
      margin-bottom: 3rem;
    }
    .annonce-card {
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.02);
      transition: box-shadow 0.3s ease;
    }
    .annonce-card:hover {
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .annonce-image {
      height: 200px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    .remove-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(255,255,255,0.9);
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s ease, background 0.2s ease;
      z-index: 10;
    }
    .remove-btn:hover {
      background: #feecec;
      color: #8f1b1b;
      transform: scale(1.1);
    }
    .annonce-content {
      padding: 1.5rem;
    }
    .annonce-title {
      font-family: 'Syne', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #0d1f1c;
    }
    .annonce-location {
      color: #637470;
      font-size: 0.9rem;
      margin-bottom: 0.8rem;
    }
    .annonce-price {
      font-size: 1.3rem;
      font-weight: 700;
      color: #00b894;
      margin-bottom: 1rem;
    }
    .annonce-meta {
      display: flex;
      gap: 15px;
      font-size: 0.85rem;
      color: #8a9e99;
      margin-bottom: 1rem;
    }
    .meta-item {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .btn-view {
      display: block;
      width: 100%;
      text-align: center;
      background: #00b894;
      color: #fff;
      padding: 0.8rem;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.3s ease;
    }
    .btn-view:hover {
      background: #00a383;
    }
    .nav-links {
      display: flex;
      align-items: center;
      list-style: none;
      gap: 20px;
    }
    .nav-links a {
      text-decoration: none;
      color: #0d1f1c;
      font-weight: 500;
    }
    .nav-cta {
      background: #00b894;
      color: #fff !important;
      padding: 8px 18px;
      border-radius: 20px;
      font-weight: 600 !important;
    }
    .nav-logout {
      background: #feecec;
      color: #8f1b1b !important;
      padding: 8px 18px;
      border-radius: 20px;
      font-weight: 600 !important;
    }
    .user-welcome {
      font-weight: 600;
      color: #0d1f1c;
    }
  </style>
</head>
<body>

<!-- Barre de navigation -->
<nav>
  <a href="index.php" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Accueil</a></li>
    <li><a href="ajouter_annonce.php">➕ Ajouter une annonce</a></li>
    <li><a href="favoris.php" style="color: #00b894; font-weight: 600;">❤️ Mes favoris</a></li>
    <li><span class="user-welcome">Bonjour <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?></span></li>
    <li><a href="profil.php">👤 Mon profil</a></li>
    <li><a href="logout.php" class="nav-logout">Se déconnecter</a></li>
  </ul>
</nav>

<div class="favoris-container">
  <div class="favoris-header">
    <h1>❤️ Mes Favoris</h1>
    <p>Retrouvez toutes les annonces que vous avez aimées</p>
  </div>

  <?php if ($message): ?>
    <div class="message message-success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (empty($favoris)): ?>
    <div class="empty-state">
      <h2>Aucun favori pour le moment</h2>
      <p>Explorez nos annonces et ajoutez-les à vos favoris !</p>
      <a href="index.php" class="btn-explore">Découvrir les annonces</a>
    </div>
  <?php else: ?>
    <div class="favoris-grid">
      <?php foreach ($favoris as $annonce): ?>
        <div class="annonce-card">
          <div class="annonce-image" style="background-image: url('https://images.unsplash.com/photo-<?= !empty($annonce['image_unsplash']) ? $annonce['image_unsplash'] : '1502672260266-1c1ef2d93688' ?>?w=600&q=80');">
            <form method="POST" style="position: absolute; top: 10px; right: 10px;">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="annonce_id" value="<?= $annonce['id'] ?>">
              <button type="submit" class="remove-btn" title="Supprimer des favoris">✕</button>
            </form>
          </div>
          <div class="annonce-content">
            <h3 class="annonce-title"><?= htmlspecialchars($annonce['titre']) ?></h3>
            <p class="annonce-location">📍 <?= htmlspecialchars($annonce['quartier'] ?? '') ?> — <?= htmlspecialchars($annonce['ville_nom'] ?? '') ?></p>
            <p class="annonce-price"><?= number_format($annonce['loyer'], 0, ',', ' ') ?> €/mois</p>
            <div class="annonce-meta">
              <div class="meta-item">📏 <?= $annonce['surface_m2'] ?> m²</div>
              <div class="meta-item">🏠 <?= htmlspecialchars($annonce['type_logement']) ?></div>
              <div class="meta-item">🛏️ <?= $annonce['nb_pieces'] ?> pièce(s)</div>
            </div>
            <a href="annonce.php?id=<?= $annonce['id'] ?>" class="btn-view">Voir les détails</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
