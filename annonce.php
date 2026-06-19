<?php
// annonce.php — page de détail d'une annonce
// - affiche les informations complètes pour l'annonce passée via GET id
// - affiche le propriétaire, les photos, la description et les contacts
// - gère éventuellement l'envoi de message au propriétaire
// Démarre la session pour gérer l'état utilisateur
session_start();

// ── Connexion MySQL (PDO) ────────────────────────────────────
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

// Récupère l'ID de l'annonce passée en paramètre GET (cast en int pour sécurité)
$idAnnonce = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Si l'ID n'est pas valide, redirige vers la page d'accueil
if ($idAnnonce <= 0) {
    header('Location: index.php');
    exit;
}

// Prépare et exécute la requête pour récupérer les détails de l'annonce
$stmt = $pdo->prepare('
    SELECT a.*, v.nom AS ville_nom, v.code_postal, CONCAT(u.prenom, " ", u.nom) AS proprietaire_nom, u.telephone AS proprietaire_telephone
    FROM annonces a
    LEFT JOIN villes v ON a.ville_id = v.id
    LEFT JOIN utilisateurs u ON a.proprietaire_id = u.id
    WHERE a.id = ? AND a.statut = "disponible"
');
$stmt->execute([$idAnnonce]);
$annonce = $stmt->fetch();

// Si l'annonce n'existe pas ou n'est pas disponible, stopper l'exécution
if (!$annonce) {
    die("Annonce introuvable ou indisponible.");
}

if (!empty($annonce['image_locale']) && file_exists(__DIR__ . '/' . $annonce['image_locale'])) {
    $imgPrincipal = $annonce['image_locale'];
} elseif (!empty($annonce['image_unsplash'])) {
    $imgPrincipal = 'https://images.unsplash.com/photo-' . $annonce['image_unsplash'] . '?w=1200&q=80';
} else {
    $imgPrincipal = 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1200&q=80';
}

// Formatage rapide de quelques labels pour l'affichage
$meuble  = $annonce['meuble'] ? 'Meublé' : 'Non meublé';
$charges = $annonce['charges_incluses'] ? 'Charges comprises' : 'Hors charges';
$userConnecte = !empty($_SESSION['user_id']);

// Récupérer les favoris de l'utilisateur connecté
$isLiked = false;
if ($userConnecte) {
    $stmt = $pdo->prepare('SELECT id FROM favoris WHERE utilisateur_id = ? AND annonce_id = ?');
    $stmt->execute([$_SESSION['user_id'], $idAnnonce]);
    $isLiked = (bool)$stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <!-- Titre dynamique selon le titre de l'annonce -->
  <title><?= htmlspecialchars($annonce['titre']) ?> — SmartHome</title>
  <!-- Google Fonts et styles globaux -->
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css">
  
  <style>
    /* Styles harmonisés pour la navigation */
    .nav-links { display: flex; align-items: center; list-style: none; gap: 20px; }
    .nav-links a { text-decoration: none; color: #0d1f1c; font-weight: 500; }
    .nav-cta { background: #00b894; color: #fff !important; padding: 8px 18px; border-radius: 20px; font-weight: 600 !important; }
    .nav-logout { background: #feecec; color: #8f1b1b !important; padding: 8px 18px; border-radius: 20px; font-weight: 600 !important; transition: background 0.3s ease; }
    .nav-logout:hover { background: #fcd7d7; }
    .user-welcome { font-weight: 600; color: #0d1f1c; }

    /* Conteneur principal de l'annonce (grille 2 colonnes) */
    .annonce-container {
      max-width: 1200px;
      margin: 120px auto 60px;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 40px;
    }
    @media (max-width: 968px) {
      .annonce-container { grid-template-columns: 1fr; margin-top: 90px; }
    }

    /* Image principale de l'annonce */
    .annonce-main-img {
      width: 100%;
      height: 450px;
      border-radius: 24px;
      background-size: cover;
      background-position: center;
      position: relative;
      margin-bottom: 2rem;
    }
    .annonce-badges { position: absolute; top: 20px; left: 20px; display: flex; gap: 10px; }
    .badge-item { padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; }
    .badge-green { background: #00b894; color: #fff; }
    .badge-white { background: #fff; color: #00b894; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    
    /* En-tête de l'annonce : titre et localisation */
    .annonce-header h1 { font-family: 'Syne', sans-serif; font-size: 2.5rem; color: #0d1f1c; margin-bottom: 0.5rem; }
    .annonce-location { font-size: 1.1rem; color: #637470; margin-bottom: 1.5rem; }

    /* Grille de métadonnées (surface, pièces, type, etc.) */
    .annonce-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 20px; margin-bottom: 2rem; padding: 1.5rem; background: #f4f7f6; border-radius: 16px; }
    .meta-item { display: flex; flex-direction: column; gap: 5px; }
    .meta-label { font-size: 0.85rem; color: #8a9e99; text-transform: uppercase; letter-spacing: 0.5px; }
    .meta-value { font-size: 1.1rem; font-weight: 600; color: #0d1f1c; }

    /* Sections descriptives et équipements */
    .annonce-section { margin-bottom: 2.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 2rem; }
    .annonce-section h2 { font-family: 'Syne', sans-serif; font-size: 1.6rem; color: #0d1f1c; margin-bottom: 1rem; }
    .annonce-section p { color: #4a5568; line-height: 1.7; font-size: 1.05rem; }

    .equipements-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
    .equipement-item { display: flex; align-items: center; gap: 10px; color: #4a5568; }
    .equipement-item.checked { color: #0d1f1c; font-weight: 500; }
    .equipement-item.unchecked { color: #a0aec0; text-decoration: line-through; }

    /* Carte latérale (prix, propriétaire, contact) */
    .sidebar-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 2rem; position: sticky; top: 110px; box-shadow: 0 10px 30px rgba(13,31,28,0.04); height: fit-content; }
    .sidebar-price { margin-bottom: 1.5rem; }
    .sidebar-price strong { font-size: 2.2rem; color: #00b894; display: block; }
    .sidebar-price span { color: #8a9e99; font-size: 1rem; font-weight: normal; }

    .sidebar-owner { display: flex; align-items: center; gap: 15px; padding: 1rem 0; border-top: 1px solid #f0f4f3; border-bottom: 1px solid #f0f4f3; margin-bottom: 1.5rem; }
    .owner-avatar { width: 45px; height: 45px; background: #00b894; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; }

    .btn-contact { display: block; width: 100%; text-align: center; background: #00b894; color: #fff; padding: 1rem; border-radius: 12px; font-weight: 600; text-decoration: none; transition: background 0.3s ease; box-shadow: 0 4px 12px rgba(0, 184, 148, 0.2); }
    .btn-contact:hover { background: #00a383; }

    /* Bouton like/favoris */
    .like-btn {
      background: rgba(255,255,255,0.92);
      border: none;
      border-radius: 50%;
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transition: transform 0.2s ease, background 0.2s ease;
      z-index: 10;
    }
    .like-btn:hover {
      transform: scale(1.15);
    }
    .like-btn.liked {
      background: #fff0f3;
    }
  </style>
</head>
<body>
 
<!-- Barre de navigation principale -->
<nav>
  <a href="index.php" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li><a href="index.php#how">Comment ça marche</a></li>
    <li><a href="index.php#why">Pourquoi nous ?</a></li>
    <li><a href="index.php#footer-contact">Contacts</a></li>
    
    <?php if ($userConnecte): ?>
      <!-- Affiche le prénom de l'utilisateur s'il est connecté -->
      <li><span class="user-welcome">Bonjour <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?></span></li>
      <li><a href="favoris.php" style="color: #00b894; font-weight: 600;">❤️ Mes favoris</a></li>
      <li><a href="profil.php" style="color: #00b894; font-weight: 600;">Mon Profil</a></li>
      <li><a href="logout.php" class="nav-logout">Se déconnecter</a></li>
    <?php else: ?>
      <li><a href="connexion.php" class="nav-cta">Mon Compte</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- Contenu principal: détails de l'annonce et sidebar -->
<main class="annonce-container">
  <div>
    <!-- Image principale avec badges superposés -->
    <div class="annonce-main-img" style="background-image: url('<?= htmlspecialchars($imgPrincipal) ?>');">
      <div class="annonce-badges">
        <?php if (!empty($annonce['badge'])): ?>
          <span class="badge-item badge-green"><?= htmlspecialchars($annonce['badge']) ?></span>
        <?php endif; ?>
        <?php if ($annonce['verifie']): ?>
          <span class="badge-item badge-white">✓ Vérifié par SmartHome</span>
        <?php endif; ?>
      </div>
      <?php if ($userConnecte): ?>
        <button class="like-btn <?= $isLiked ? 'liked' : '' ?>"
                onclick="toggleLike(this, <?= $idAnnonce ?>)"
                title="<?= $isLiked ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>"
                style="position: absolute; top: 20px; right: 20px;">
          <?= $isLiked ? '❤️' : '🤍' ?>
        </button>
      <?php endif; ?>
    </div>

    <!-- Titre et localisation -->
    <div class="annonce-header">
      <h1><?= htmlspecialchars($annonce['titre']) ?></h1>
      <p class="annonce-location">📍 <?= htmlspecialchars($annonce['quartier'] ?? '') ?> — <?= htmlspecialchars($annonce['ville_nom'] ?? '') ?> (<?= htmlspecialchars($annonce['code_postal'] ?? '') ?>)</p>
    </div>

    <!-- Métadonnées (surface, type, mobilier, pièces) -->
    <div class="annonce-meta-grid">
      <div class="meta-item">
        <span class="meta-label">Surface</span>
        <span class="meta-value"><?= htmlspecialchars($annonce['surface_m2']) ?> m²</span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Type</span>
        <span class="meta-value"><?= htmlspecialchars($annonce['type_logement']) ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Mobilier</span>
        <span class="meta-value"><?= $meuble ?></span>
      </div>
      <div class="meta-item">
        <span class="meta-label">Pièces</span>
        <span class="meta-value"><?= htmlspecialchars($annonce['nb_pieces'] ?? 1) ?></span>
      </div>
    </div>

    <!-- Description détaillée -->
    <div class="annonce-section">
      <h2>Description du bien</h2>
      <p><?= nl2br(htmlspecialchars($annonce['description'])) ?></p>
    </div>

    <!-- Liste des équipements avec indication présence/absence -->
    <div class="annonce-section">
      <h2>Équipements & Caractéristiques</h2>
      <div class="equipements-list">
        <?php
       $equipements = [
    'ascenseur'   => '🛗 Ascenseur',
    'digicode'    => '🔒 Digicode / Code d\'accès',
    'gardien'     => '💂 Gardien de l\'immeuble',
    'cave'        => '📦 Cave privative',
    'parking'     => '🚗 Place de parking',
    'balcon'      => '🌿 Balcon / Extérieur',
    'fibre'       => '⚡ Connexion Fibre Internet',
    'lave_linge'  => '🧺 Lave-linge inclus'
];

// Équipements toujours inclus pour Chambre et Colocation
$toujours_inclus = ['ascenseur', 'digicode', 'fibre', 'lave_linge'];
$est_chambre_coloc = in_array($annonce['type_logement'], ['Chambre', 'Colocation', 'T1']);

foreach ($equipements as $cle => $libelle):
  $present = !empty($annonce[$cle]);
  
  // Pour Chambre/Colocation, certains équipements sont toujours considérés présents
  if ($est_chambre_coloc && in_array($cle, $toujours_inclus)) {
      $present = true;
  }
?>
  <div class="equipement-item <?= $present ? 'checked' : 'unchecked' ?>">
    <?= $present ? '✓' : '✗' ?> <?= $libelle ?>
  </div>
<?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar contenant le prix, propriétaire et bouton de contact -->
  <aside>
    <div class="sidebar-card">
      <div class="sidebar-price">
        <strong><?= number_format($annonce['loyer'], 0, ',', ' ') ?> €<span>/mois</span></strong>
        <span><?= $charges ?> (Dépôt de garantie : <?= number_format($annonce['depot_garantie'] ?? $annonce['loyer'], 0, ',', ' ') ?> €)</span>
      </div>

      <div class="sidebar-owner">
        <div class="owner-avatar">
          <?= strtoupper(substr($annonce['proprietaire_nom'] ?? 'P', 0, 1)) ?>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: #8a9e99; display:block;">Propriétaire</span>
          <strong style="color: #0d1f1c;"><?= htmlspecialchars($annonce['proprietaire_nom'] ?? 'Non renseigné') ?></strong>
        </div>
      </div>

      <!-- Numéro de téléphone du propriétaire -->
      <?php if ($userConnecte): ?>
        <?php if (!empty($annonce['proprietaire_telephone'])): ?>
          <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $annonce['proprietaire_telephone'])) ?>" class="btn-contact">
            📞 <?= htmlspecialchars($annonce['proprietaire_telephone']) ?>
          </a>
        <?php else: ?>
          <p style="text-align:center; color:#8a9e99; font-size:0.95rem;">Numéro non disponible</p>
        <?php endif; ?>
      <?php else: ?>
        <a href="connexion.php" class="btn-contact">Se connecter pour voir le numéro</a>
      <?php endif; ?>
    </div>
  </aside>
</main>

<!-- Footer et infos de contact -->
<footer id="footer-contact">
  <div class="footer-brand">
    <a href="#" class="nav-logo">🏠 SmartHome<span> </span></a>
    <p>La plateforme intelligente qui simplifie la recherche de logement pour les étudiants, travailleurs et étrangers.</p>
  </div>
  <div class="footer-col">
    <h4>Navigation</h4>
    <a href="index.php#how">Comment ça marche</a>
    <a href="index.php#why">Pourquoi nous ?</a>
    <a href="index.php#cta">connexion/inscription</a>
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

</body>
<script>
function toggleLike(button, annonceId) {
    const isLiked = button.classList.contains('liked');
    const action = isLiked ? 'unlike' : 'like';
    
    const formData = new FormData();
    formData.append('action', action);
    formData.append('annonce_id', annonceId);
    
    fetch('like.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.liked) {
                button.classList.add('liked');
                button.textContent = '❤️';
                button.title = 'Retirer des favoris';
            } else {
                button.classList.remove('liked');
                button.textContent = '🤍';
                button.title = 'Ajouter aux favoris';
            }
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour du favori');
    });
}
</script>
</html>