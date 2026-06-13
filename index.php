<?php
session_start();

// ── Connexion MySQL directe ──────────────────────────────────
$host   = '127.0.0.1';
$dbname = 'smarthome';
$user   = 'root';
$pass   = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Récupération de la recherche si elle existe
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if (!empty($search)) {
        // Recherche par titre, quartier ou description
        $stmt = $pdo->prepare('SELECT * FROM v_annonces_disponibles WHERE titre LIKE ? OR quartier LIKE ? OR description LIKE ? ORDER BY cree_le DESC');
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        $annonces = $stmt->fetchAll();
    } else {
        $annonces = $pdo->query('SELECT * FROM v_annonces_disponibles ORDER BY cree_le DESC')->fetchAll();
    }
} catch (PDOException $e) {
    $annonces = [];
}

$userConnecte = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SmartHome — Trouvez votre logement intelligemment</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css">
  
  <style>
    /* Styles pour la gestion du bouton de déconnexion et de l'état connecté */
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
      transition: background 0.3s ease;
    }
    .nav-cta:hover {
      background: #00a383;
    }
    .nav-logout {
      background: #feecec;
      color: #8f1b1b !important;
      padding: 8px 18px;
      border-radius: 20px;
      font-weight: 600 !important;
      transition: background 0.3s ease;
    }
    .nav-logout:hover {
      background: #fcd7d7;
    }
    .user-welcome {
      font-weight: 600;
      color: #0d1f1c;
    }

    /* Styles de la barre de recherche */
    .search-container {
      max-width: 600px;
      margin: 0 auto 40px auto;
    }
    .search-form {
      display: flex;
      gap: 10px;
      background: #fff;
      padding: 8px;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 20px rgba(13,31,28,0.03);
    }
    .search-input {
      flex: 1;
      border: none;
      padding: 10px 15px;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      color: #0d1f1c;
      outline: none;
    }
    .btn-search {
      background: #00b894;
      color: #fff;
      border: none;
      padding: 10px 24px;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .btn-search:hover {
      background: #00a383;
    }
    .clear-search {
      display: inline-block;
      margin-top: 10px;
      color: #637470;
      text-decoration: none;
      font-size: 0.9rem;
    }
    .clear-search:hover {
      color: #00b894;
    }
  </style>
</head>
 
<body>
 
<nav>
  <a href="index.php" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li><a href="#how">Comment ça marche</a></li>
    <li><a href="#why">Pourquoi nous ?</a></li>
    <li><a href="#footer-contact">Contacts</a></li>
    
    <?php if ($userConnecte): ?>
      <?php $nomAffichage = !empty($_SESSION['user_prenom']) ? ' ' . htmlspecialchars($_SESSION['user_prenom']) : ' !' ?>
      <li><span class="user-welcome">Bonjour<?= $nomAffichage ?></span></li>
      <li><a href="logout.php" class="nav-logout">Se déconnecter</a></li>
    <?php else: ?>
      <li><a href="#cta" class="nav-cta">Mon Compte</a></li>
    <?php endif; ?>
  </ul>
</nav>

<section style="max-width:1200px; margin: 120px auto 40px; padding: 0 20px;">
  
  <div style="text-align: center; margin-bottom: 40px;">
    <h2 style="font-family:'Syne'; font-size: 2.5rem; margin-bottom: 10px; color: #0d1f1c;">Annonces disponibles</h2>
    <p style="color: #637470; font-family:'DM Sans'; margin-bottom: 25px;">Trouvez le bien idéal parmi nos logements vérifiés</p>
    
    <div class="search-container">
      <form action="index.php" method="GET" class="search-form">
        <input type="text" name="search" class="search-input" placeholder="Rechercher par ville, quartier, mot-clé..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Rechercher</button>
      </form>
      <?php if (!empty($search)): ?>
        <a href="index.php" class="clear-search">❌ Réinitialiser la recherche</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($annonces)): ?>
    <div style="text-align: center; padding: 40px; background: #fff; border-radius: 20px; border: 1px dashed #e2e8f0;">
      <p style="color: #637470; font-size: 1.1rem;">Aucune annonce ne correspond à votre recherche "<strong><?= htmlspecialchars($search) ?></strong>".</p>
    </div>
  <?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
      <?php foreach ($annonces as $a): ?>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
          <div style="height:200px; background-size:cover; background-position:center; background-image: url('https://images.unsplash.com/photo-<?= !empty($a['image_unsplash']) ? $a['image_unsplash'] : '1502672260266-1c1ef2d93688' ?>?w=600&q=80');"></div>
          <div style="padding: 20px;">
            <h3 style="font-family:'Syne'; font-size:1.2rem; margin-bottom:10px;"><?= htmlspecialchars($a['titre']) ?></h3>
            <p style="color:#637470; margin-bottom:15px; font-size:0.95rem;"><?= number_format($a['loyer'], 0, ',', ' ') ?> €/mois</p>
            <a href="annonce.php?id=<?= $a['id'] ?>" style="color:#00b894; font-weight:600; text-decoration:none;">Voir les détails →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  
</section>
 
<section class="cta-section" id="cta">
  <span class="section-tag">Rejoignez-nous</span>
  
  <?php if ($userConnecte): ?>
    <h2 class="section-title">Heureux de vous revoir !</h2>
    <p class="section-sub">Parcourez nos annonces vérifiées et trouvez le logement idéal en quelques clics.</p>
    <div class="cta-buttons" style="margin-top: 20px;">
      <a href="logout.php" class="nav-logout" style="display: inline-block; text-decoration: none;">Se déconnecter</a>
    </div>
  <?php else: ?>
    <h2 class="section-title">Prêt à trouver votre logement ?</h2>
    <p class="section-sub">Que vous soyez à la recherche d'un toit ou propriétaire souhaitant louer, SmartHome AI est fait pour vous.</p>
    <div class="cta-buttons">
      <a href="inscription.php" class="btn-primary">Inscription</a>
      <a href="connexion.php" class="btn-secondary">Connexion</a>
    </div>
  <?php endif; ?>
</section>

<footer id="footer-contact">
  <div class="footer-brand">
    <a href="#" class="nav-logo">🏠 SmartHome<span> </span></a>
    <p>La plateforme intelligente qui simplifie la recherche de logement pour les étudiants, travailleurs et étrangers.</p>
  </div>
  <div class="footer-col">
    <h4>Navigation</h4>
    <a href="#how">Comment ça marche</a>
    <a href="#why">Pourquoi nous ?</a>
    <a href="#cta">connexion/inscription</a>
  </div>
  <div class="footer-col">
    <h4>Contact</h4>
    <a href="mailto:contact@smarthome.fr">contact@smarthome.fr</a>
    <a href="#">Support 24h/24 et 7j/7</a>
    <a href="#">FAQ</a>
  </div>
</footer>
 
<div class="footer-bottom">
  © 2026 SmartHome — Tous droits réservés
</div>

<script src="script.js"></script>
</body>
</html>