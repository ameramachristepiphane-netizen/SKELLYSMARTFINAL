<?php
// Démarrage de la session pour gérer l'état d'authentification
session_start();

// Utiliser la configuration centralisée de la base de données
require_once __DIR__ . '/config.php';

// Récupération du paramètre de recherche depuis la query string (GET)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
  if (!empty($search)) {
    // Prépare une requête pour rechercher dans plusieurs colonnes (titre, quartier, description)
    $stmt = $pdo->prepare('SELECT * FROM v_annonces_disponibles WHERE titre LIKE ? OR quartier LIKE ? OR description LIKE ? ORDER BY cree_le DESC');
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $annonces = $stmt->fetchAll(); // récupère les résultats correspondants
  } else {
    // Si pas de recherche, récupère toutes les annonces disponibles
    $annonces = $pdo->query('SELECT * FROM v_annonces_disponibles ORDER BY cree_le DESC')->fetchAll();
  }
} catch (PDOException $e) {
  // En cas d'erreur de requête, retourner un tableau vide d'annonces
  $annonces = [];
}

// Booléen indiquant si l'utilisateur est connecté (présence d'un user_id en session)
$userConnecte = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<!-- Page d'accueil affichant la liste des annonces et la barre de recherche -->
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SmartHome — Trouvez votre logement intelligemment</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <!-- Feuille de style globale -->
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

    /* --- SECTION POURQUOI CHOISIR SMARTHOME --- */
.why-section {
  max-width: 1200px;
  margin: 80px auto;
  padding: 0 20px;
  font-family: 'DM Sans', sans-serif;
}

.why-container {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 50px;
  align-items: center;
}

/* Côté gauche - Texte */
.why-tag {
  color: #00b894;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 25px;
  text-transform: uppercase;
  display: block;
  margin-bottom: 10px;
}

.why-title {
  font-family: 'Syne', sans-serif;
  font-size: 2.6rem;
  font-weight: 800;
  color: #0d1f1c;
  line-height: 1.2;
  margin-bottom: 15px;
}

.why-subtitle {
  color: #637470;
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 35px;
}

/* Cartes des avantages */
.advantage-cards {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.adv-card {
  display: flex;
  gap: 15px;
  background: #fff;
  padding: 16px;        /* était 25px */
  border-radius: 16px;  /* était 20px */
  box-shadow: 0 10px 30px rgba(13,31,28,0.03);
  border: 1px solid #f0f4f3;
}

.adv-icon {
  width: 40px;          /* était 50px */
  height: 40px;         /* était 50px */
  border-radius: 10px;  /* était 14px */
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;    /* était 1.4rem */
  flex-shrink: 0;
}

.adv-content h3 {
  font-size: 1rem;      /* était 1.2rem */
  color: #0d1f1c;
  margin-bottom: 5px;
  font-weight: 600;
}

.adv-content p {
  color: #637470;
  font-size: 0.88rem;   /* était 0.95rem */
  line-height: 1.5;
}

.advantage-cards {
  display: flex;
  flex-direction: column;
  gap: 20px;            /* était 20px */
}
/* NOUVEAU - à coller à la place */
.comparison-table {
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  background: #fff;
  width: 100%;
  max-width: 550px;
}

.table-header {
  background: #2ec4a5;
  padding: 22px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.th-left {
  color: #fff;
  font-weight: 700;
  font-size: 1.05rem;
}

.th-right {
  color: #fff;
  font-weight: 700;
  font-size: 1.05rem;
}

.table-row {
  display: flex;
  align-items: center;
  padding: 18px 28px;
  border-bottom: 1px solid #f0f0f0;
  gap: 100px;
}

.table-row:last-child {
  border-bottom: none;
}

.row-label {
  flex: 4;
  font-size: 0.95rem;
  color: #1a1a2e;
  font-weight: 500;
}
.row-val {
  width: 60px;
  text-align: center;
  font-size: 1.1rem;
  font-weight: 700;
}

.row-cross { color: #e33c3c; }
.row-check { color: #2ec4a5; }

/* Responsive Mobile */
@media (max-width: 968px) {
  .why-container { grid-template-columns: 1fr; gap: 40px; }
  .why-title { font-size: 2rem; }
}

  </style>
</head>
 
<body>
 
<!-- Barre de navigation principale -->
<nav>
  <a href="index.php" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li><a href="#how">Comment ça marche</a></li>
    <li><a href="#why">Pourquoi nous ?</a></li>
    <li><a href="#footer-contact">Contacts</a></li>
    
    <?php if ($userConnecte): ?>
      <!-- Affiche le prénom si présent en session -->
      <li><span class="user-welcome">Bonjour <?= htmlspecialchars($_SESSION['prenom'] ?? 'utilisateur') ?></span></li>
      <!-- Lien de déconnexion -->
      <li><a href="logout.php" class="nav-logout">Se déconnecter</a></li>
    <?php else: ?>
      <!-- Lien vers la zone d'inscription/connexion lorsque non connecté -->
      <li><a href="#cta" class="nav-cta">Mon Compte</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- Section principale contenant la recherche et les annonces -->
<section style="max-width:1200px; margin: 120px auto 40px; padding: 0 20px;">
  
  <div style="text-align: center; margin-bottom: 40px;">
    <h2 style="font-family:'Syne'; font-size: 2.5rem; margin-bottom: 10px; color: #0d1f1c;">Annonces disponibles</h2>
    <p style="color: #637470; font-family:'DM Sans'; margin-bottom: 25px;">Trouvez le bien idéal parmi nos logements vérifiés</p>
    
    <!-- Barre de recherche GET -->
    <div class="search-container">
      <form action="index.php" method="GET" class="search-form">
        <!-- Valeur du champ préremplie avec la recherche actuelle (sécurisée) -->
        <input type="text" name="search" class="search-input" placeholder="Rechercher par ville, quartier, mot-clé..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Rechercher</button>
      </form>
      <?php if (!empty($search)): ?>
        <!-- Lien pour réinitialiser la recherche (affiche toutes les annonces) -->
        <a href="index.php" class="clear-search">❌ Réinitialiser la recherche</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Si aucune annonce trouvée, afficher un message adapté -->
  <?php if (empty($annonces)): ?>
    <div style="text-align: center; padding: 40px; background: #fff; border-radius: 20px; border: 1px dashed #e2e8f0;">
      <p style="color: #637470; font-size: 1.1rem;">Aucune annonce ne correspond à votre recherche "<strong><?= htmlspecialchars($search) ?></strong>".</p>
    </div>
  <?php else: ?>
    <!-- Affichage en grille des annonces récupérées depuis la base -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
      <?php foreach ($annonces as $a): ?>
        <!-- Carte annonce individuelle -->
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
          <!-- Image (utilise Unsplash ou image par défaut si absent) -->
          <?php
if (!empty($a['image_locale'])) {
    $imgCard = $a['image_locale'];
} elseif (!empty($a['image_unsplash'])) {
    $imgCard = 'https://images.unsplash.com/photo-' . $a['image_unsplash'] . '?w=600&q=80';
} else {
    $imgCard = 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&q=80';
}
?>
<div style="height:200px; background-size:cover; background-position:center; background-image: url('<?= htmlspecialchars($imgCard) ?>');"></div>
          <div style="padding: 20px;">
            <!-- Titre et prix (sécurisés pour éviter XSS) -->
            <h3 style="font-family:'Syne'; font-size:1.2rem; margin-bottom:10px;"><?= htmlspecialchars($a['titre']) ?></h3>
            <p style="color:#637470; margin-bottom:15px; font-size:0.95rem;"><?= number_format($a['loyer'], 0, ',', ' ') ?> €/mois</p>
            <a href="annonce.php?id=<?= $a['id'] ?>" style="color:#00b894; font-weight:600; text-decoration:none;">Voir les détails →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  
</section>
<!-- ============================================ -->
<!-- Section : Comment ça marche                 -->
<!-- A coller dans index.php avant le footer     -->
<!-- ============================================ -->

<section id="how" style="
  background: #0d2b25;
  padding: 80px 20px;
  margin: 60px 0;
">
  <div style="max-width: 1100px; margin: 0 auto;">

    <!-- En-tête de section -->
    <span style="
      background: #1a3d35;
      color: #00b894;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 6px 16px;
      border-radius: 20px;
      display: inline-block;
      margin-bottom: 20px;
    ">PROCESSUS</span>

    <h2 style="
      font-family: 'Syne', sans-serif;
      font-size: 3rem;
      font-weight: 800;
      color: #ffffff;
      margin: 0 0 15px 0;
      line-height: 1.1;
    ">Comment ça marche ?</h2>

    <p style="
      color: #7aa99a;
      font-size: 1.05rem;
      margin-bottom: 50px;
      max-width: 500px;
      line-height: 1.6;
    ">En 3 étapes simples, SmartHome vous trouve un logement adapté et vous accompagne jusqu'à l'emménagement.</p>

    <!-- Grille des 3 étapes -->
    <div style="
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
    ">

      <!-- Étape 01 -->
      <div style="
        background: #122e27;
        border: 1px solid #1e4a40;
        border-radius: 20px;
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
      ">
        <div style="
          font-family: 'Syne', sans-serif;
          font-size: 5rem;
          font-weight: 800;
          color: #1a3d35;
          position: absolute;
          top: 10px;
          left: 20px;
          line-height: 1;
          user-select: none;
        ">01</div>
        <div style="position: relative; z-index: 1; margin-top: 40px;">
          <div style="font-size: 2.5rem; margin-bottom: 15px;">🎯</div>
          <h3 style="
            font-family: 'Syne', sans-serif;
            color: #ffffff;
            font-size: 1.3rem;
            margin: 0 0 10px 0;
          ">Créez votre profil</h3>
          <p style="color: #7aa99a; font-size: 0.95rem; line-height: 1.6; margin: 0;">
            Renseignez votre budget, votre situation et vos préférences. Notre système analyse vos besoins pour trouver les meilleures offres.
          </p>
        </div>
      </div>

      <!-- Étape 02 -->
      <div style="
        background: #122e27;
        border: 1px solid #1e4a40;
        border-radius: 20px;
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
      ">
        <div style="
          font-family: 'Syne', sans-serif;
          font-size: 5rem;
          font-weight: 800;
          color: #1a3d35;
          position: absolute;
          top: 10px;
          left: 20px;
          line-height: 1;
          user-select: none;
        ">02</div>
        <div style="position: relative; z-index: 1; margin-top: 40px;">
          <div style="font-size: 2.5rem; margin-bottom: 15px;">🤝</div>
          <h3 style="
            font-family: 'Syne', sans-serif;
            color: #ffffff;
            font-size: 1.3rem;
            margin: 0 0 10px 0;
          ">Entrez en contact</h3>
          <p style="color: #7aa99a; font-size: 0.95rem; line-height: 1.6; margin: 0;">
            SmartHome vous met en relation directe avec les propriétaires vérifiés. Posez vos questions et organisez vos visites facilement.
          </p>
        </div>
      </div>

      <!-- Étape 03 -->
      <div style="
        background: #122e27;
        border: 1px solid #1e4a40;
        border-radius: 20px;
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
      ">
        <div style="
          font-family: 'Syne', sans-serif;
          font-size: 5rem;
          font-weight: 800;
          color: #1a3d35;
          position: absolute;
          top: 10px;
          left: 20px;
          line-height: 1;
          user-select: none;
        ">03</div>
        <div style="position: relative; z-index: 1; margin-top: 40px;">
          <div style="font-size: 2.5rem; margin-bottom: 15px;">🏠</div>
          <h3 style="
            font-family: 'Syne', sans-serif;
            color: #ffffff;
            font-size: 1.3rem;
            margin: 0 0 10px 0;
          ">Emménagez sereinement</h3>
          <p style="color: #7aa99a; font-size: 0.95rem; line-height: 1.6; margin: 0;">
            Une fois le logement trouvé, nos assistants vous accompagnent dans toutes les démarches administratives jusqu'à votre emménagement.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>
<section id="why" class="why-section">
  <div class="why-container">
    
    <div class="why-text-side">
      <span class="why-tag">NOS AVANTAGES</span>
      <h2 class="why-title">Pourquoi choisir SmartHome ?</h2>
      <p class="why-subtitle">Contrairement aux plateformes classiques, nous offrons un accompagnement complet et personnalisé à chaque étape.</p>
      
    <div class="advantage-cards">
      <div class="adv-card">
      <div class="adv-icon icon-lightning">⚡</div>
      <div class="adv-content">
      <h3>Gain de temps considérable</h3>
      <p>Fini les heures perdues à trier des centaines d'annonces. Nous sélectionnons uniquement ce qui correspond à votre profil.</p>
    </div>
  </div>
  
  <div class="adv-card">
    <div class="adv-icon icon-brain">🧠</div>
    <div class="adv-content">
      <h3>Recommandations intelligentes</h3>
      <p>Nous analysons votre budget, votre mode de vie et vos besoins pour des suggestions vraiment pertinentes.</p>
    </div>
  </div>

  <!-- NOUVELLES CARTES -->
  <div class="adv-card">
    <div class="adv-icon icon-shield">🛡️</div>
    <div class="adv-content">
      <h3>Annonces vérifiées</h3>
      <p>Chaque annonce est contrôlée. Aucune arnaque, aucun doublon — seulement des logements réels disponibles.</p>
    </div>
  </div>

  <div class="adv-card">
    <div class="adv-icon icon-plant">🪴</div>
    <div class="adv-content">
      <h3>Aide à l'aménagement</h3>
      <p>Après l'emménagement, nos assistants vous proposent des idées de décoration et d'optimisation de votre espace.</p>
    </div>
  </div>
</div>
    </div>
    
    <div class="why-table-side">
      <div class="comparison-table">
  <div class="table-header">
    <span class="th-left">Concurrents</span>
    <span class="th-right">SmartHome</span>
  </div>

  <div class="table-row">
    <span class="row-label">Recommandations personnalisées</span>
    <span class="row-val row-cross">✕</span>
    <span class="row-val row-check">✓</span>
  </div>

  <div class="table-row">
    <span class="row-label">Accompagnement complet</span>
    <span class="row-val row-cross">✕</span>
    <span class="row-val row-check">✓</span>
  </div>

  <div class="table-row">
    <span class="row-label">Aide à l'aménagement</span>
    <span class="row-val row-cross">✕</span>
    <span class="row-val row-check">✓</span>
  </div>

  <div class="table-row">
    <span class="row-label">Annonces vérifiées sans doublons</span>
    <span class="row-val row-cross">✕</span>
    <span class="row-val row-check">✓</span>
  </div>

  <div class="table-row">
    <span class="row-label">Profil adapté à l'étranger</span>
    <span class="row-val row-cross">✕</span>
    <span class="row-val row-check">✓</span>
  </div>

  <div class="table-row">
    <span class="row-label">Support réactif 24h/24 et 7j/7</span>
    <span class="row-val row-cross">✕</span>
    <span class="row-val row-check">✓</span>
  </div>
</div>
    </div>

  </div>
</section>
<!-- Section CTA pour inscription/connexion -->
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

<!-- Footer avec navigation et contacts -->
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
 
<!-- Bas de page -->
<div class="footer-bottom">
  © 2026 SmartHome — Tous droits réservés
</div>

</body>
</html>