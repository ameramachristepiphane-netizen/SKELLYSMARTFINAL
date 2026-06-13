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
    $annonces = $pdo->query('SELECT * FROM v_annonces_disponibles ORDER BY cree_le DESC')->fetchAll();
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
  <title>SmartHome  — Trouvez votre logement intelligemment</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css">
</head>
 
<body>
 
<nav>
  <a href="#" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li class="has-dropdown">
      
      </div>
    </li>
    <li class="has-dropdown">
      <a href="#how">Comment ça marche</a>
    </li>
    <li class="has-dropdown">
      <a href="#why">Pourquoi nous ?</a>
    </li>
    <li class="has-dropdown">
      <a href="#footer-contact">Contacts</a>
    </li>
    <li><a href="#cta" class="nav-cta">connexion/inscription</a></li>
  </ul>
</nav>
 
<section class="hero">
  <div class="hero-content">
    <h1>Trouvez votre <em>logement idéal</em> en toute simplicité</h1>
    <p>SmartHome accompagne les étudiants, travailleurs et étrangers de la recherche jusqu'à l'aménagement. Budget, localisation, style de vie — on s'occupe de tout.</p>
    <form class="search-box" action="#" method="GET">
      <span>📍</span>
      <input type="text" placeholder="Ville, quartier ou code postal..." aria-label="Recherche de ville ou quartier"/>
      <button type="submit" class="search-btn">Rechercher</button>
    </form>
    <div class="hero-tags">
      <span class="hero-tag">🎓 Étudiant</span>
    </div>
  </div>
 
  <div class="hero-visual">
    <div class="hero-card">
      <div class="hero-card-header">
        
    </div>
    <div class="hero-card">
      <div class="stat-row">
        <div class="stat-item">
          <span class="stat-num"> 1900+</span>
          <span class="stat-lbl">Annonces vérifiées</span>
        </div>
        <div class="stat-item">
          <span class="stat-num">98%</span>
          <span class="stat-lbl">Satisfaction</span>
        </div>
        <div class="stat-item">
          <span class="stat-num">72h</span>
          <span class="stat-lbl">Délai moyen</span>
        </div>
      </div>
    </div>
  </div>
</section>
 
<!-- ✅ SECTION ANNONCES — depuis MySQL -->
<section class="types-section" id="types">
  <span class="section-tag">Tous types de logement</span>
  <h2 class="section-title">ANNONCES</h2>
  <div class="types-grid" id="annonces-grid">
    <?php foreach ($annonces as $a):
      $img     = $a['image_unsplash']
               ? 'https://images.unsplash.com/photo-' . $a['image_unsplash'] . '?w=600&q=80'
               : 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&q=80';
      $badge   = $a['badge'] ?? '';
      $meuble  = $a['meuble'] ? 'Meublé' : 'Non meublé';
      $charges = $a['charges_incluses'] ? ' CC' : ' HC';
    ?>
      <div class="type-card">
        <div class="type-img" style="background-image:url('<?= htmlspecialchars($img) ?>');background-size:cover;background-position:center;height:180px;border-radius:16px 16px 0 0;position:relative;">
          <?php if ($badge): ?>
            <span style="position:absolute;top:10px;left:10px;background:#00b894;color:#fff;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;"><?= htmlspecialchars($badge) ?></span>
          <?php endif; ?>
          <?php if ($a['verifie']): ?>
            <span style="position:absolute;top:10px;right:10px;background:#fff;color:#00b894;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;">✓ Vérifié</span>
          <?php endif; ?>
        </div>
        <div style="padding:1rem;">
          <h3 style="margin:0 0 0.3rem;font-size:1rem;color:#0d1f1c;"><?= htmlspecialchars($a['titre']) ?></h3>
          <p style="margin:0 0 0.5rem;font-size:0.85rem;color:#637470;">📍 <?= htmlspecialchars($a['quartier'] ?? $a['ville'] ?? '') ?></p>
          <p style="margin:0 0 0.8rem;font-size:0.82rem;color:#8a9e99;"><?= htmlspecialchars($a['surface_m2']) ?>m² · <?= $meuble ?> · <?= htmlspecialchars($a['type_logement']) ?></p>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <strong style="color:#00b894;font-size:1.05rem;"><?= number_format($a['loyer'], 0, ',', ' ') ?> €<span style="font-size:0.8rem;color:#8a9e99;">/mois<?= $charges ?></span></strong>
            <a href="<?= $userConnecte ? 'annonce.php?id='.$a['id'] : 'connexion.php' ?>" style="background:#00b894;color:#fff;padding:0.4rem 0.9rem;border-radius:10px;font-size:0.85rem;font-weight:600;text-decoration:none;">Voir</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
 
<section class="how-section" id="how">
  <span class="section-tag">Processus</span>
  <h2 class="section-title">Comment ça marche ?</h2>
  <p class="section-sub">En 3 étapes simples, SmartHome vous trouve un logement adapté et vous accompagne jusqu'à l'emménagement.</p>
  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">01</div>
      <span class="step-icon">🎯</span>
      <div class="step-title">Créez votre profil</div>
      <p class="step-desc">Renseignez votre budget, votre situation (étudiant, travailleur, étranger) et vos préférences. Nous analysons votre profil pour des recommandations personnalisées.</p>
    </div>
    <div class="step-card">
      <div class="step-num">02</div>
      <span class="step-icon">🤝</span>
      <div class="step-title">Entrez en contact</div>
      <p class="step-desc">SmartHome vous met en relation directement avec les propriétaires. Planifiez des visites, posez vos questions, tout se passe sur la plateforme.</p>
    </div>
    <div class="step-card">
      <div class="step-num">03</div>
      <span class="step-icon">🏠</span>
      <div class="step-title">Emménagez sereinement</div>
      <p class="step-desc">Une fois le logement trouvé, nos assistants vous aident à l'aménager intelligemment selon votre espace et votre budget de décoration.</p>
    </div>
  </div>
</section>
 
<section class="why-section" id="why">
  <div>
    <span class="section-tag">Nos avantages</span>
    <h2 class="section-title">Pourquoi choisir SmartHome ?</h2>
    <p class="section-sub">Contrairement aux plateformes classiques, nous offrons un accompagnement complet et personnalisé à chaque étape.</p>
    <div class="why-list">
      <div class="why-item">
        <div class="why-icon">⚡</div>
        <div class="why-text">
          <strong>Gain de temps considérable</strong>
          <span>Fini les heures perdues à trier des centaines d'annonces. Nous sélectionnons uniquement ce qui correspond à votre profil.</span>
        </div>
      </div>
      <div class="why-item">
        <div class="why-icon">🧠</div>
        <div class="why-text">
          <strong>Recommandations intelligentes</strong>
          <span>Nous analysons votre budget, votre mode de vie et vos besoins pour des suggestions vraiment pertinentes.</span>
        </div>
      </div>
      <div class="why-item">
        <div class="why-icon">🛡️</div>
        <div class="why-text">
          <strong>Annonces vérifiées</strong>
          <span>Chaque annonce est contrôlée. Aucune arnaque, aucun doublon — seulement des logements réels disponibles.</span>
        </div>
      </div>
      <div class="why-item">
        <div class="why-icon">🪴</div>
        <div class="why-text">
          <strong>Aide à l'aménagement</strong>
          <span>Après l'emménagement, nos assistants vous proposent des idées de décoration et d'optimisation de votre espace.</span>
        </div>
      </div>
    </div>
  </div>
  <div>
    <div class="compare-card">
      <div class="compare-header">
        <span>Concurrents</span>
        <strong>SmartHome</strong>
      </div>
      <div class="compare-row">
        <span class="feature">Recommandations personnalisées</span>
        <span class="them">✗</span><span class="us">✓</span>
      </div>
      <div class="compare-row">
        <span class="feature">Accompagnement complet</span>
        <span class="them">✗</span><span class="us">✓</span>
      </div>
      <div class="compare-row">
        <span class="feature">Aide à l'aménagement</span>
        <span class="them">✗</span><span class="us">✓</span>
      </div>
      <div class="compare-row">
        <span class="feature">Annonces vérifiées sans doublons</span>
        <span class="them">✗</span><span class="us">✓</span>
      </div>
      <div class="compare-row">
        <span class="feature">Profil adapté à l'étranger</span>
        <span class="them">✗</span><span class="us">✓</span>
      </div>
      <div class="compare-row">
        <span class="feature">Support réactif 24h/24 et 7j/7</span>
        <span class="them">✗</span><span class="us">✓</span>
      </div>
    </div>
  </div>
</section>
 
<section class="cta-section" id="cta">
  <span class="section-tag">Rejoignez-nous</span>
  <h2 class="section-title">Prêt à trouver votre logement ?</h2>
  <p class="section-sub">Que vous soyez à la recherche d'un toit ou propriétaire souhaitant louer, SmartHome AI est fait pour vous.</p>
  <div class="cta-buttons">
    <a href="inscription.php" class="btn-primary">Inscription</a>
    <a href="connexion.php" class="btn-secondary">Connexion</a>
  </div>
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
