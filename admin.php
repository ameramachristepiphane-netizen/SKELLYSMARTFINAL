<?php
session_start();

// ── Connexion MySQL ──────────────────────────────────────────
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

// ── Vérification : admin uniquement ─────────────────────────
if (empty($_SESSION['admin_id'])) {
    header('Location: connexion.php');
    exit;
}
$adminId = $_SESSION['admin_id'];
$adminPrenom = $_SESSION['admin_prenom'];
// ── Traitement des actions POST ──────────────────────────────
$flash = '';
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Envoyer un message
    if ($action === 'send_message') {
        $destinataire = (int)($_POST['destinataire_id'] ?? 0);
        $contenu      = trim($_POST['contenu'] ?? '');
        if ($destinataire > 0 && $contenu !== '') {
            // On réutilise la table messages (annonce_id = 0 pour messages admin)
            // On utilise annonce_id = 1 par convention pour les messages admin
            $stmt = $pdo->prepare('INSERT INTO messages (annonce_id, expediteur_id, destinataire_id, contenu) VALUES (1, ?, ?, ?)');
            $stmt->execute([$adminId, $destinataire, $contenu]);
            $flash = 'Message envoyé avec succès.';
        } else {
            $flash = 'Destinataire ou message vide.';
            $flashType = 'error';
        }
    }

    // Supprimer une annonce
    if ($action === 'delete_annonce') {
        $idA = (int)($_POST['annonce_id'] ?? 0);
        if ($idA > 0) {
            $pdo->prepare('DELETE FROM annonces WHERE id = ?')->execute([$idA]);
            $flash = 'Annonce supprimée.';
        }
    }

    // Modifier le statut d'une annonce
    if ($action === 'update_annonce') {
        $idA    = (int)($_POST['annonce_id'] ?? 0);
        $titre  = trim($_POST['titre'] ?? '');
        $loyer  = (int)($_POST['loyer'] ?? 0);
        $statut = $_POST['statut'] ?? 'disponible';
        if ($idA > 0 && $titre !== '') {
            $pdo->prepare('UPDATE annonces SET titre=?, loyer=?, statut=? WHERE id=?')
                ->execute([$titre, $loyer, $statut, $idA]);
            $flash = 'Annonce mise à jour.';
        }
    }

    // Supprimer un utilisateur
    if ($action === 'delete_user') {
        $idU = (int)($_POST['user_id'] ?? 0);
        if ($idU > 0 && $idU !== $adminId) {
            $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$idU]);
            $flash = 'Utilisateur supprimé.';
        } else {
            $flash = 'Impossible de supprimer votre propre compte.';
            $flashType = 'error';
        }
    }

    // Supprimer un message
    if ($action === 'delete_message') {
        $idM = (int)($_POST['message_id'] ?? 0);
        if ($idM > 0) {
            $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$idM]);
            $flash = 'Message supprimé.';
        }
    }
}

// ── Données pour la page ─────────────────────────────────────

// Assurer la présence du champ dernier_connexion dans la table utilisateurs
try {
    $pdo->exec('ALTER TABLE utilisateurs ADD COLUMN dernier_connexion DATETIME NULL');
} catch (PDOException $e) {
    // ignore si déjà présent
}

// Tous les utilisateurs (sauf l'admin courant)
$utilisateurs = $pdo->query('SELECT id, prenom, nom, email, telephone, role, actif, cree_le, dernier_connexion FROM utilisateurs ORDER BY cree_le DESC')->fetchAll();

// Toutes les annonces avec nom du propriétaire
$annonces = $pdo->query('
    SELECT a.id, a.titre, a.loyer, a.statut, a.type_logement, a.cree_le,
           CONCAT(u.prenom, " ", u.nom) AS proprio
    FROM annonces a
    LEFT JOIN utilisateurs u ON a.proprietaire_id = u.id
    ORDER BY a.cree_le DESC
')->fetchAll();

// Messages reçus/envoyés par l'admin
$messages = $pdo->prepare('
    SELECT m.id, m.contenu, m.envoye_le, m.lu,
           CONCAT(exp.prenom, " ", exp.nom) AS expediteur_nom,
           CONCAT(dest.prenom, " ", dest.nom) AS destinataire_nom,
           m.expediteur_id, m.destinataire_id
    FROM messages m
    LEFT JOIN utilisateurs exp  ON m.expediteur_id   = exp.id
    LEFT JOIN utilisateurs dest ON m.destinataire_id = dest.id
    WHERE m.expediteur_id = ? OR m.destinataire_id = ?
    ORDER BY m.envoye_le DESC
    LIMIT 50
');
$messages->execute([$adminId, $adminId]);
$messages = $messages->fetchAll();

// Collaborateurs (propriétaires + admins) pour le select du message
$collaborateurs = $pdo->query("SELECT id, prenom, nom, role FROM utilisateurs WHERE role IN ('proprietaire','admin') ORDER BY prenom")->fetchAll();

// Stats rapides
$nbUsers    = count($utilisateurs);
$nbAnnonces = count($annonces);
$nbMessages = count($messages);
$nbDispo    = count(array_filter($annonces, fn($a) => $a['statut'] === 'disponible'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Administration — SmartHome</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ── Layout admin ── */
    body { background: #f4f7f6; font-family: 'DM Sans', sans-serif; color: #0d1f1c; }

    .admin-wrapper {
      min-height: 100vh;
      padding-top: 70px;
      position: relative;
    }

    /* ── Sidebar ── */
    .sidebar {
      background: #0a2e2a;
      color: #fff;
      position: fixed;
      top: 70px; left: 0;
      width: 240px;
      height: calc(100vh - 70px);
      overflow-y: auto;
      padding: 1.5rem 0;
      z-index: 50;
    }

    .admin-main {
      margin-left: 240px;
      padding: 2rem;
      min-height: calc(100vh - 70px);
      overflow-x: hidden;
    }
    .sidebar-section { padding: 0 1.2rem; margin-bottom: 0.5rem; }
    .sidebar-label {
      font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
      text-transform: uppercase; color: #74d7c4;
      padding: 0.8rem 1rem 0.3rem;
      display: block;
    }
    .sidebar-link {
      display: flex; align-items: center; gap: 10px;
      padding: 0.65rem 1rem;
      border-radius: 10px;
      color: #b2d2ce;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.2s;
      cursor: pointer;
      border: none; background: none; width: 100%; text-align: left;
    }
    .sidebar-link:hover, .sidebar-link.active {
      background: rgba(116,215,196,0.15);
      color: #fff;
    }
    .sidebar-link .ico { font-size: 1.1rem; width: 22px; text-align: center; }

    /* ── Contenu principal ── */
    .admin-main {
      margin-left: 240px;
      padding: 2rem;
      min-height: calc(100vh - 70px);
    }

    /* ── Tabs ── */
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* ── Admin tables ── */
    .admin-table-wrap {
      overflow-x: auto;
      background: #fff;
      border: 1px solid #e8e8e8;
      border-radius: 18px;
      padding: 1rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.04);
      margin-bottom: 1.8rem;
    }
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 720px;
      font-size: 0.95rem;
      color: #111;
    }
    .admin-table th,
    .admin-table td {
      padding: 14px 16px;
      border-bottom: 1px solid #eef1f1;
      text-align: left;
      vertical-align: middle;
    }
    .admin-table th {
      font-weight: 700;
      color: #2f4441;
      background: #f8faf9;
    }
    .admin-table tbody tr:hover {
      background: #f7fbfa;
    }

    /* ── Cards stats ── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 2rem;
    }
    .stat-card {
      background: #fff;
      border-radius: 16px;
      padding: 1.2rem 1.5rem;
      border: 1px solid #e2e8f0;
      display: flex; align-items: center; gap: 14px;
    }
    .stat-card .stat-ico {
      width: 48px; height: 48px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
    }
    .stat-card .stat-val {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem; font-weight: 800; color: #0d1f1c; line-height: 1;
    }
    .stat-card .stat-lbl { font-size: 0.8rem; color: #8a9e99; margin-top: 3px; }

    /* ── Titres sections ── */
    .section-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 1.2rem;
    }
    .section-header h2 {
      font-family: 'Syne', sans-serif;
      font-size: 1.4rem; font-weight: 700;
    }

    /* ── Table ── */
    .admin-table-wrap {
      background: #fff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      overflow: hidden;
      margin-bottom: 2rem;
    }
    .admin-table {
      width: 100%; border-collapse: collapse;
    }
    .admin-table th {
      background: #f4f7f6;
      font-size: 11px; font-weight: 700;
      letter-spacing: 0.06em; text-transform: uppercase;
      color: #637470; padding: 10px 16px; text-align: left;
    }
    .admin-table td {
      padding: 12px 16px;
      font-size: 0.88rem;
      border-top: 1px solid #f0f4f3;
      vertical-align: middle;
    }
    .admin-table tr:hover td { background: #fafcfb; }

    /* ── Badges statut ── */
    .badge-status {
      display: inline-block;
      font-size: 11px; font-weight: 600;
      padding: 3px 10px; border-radius: 20px;
    }
    .badge-disponible { background: #d4f5ea; color: #085041; }
    .badge-loué       { background: #fde8de; color: #712B13; }
    .badge-suspendu   { background: #f0f0f0; color: #666; }
    .badge-proprietaire { background: #e8e6fd; color: #3C3489; }
    .badge-locataire  { background: #e6f1fb; color: #0C447C; }
    .badge-admin      { background: #faeeda; color: #633806; }

    /* ── Boutons action ── */
    .btn-action {
      padding: 5px 12px; border-radius: 8px;
      font-size: 12px; font-weight: 600;
      cursor: pointer; border: none;
      transition: all 0.2s; text-decoration: none;
      display: inline-block;
    }
    .btn-edit   { background: #e8e6fd; color: #3C3489; }
    .btn-edit:hover { background: #d5d2f8; }
    .btn-delete { background: #feecec; color: #8f1b1b; }
    .btn-delete:hover { background: #fcd7d7; }
    .btn-green  { background: #00b894; color: #fff; }
    .btn-green:hover { background: #00a383; }

    /* ── Flash message ── */
    .flash {
      padding: 12px 18px; border-radius: 12px;
      margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 500;
    }
    .flash.success { background: #d4f5ea; color: #085041; border: 1px solid #b9e7d6; }
    .flash.error   { background: #feecec; color: #8f1b1b; border: 1px solid #f1c1c1; }

    /* ── Messagerie ── */
    .chat-layout {
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 20px;
      background: #fff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      overflow: hidden;
      min-height: 500px;
    }
    .chat-contacts {
      border-right: 1px solid #f0f4f3;
      padding: 1rem 0;
    }
    .chat-contacts h3 {
      font-size: 13px; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.06em;
      color: #8a9e99; padding: 0 1rem 0.8rem;
    }
    .contact-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 1rem; cursor: pointer;
      transition: background 0.2s;
      border: none; background: none; width: 100%; text-align: left;
    }
    .contact-item:hover { background: #f4f7f6; }
    .contact-item.selected { background: #f0faf8; }
    .contact-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: #00b894; color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 0.9rem; flex-shrink: 0;
    }
    .contact-name { font-size: 0.88rem; font-weight: 500; }
    .contact-role { font-size: 0.75rem; color: #8a9e99; }

    .chat-area { padding: 1.2rem; display: flex; flex-direction: column; }
    .chat-area h3 {
      font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700;
      margin-bottom: 1rem; padding-bottom: 0.8rem;
      border-bottom: 1px solid #f0f4f3;
    }
    .messages-list {
      flex: 1; overflow-y: auto; max-height: 320px;
      display: flex; flex-direction: column; gap: 10px;
      margin-bottom: 1rem; padding-right: 4px;
    }
    .msg-bubble {
      max-width: 75%; padding: 10px 14px;
      border-radius: 14px; font-size: 0.88rem; line-height: 1.5;
    }
    .msg-bubble.sent {
      background: #00b894; color: #fff;
      align-self: flex-end; border-bottom-right-radius: 4px;
    }
    .msg-bubble.received {
      background: #f4f7f6; color: #0d1f1c;
      align-self: flex-start; border-bottom-left-radius: 4px;
    }
    .msg-meta { font-size: 10px; margin-top: 4px; opacity: 0.7; }

    .chat-form { display: flex; gap: 10px; }
    .chat-input {
      flex: 1; padding: 10px 14px;
      border: 1px solid #d1dbd9; border-radius: 12px;
      font-family: inherit; font-size: 0.9rem;
      outline: none; transition: border-color 0.2s;
    }
    .chat-input:focus { border-color: #00b894; }

    /* ── Modal édition annonce ── */
    .modal-overlay {
      display: none; position: fixed;
      inset: 0; background: rgba(0,0,0,0.4);
      z-index: 200; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
      background: #fff; border-radius: 20px;
      padding: 2rem; width: 100%; max-width: 480px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .modal-box h3 {
      font-family: 'Syne', sans-serif; font-size: 1.2rem; font-weight: 700;
      margin-bottom: 1.2rem;
    }
    .form-group { margin-bottom: 1rem; }
    .form-group label {
      display: block; font-size: 12px; font-weight: 600;
      color: #637470; margin-bottom: 4px; text-transform: uppercase;
    }
    .form-group input, .form-group select {
      width: 100%; padding: 9px 12px;
      border: 1px solid #d1dbd9; border-radius: 10px;
      font-family: inherit; font-size: 0.9rem; outline: none;
      transition: border-color 0.2s;
    }
    .form-group input:focus, .form-group select:focus { border-color: #00b894; }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 1.2rem; }

    /* ── Nav ── */
    .nav-links { display: flex; align-items: center; list-style: none; gap: 20px; }
    .nav-links a { text-decoration: none; color: #0d1f1c; font-weight: 500; }
    .nav-logout { background: #feecec; color: #8f1b1b !important; padding: 8px 18px; border-radius: 20px; font-weight: 600 !important; }
    .user-welcome { font-weight: 600; color: #0d1f1c; }

    @media (max-width: 900px) {
      .admin-wrapper { padding-top: 70px; }
      .sidebar { display: none; }
      .admin-main { margin-left: 0; }
      .stats-row { grid-template-columns: 1fr 1fr; }
      .chat-layout { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- Nav -->
<nav>
  <a href="index.php" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li><span class="user-welcome">👑 <?= $adminPrenom ?> — Admin</span></li>
    <li><a href="index.php">Retour au site</a></li>
    <li><a href="logout.php" class="nav-logout">Se déconnecter</a></li>
  </ul>
</nav>

<div class="admin-wrapper">

  <!-- Sidebar -->
  <aside class="sidebar">
    <span class="sidebar-label">Tableau de bord</span>
    <div class="sidebar-section">
      <button class="sidebar-link active" data-tab="dashboard" onclick="showTab('dashboard', this)">
        <span class="ico">📊</span> Vue générale
      </button>
    </div>
    <span class="sidebar-label">Gestion</span>
    <div class="sidebar-section">
      <button class="sidebar-link" data-tab="annonces" onclick="showTab('annonces', this)">
        <span class="ico">🏠</span> Annonces
      </button>
      <button class="sidebar-link" data-tab="utilisateurs" onclick="showTab('utilisateurs', this)">
        <span class="ico">👥</span> Utilisateurs
      </button>
      <button class="sidebar-link" data-tab="messagerie" onclick="showTab('messagerie', this)">
        <span class="ico">💬</span> Messagerie
      </button>
    </div>
  </aside>

  <!-- Contenu -->
  <main class="admin-main">

    <?php if ($flash): ?>
      <div class="flash <?= $flashType ?>"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <!-- ── TAB : Dashboard ── -->
    <div id="tab-dashboard" class="tab-panel active">
      <h1 style="font-family:'Syne';font-size:1.8rem;font-weight:800;margin-bottom:0.4rem;">Tableau de bord</h1>
      <p style="color:#637470;margin-bottom:1.5rem;">Bienvenue <?= $adminPrenom ?>, voici un aperçu de la plateforme.</p>

      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-ico" style="background:#f0faf8;">👥</div>
          <div>
            <div class="stat-val"><?= $nbUsers ?></div>
            <div class="stat-lbl">Utilisateurs</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-ico" style="background:#e8e6fd;">🏠</div>
          <div>
            <div class="stat-val"><?= $nbAnnonces ?></div>
            <div class="stat-lbl">Annonces</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-ico" style="background:#d4f5ea;">✅</div>
          <div>
            <div class="stat-val"><?= $nbDispo ?></div>
            <div class="stat-lbl">Disponibles</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-ico" style="background:#faeeda;">💬</div>
          <div>
            <div class="stat-val"><?= $nbMessages ?></div>
            <div class="stat-lbl">Messages</div>
          </div>
        </div>
      </div>

      <!-- Dernières annonces -->
      <div class="section-header">
        <h2>Dernières annonces</h2>
        <button class="btn-action btn-green" onclick="showTab('annonces', this)">Voir tout →</button>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>Titre</th><th>Propriétaire</th><th>Loyer</th><th>Statut</th></tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($annonces, 0, 5) as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['titre']) ?></td>
              <td><?= htmlspecialchars($a['proprio'] ?? '-') ?></td>
              <td><?= number_format($a['loyer'], 0, ',', ' ') ?> €/mois</td>
              <td><span class="badge-status badge-<?= $a['statut'] ?>"><?= ucfirst($a['statut']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Derniers utilisateurs -->
      <div class="section-header">
        <h2>Derniers inscrits</h2>
        <button class="btn-action btn-green" onclick="showTab('utilisateurs', this)">Voir tout →</button>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Dernière connexion</th><th>Inscrit le</th></tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($utilisateurs, 0, 5) as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><span class="badge-status badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
              <td><?= $u['dernier_connexion'] ? date('d/m/Y H:i', strtotime($u['dernier_connexion'])) : 'Jamais' ?></td>
              <td><?= date('d/m/Y', strtotime($u['cree_le'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── TAB : Annonces ── -->
    <div id="tab-annonces" class="tab-panel">
      <div class="section-header">
        <div>
          <h1 style="font-family:'Syne';font-size:1.8rem;font-weight:800;margin-bottom:0.2rem;">Annonces</h1>
          <p style="color:#637470;"><?= $nbAnnonces ?> annonces au total</p>
        </div>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>ID</th><th>Titre</th><th>Propriétaire</th><th>Type</th><th>Loyer</th><th>Statut</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($annonces as $a): ?>
            <tr>
              <td style="color:#8a9e99;">#<?= $a['id'] ?></td>
              <td style="font-weight:500;"><?= htmlspecialchars($a['titre']) ?></td>
              <td><?= htmlspecialchars($a['proprio'] ?? '-') ?></td>
              <td><?= htmlspecialchars($a['type_logement']) ?></td>
              <td><?= number_format($a['loyer'], 0, ',', ' ') ?> €</td>
              <td><span class="badge-status badge-<?= $a['statut'] ?>"><?= ucfirst($a['statut']) ?></span></td>
              <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <button class="btn-action btn-edit"
                  onclick="openEditModal(<?= $a['id'] ?>, '<?= addslashes(htmlspecialchars($a['titre'])) ?>', <?= $a['loyer'] ?>, '<?= $a['statut'] ?>')">
                  ✏️ Modifier
                </button>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette annonce ?')">
                  <input type="hidden" name="action" value="delete_annonce">
                  <input type="hidden" name="annonce_id" value="<?= $a['id'] ?>">
                  <button type="submit" class="btn-action btn-delete">🗑 Supprimer</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── TAB : Utilisateurs ── -->
    <div id="tab-utilisateurs" class="tab-panel">
      <div class="section-header">
        <div>
          <h1 style="font-family:'Syne';font-size:1.8rem;font-weight:800;margin-bottom:0.2rem;">Utilisateurs</h1>
          <p style="color:#637470;"><?= $nbUsers ?> comptes enregistrés</p>
        </div>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Rôle</th><th>Statut</th><th>Inscrit le</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php foreach ($utilisateurs as $u): ?>
            <tr>
              <td style="color:#8a9e99;">#<?= $u['id'] ?></td>
              <td style="font-weight:500;"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
              <td style="color:#637470;"><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
              <td><span class="badge-status badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
              <td>
                <span style="font-size:12px;color:<?= $u['actif'] ? '#085041' : '#8f1b1b' ?>;">
                  <?= $u['actif'] ? '● Actif' : '○ Inactif' ?>
                </span>
              </td>
              <td><?= $u['dernier_connexion'] ? date('d/m/Y H:i', strtotime($u['dernier_connexion'])) : 'Jamais' ?></td>
              <td><?= date('d/m/Y', strtotime($u['cree_le'])) ?></td>
              <td>
                <?php if ($u['id'] !== $adminId): ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-action btn-delete">🗑 Supprimer</button>
                </form>
                <?php else: ?>
                  <span style="font-size:12px;color:#8a9e99;">Vous</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── TAB : Messagerie ── -->
    <div id="tab-messagerie" class="tab-panel">
      <h1 style="font-family:'Syne';font-size:1.8rem;font-weight:800;margin-bottom:0.4rem;">Messagerie</h1>
      <p style="color:#637470;margin-bottom:1.5rem;">Échangez avec vos collaborateurs (propriétaires & admins).</p>

      <div class="chat-layout">
        <!-- Liste contacts -->
        <div class="chat-contacts">
          <h3>Collaborateurs</h3>
          <?php foreach ($collaborateurs as $c): ?>
            <?php if ($c['id'] === $adminId) continue; ?>
            <button class="contact-item" onclick="selectContact(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['prenom'] . ' ' . $c['nom'])) ?>', this)">
              <div class="contact-avatar"><?= strtoupper(substr($c['prenom'], 0, 1)) ?></div>
              <div>
                <div class="contact-name"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></div>
                <div class="contact-role"><?= ucfirst($c['role']) ?></div>
              </div>
            </button>
          <?php endforeach; ?>
        </div>

        <!-- Zone de chat -->
        <div class="chat-area">
          <h3 id="chat-title">Sélectionnez un collaborateur</h3>
          <div class="messages-list" id="messages-list">
            <!-- Messages filtrés par JS -->
            <?php foreach ($messages as $msg): ?>
              <div class="msg-bubble <?= $msg['expediteur_id'] == $adminId ? 'sent' : 'received' ?>"
                   data-exp="<?= $msg['expediteur_id'] ?>"
                   data-dest="<?= $msg['destinataire_id'] ?>"
                   style="display:none;">
                <?= htmlspecialchars($msg['contenu']) ?>
                <div class="msg-meta">
                  <?= $msg['expediteur_id'] == $adminId ? 'Vous' : htmlspecialchars($msg['expediteur_nom']) ?>
                  · <?= date('d/m H:i', strtotime($msg['envoye_le'])) ?>
                  <?php if ($msg['expediteur_id'] == $adminId): ?>
                    <form method="POST" style="display:inline;margin-left:6px;">
                      <input type="hidden" name="action" value="delete_message">
                      <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                      <button type="submit" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:11px;">🗑</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
            <p id="no-messages" style="color:#8a9e99;font-size:0.85rem;text-align:center;margin:auto;display:none;">
              Aucun message avec ce collaborateur.
            </p>
          </div>

          <!-- Formulaire envoi -->
          <form method="POST" class="chat-form" id="chat-form" style="display:none;">
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="destinataire_id" id="dest-id">
            <input type="text" name="contenu" class="chat-input" placeholder="Écrire un message..." required autocomplete="off">
            <button type="submit" class="btn-action btn-green" style="padding:10px 18px;">Envoyer ↗</button>
          </form>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- ── Modal édition annonce ── -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal-box">
    <h3>✏️ Modifier l'annonce</h3>
    <form method="POST">
      <input type="hidden" name="action" value="update_annonce">
      <input type="hidden" name="annonce_id" id="modal-id">
      <div class="form-group">
        <label>Titre</label>
        <input type="text" name="titre" id="modal-titre" required>
      </div>
      <div class="form-group">
        <label>Loyer (€/mois)</label>
        <input type="number" name="loyer" id="modal-loyer" min="0" required>
      </div>
      <div class="form-group">
        <label>Statut</label>
        <select name="statut" id="modal-statut">
          <option value="disponible">Disponible</option>
          <option value="loué">Loué</option>
          <option value="suspendu">Suspendu</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-action btn-delete" onclick="closeModal()">Annuler</button>
        <button type="submit" class="btn-action btn-green">💾 Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
  const adminId = <?= $adminId ?>;

  // ── Navigation tabs ──
  function showTab(name, button) {
    const target = document.getElementById('tab-' + name);
    if (!target) return;

    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));

    target.classList.add('active');

    const sidebarBtn = button && button.classList.contains('sidebar-link')
      ? button
      : document.querySelector('.sidebar-link[data-tab="' + name + '"]');

    if (sidebarBtn) {
      sidebarBtn.classList.add('active');
    }
  }

  window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sidebar-link[data-tab]').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        showTab(this.dataset.tab, this);
      });
    });

    const hash = window.location.hash.replace('#','');
    if (hash && document.getElementById('tab-' + hash)) {
      const button = document.querySelector('.sidebar-link[data-tab="' + hash + '"]');
      showTab(hash, button);
    }
  });

  // ── Modal annonce ──
  function openEditModal(id, titre, loyer, statut) {
    document.getElementById('modal-id').value    = id;
    document.getElementById('modal-titre').value = titre;
    document.getElementById('modal-loyer').value = loyer;
    document.getElementById('modal-statut').value = statut;
    document.getElementById('edit-modal').classList.add('open');
  }
  function closeModal() {
    document.getElementById('edit-modal').classList.remove('open');
  }
  document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });

  // ── Messagerie : sélection contact ──
  function selectContact(contactId, contactName, button) {
    // Mettre à jour le titre
    document.getElementById('chat-title').textContent = '💬 ' + contactName;
    document.getElementById('dest-id').value = contactId;
    document.getElementById('chat-form').style.display = 'flex';

    // Mettre en surbrillance le contact sélectionné
    document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('selected'));
    if (button && button.classList) {
      button.classList.add('selected');
    }

    // Afficher les messages liés à ce contact
    let hasMessages = false;
    document.querySelectorAll('.msg-bubble').forEach(function(el) {
      const exp  = parseInt(el.dataset.exp);
      const dest = parseInt(el.dataset.dest);
      const show = (exp === adminId && dest === contactId) || (exp === contactId && dest === adminId);
      el.style.display = show ? 'flex' : 'none';
      if (show) { hasMessages = true; el.style.flexDirection = 'column'; }
    });

    const noMsg = document.getElementById('no-messages');
    noMsg.style.display = hasMessages ? 'none' : 'block';

    // Scroll en bas
    const list = document.getElementById('messages-list');
    list.scrollTop = list.scrollHeight;
  }
</script>

</body>
</html>