<?php
// ajouter_annonce.php — formulaire pour créer une nouvelle annonce
// - accessible aux propriétaires (ou utilisateurs selon logique de l'app)
// - traite l'upload d'image, la validation des champs et l'insertion en base
// NOTE: ajoutez des vérifications supplémentaires côté serveur selon besoins.
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

// Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$erreurs = [];
$succes  = false;

// ── Récupérer les villes pour le select ─────────────────────
$villes = $pdo->query("SELECT id, nom, code_postal FROM villes ORDER BY nom")->fetchAll();

// ── Traitement du formulaire ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Champs texte
    $titre         = trim($_POST['titre'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $type_logement = trim($_POST['type_logement'] ?? '');
    $surface_m2    = (float)($_POST['surface_m2'] ?? 0);
    $nb_pieces     = (int)($_POST['nb_pieces'] ?? 1);
    $loyer         = (float)($_POST['loyer'] ?? 0);
    $depot_garantie= (float)($_POST['depot_garantie'] ?? 0);
    $ville_id      = (int)($_POST['ville_id'] ?? 0);
    $quartier      = trim($_POST['quartier'] ?? '');
    $adresse       = trim($_POST['adresse'] ?? '');
    $meuble        = isset($_POST['meuble']) ? 1 : 0;
    $charges_incluses = isset($_POST['charges_incluses']) ? 1 : 0;

    // Équipements (checkboxes)
    $ascenseur  = isset($_POST['ascenseur'])  ? 1 : 0;
    $digicode   = isset($_POST['digicode'])   ? 1 : 0;
    $gardien    = isset($_POST['gardien'])    ? 1 : 0;
    $cave       = isset($_POST['cave'])       ? 1 : 0;
    $parking    = isset($_POST['parking'])    ? 1 : 0;
    $balcon     = isset($_POST['balcon'])     ? 1 : 0;
    $fibre      = isset($_POST['fibre'])      ? 1 : 0;
    $lave_linge = isset($_POST['lave_linge']) ? 1 : 0;

    // Validations de base
    if (!$titre)         $erreurs[] = "Le titre est obligatoire.";
    if (!$description)   $erreurs[] = "La description est obligatoire.";
    if (!$type_logement) $erreurs[] = "Le type de logement est obligatoire.";
    if ($surface_m2 <= 0) $erreurs[] = "La surface doit être supérieure à 0.";
    if ($loyer <= 0)     $erreurs[] = "Le loyer doit être supérieur à 0.";
    if (!$ville_id)      $erreurs[] = "Veuillez choisir une ville.";

    // ── Gestion de l'upload photo ────────────────────────────
    $image_locale = null;

    if (!empty($_FILES['photo']['name'])) {
        $photo     = $_FILES['photo'];
        $nomFichier = $photo['name'];
        $tmpPath    = $photo['tmp_name'];
        $taille     = $photo['size'];
        $erreurUp   = $photo['error'];
        $extension  = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));

        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
        $tailleMax = 5 * 1024 * 1024; // 5 Mo

        if ($erreurUp !== UPLOAD_ERR_OK) {
            $erreurs[] = "Erreur lors de l'upload de la photo.";
        } elseif (!in_array($extension, $extensionsAutorisees)) {
            $erreurs[] = "Format de photo non autorisé (jpg, jpeg, png, webp uniquement).";
        } elseif ($taille > $tailleMax) {
            $erreurs[] = "La photo ne doit pas dépasser 5 Mo.";
        } else {
            // Utiliser le dossier image/ existant du projet
           // Utiliser le dossier image/ existant du projet
          $dossierUpload = __DIR__ . '/image/';

          // Créer le dossier automatiquement s'il n'existe pas
        if (!is_dir($dossierUpload)) {
            mkdir($dossierUpload, 0755, true);
        }

            // Nom unique pour éviter les conflits
            $nouveauNom = 'annonce_' . uniqid() . '.' . $extension;
            $destination = $dossierUpload . $nouveauNom;

            if (move_uploaded_file($tmpPath, $destination)) {
                $image_locale = 'image/' . $nouveauNom;
            } else {
                $erreurs[] = "Impossible de sauvegarder la photo. Vérifiez les permissions du dossier.";
            }
        }
    }

    // ── Insertion en base si pas d'erreurs ───────────────────
    if (empty($erreurs)) {
        $stmt = $pdo->prepare("
            INSERT INTO annonces 
            (proprietaire_id, ville_id, titre, description, type_logement, surface_m2, nb_pieces,
             loyer, depot_garantie, quartier, adresse, meuble, charges_incluses,
             ascenseur, digicode, gardien, cave, parking, balcon, fibre, lave_linge,
             image_locale, statut)
            VALUES 
            (:proprietaire_id, :ville_id, :titre, :description, :type_logement, :surface_m2, :nb_pieces,
             :loyer, :depot_garantie, :quartier, :adresse, :meuble, :charges_incluses,
             :ascenseur, :digicode, :gardien, :cave, :parking, :balcon, :fibre, :lave_linge,
             :image_locale, 'disponible')
        ");

        $stmt->execute([
            ':proprietaire_id'  => $_SESSION['user_id'],
            ':ville_id'         => $ville_id,
            ':titre'            => $titre,
            ':description'      => $description,
            ':type_logement'    => $type_logement,
            ':surface_m2'       => $surface_m2,
            ':nb_pieces'        => $nb_pieces,
            ':loyer'            => $loyer,
            ':depot_garantie'   => $depot_garantie,
            ':quartier'         => $quartier,
            ':adresse'          => $adresse,
            ':meuble'           => $meuble,
            ':charges_incluses' => $charges_incluses,
            ':ascenseur'        => $ascenseur,
            ':digicode'         => $digicode,
            ':gardien'          => $gardien,
            ':cave'             => $cave,
            ':parking'          => $parking,
            ':balcon'           => $balcon,
            ':fibre'            => $fibre,
            ':lave_linge'       => $lave_linge,
            ':image_locale'     => $image_locale,
        ]);

        $succes = true;
        $nouvelId = $pdo->lastInsertId();
        header("Location: annonce.php?id=" . $nouvelId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ajouter une annonce — SmartHome</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-container {
      max-width: 800px;
      margin: 120px auto 60px;
      padding: 0 20px;
    }
    .form-container h1 {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      color: #0d1f1c;
      margin-bottom: 2rem;
    }
    .form-card {
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      padding: 2.5rem;
      box-shadow: 0 10px 30px rgba(13,31,28,0.04);
    }
    .form-section-title {
      font-family: 'Syne', sans-serif;
      font-size: 1.2rem;
      color: #0d1f1c;
      margin: 2rem 0 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #e2f7f3;
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 1.2rem;
    }
    .form-group label {
      font-weight: 600;
      color: #0d1f1c;
      font-size: 0.95rem;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      padding: 12px 16px;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      font-size: 1rem;
      font-family: 'DM Sans', sans-serif;
      color: #0d1f1c;
      transition: border 0.2s;
      background: #f9fafb;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #00b894;
      outline: none;
      background: #fff;
    }
    .form-group textarea {
      resize: vertical;
      min-height: 120px;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    @media (max-width: 600px) {
      .form-row { grid-template-columns: 1fr; }
    }

    /* Zone d'upload photo */
    .upload-zone {
      border: 2px dashed #00b894;
      border-radius: 16px;
      padding: 2rem;
      text-align: center;
      cursor: pointer;
      background: #f0fdf9;
      transition: background 0.2s;
      position: relative;
    }
    .upload-zone:hover { background: #e0faf3; }
    .upload-zone input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }
    .upload-zone .upload-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
    .upload-zone p { color: #4a5568; margin: 0; font-size: 0.95rem; }
    .upload-zone small { color: #8a9e99; }

    /* Prévisualisation photo */
    #preview-container {
      display: none;
      margin-top: 1rem;
      text-align: center;
    }
    #preview-img {
      max-width: 100%;
      max-height: 250px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    #preview-name {
      font-size: 0.9rem;
      color: #637470;
      margin-top: 0.5rem;
    }
    #btn-suppr-photo {
      background: none;
      border: none;
      color: #e53e3e;
      cursor: pointer;
      font-size: 0.9rem;
      margin-top: 0.3rem;
      text-decoration: underline;
    }

    /* Checkboxes équipements */
    .equipements-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 12px;
    }
    .equip-check {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      cursor: pointer;
      transition: border 0.2s, background 0.2s;
      font-size: 0.95rem;
      color: #0d1f1c;
    }
    .equip-check input { accent-color: #00b894; width: 18px; height: 18px; }
    .equip-check:has(input:checked) {
      border-color: #00b894;
      background: #f0fdf9;
      font-weight: 600;
    }
    .toggle-check {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 600;
      color: #0d1f1c;
      cursor: pointer;
    }
    .toggle-check input { accent-color: #00b894; width: 18px; height: 18px; }

    /* Messages erreur / succès */
    .alert-erreur {
      background: #feecec;
      border-left: 4px solid #e53e3e;
      border-radius: 10px;
      padding: 1rem 1.5rem;
      margin-bottom: 1.5rem;
      color: #8f1b1b;
    }
    .alert-erreur ul { margin: 0; padding-left: 1.2rem; }

    /* Bouton de soumission */
    .btn-submit {
      width: 100%;
      padding: 1rem;
      background: #00b894;
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: 2rem;
      transition: background 0.3s;
      font-family: 'DM Sans', sans-serif;
      box-shadow: 0 4px 12px rgba(0,184,148,0.2);
    }
    .btn-submit:hover { background: #00a383; }

    /* Nav */
    .nav-links { display: flex; align-items: center; list-style: none; gap: 20px; }
    .nav-links a { text-decoration: none; color: #0d1f1c; font-weight: 500; }
    .nav-cta { background: #00b894; color: #fff !important; padding: 8px 18px; border-radius: 20px; font-weight: 600 !important; }
    .nav-logout { background: #feecec; color: #8f1b1b !important; padding: 8px 18px; border-radius: 20px; font-weight: 600 !important; }
    .user-welcome { font-weight: 600; color: #0d1f1c; }
  </style>
</head>
<body>

<!-- Navigation -->
<nav>
  <a href="index.php" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li><a href="index.php#how">Comment ça marche</a></li>
    <li><a href="index.php#why">Pourquoi nous ?</a></li>
    <li><span class="user-welcome">Bonjour <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?></span></li>
    <li><a href="logout.php" class="nav-logout">Se déconnecter</a></li>
  </ul>
</nav>

<main class="form-container">
  <h1>📋 Ajouter une annonce</h1>

  <!-- Affichage des erreurs -->
  <?php if (!empty($erreurs)): ?>
    <div class="alert-erreur">
      <ul>
        <?php foreach ($erreurs as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="form-card">
    <form method="post" enctype="multipart/form-data">

      <!-- ── PHOTO ─────────────────────────────────────── -->
      <p class="form-section-title">📷 Photo principale</p>
      <div class="form-group">
        <div class="upload-zone" id="upload-zone">
          <input type="file" name="photo" id="photo-input" accept=".jpg,.jpeg,.png,.webp">
          <div class="upload-icon">🖼️</div>
          <p>Cliquez ou glissez votre photo ici</p>
          <small>JPG, PNG, WEBP — 5 Mo max</small>
        </div>
        <div id="preview-container">
          <img id="preview-img" src="" alt="Aperçu">
          <p id="preview-name"></p>
          <button type="button" id="btn-suppr-photo">✕ Supprimer la photo</button>
        </div>
      </div>

      <!-- ── INFORMATIONS GENERALES ─────────────────── -->
      <p class="form-section-title">🏠 Informations générales</p>

      <div class="form-group">
        <label for="titre">Titre de l'annonce *</label>
        <input type="text" id="titre" name="titre" placeholder="Ex : Studio lumineux — Montmartre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="description">Description *</label>
        <textarea id="description" name="description" placeholder="Décrivez le logement en détail..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="type_logement">Type de logement *</label>
          <select id="type_logement" name="type_logement" required>
            <option value="">-- Choisir --</option>
            <?php foreach (['Studio','T1','T2','T3','T4+','Colocation','Chambre'] as $t): ?>
              <option value="<?= $t ?>" <?= ($_POST['type_logement'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="surface_m2">Surface (m²) *</label>
          <input type="number" id="surface_m2" name="surface_m2" min="1" placeholder="Ex : 28" value="<?= htmlspecialchars($_POST['surface_m2'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="nb_pieces">Nombre de pièces</label>
          <input type="number" id="nb_pieces" name="nb_pieces" min="1" value="<?= htmlspecialchars($_POST['nb_pieces'] ?? '1') ?>">
        </div>
        <div class="form-group" style="justify-content: flex-end; gap: 15px;">
          <label class="toggle-check">
            <input type="checkbox" name="meuble" <?= !empty($_POST['meuble']) ? 'checked' : '' ?>>
            Meublé
          </label>
          <label class="toggle-check">
            <input type="checkbox" name="charges_incluses" <?= !empty($_POST['charges_incluses']) ? 'checked' : '' ?>>
            Charges incluses
          </label>
        </div>
      </div>

      <!-- ── LOCALISATION ───────────────────────────── -->
      <p class="form-section-title">📍 Localisation</p>

      <div class="form-row">
        <div class="form-group">
          <label for="ville_id">Ville *</label>
          <select id="ville_id" name="ville_id" required>
            <option value="">-- Choisir une ville --</option>
            <?php foreach ($villes as $v): ?>
              <option value="<?= $v['id'] ?>" <?= ($_POST['ville_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($v['nom']) ?> (<?= htmlspecialchars($v['code_postal']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="quartier">Quartier</label>
          <input type="text" id="quartier" name="quartier" placeholder="Ex : Belleville" value="<?= htmlspecialchars($_POST['quartier'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="adresse">Adresse</label>
        <input type="text" id="adresse" name="adresse" placeholder="Ex : 12 rue des Lilas" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
      </div>

      <!-- ── PRIX ───────────────────────────────────── -->
      <p class="form-section-title">💶 Prix</p>

      <div class="form-row">
        <div class="form-group">
          <label for="loyer">Loyer mensuel (€) *</label>
          <input type="number" id="loyer" name="loyer" min="1" placeholder="Ex : 750" value="<?= htmlspecialchars($_POST['loyer'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label for="depot_garantie">Dépôt de garantie (€)</label>
          <input type="number" id="depot_garantie" name="depot_garantie" min="0" placeholder="Ex : 750" value="<?= htmlspecialchars($_POST['depot_garantie'] ?? '') ?>">
        </div>
      </div>

      <!-- ── EQUIPEMENTS ────────────────────────────── -->
      <p class="form-section-title">🛠️ Équipements</p>
      <div class="equipements-grid">
        <?php
        $equips = [
          'ascenseur'  => '🛗 Ascenseur',
          'digicode'   => '🔒 Digicode',
          'gardien'    => '💂 Gardien',
          'cave'       => '📦 Cave',
          'parking'    => '🚗 Parking',
          'balcon'     => '🌿 Balcon',
          'fibre'      => '⚡ Fibre Internet',
          'lave_linge' => '🧺 Lave-linge',
        ];
        foreach ($equips as $nom => $libelle):
        ?>
          <label class="equip-check">
            <input type="checkbox" name="<?= $nom ?>" <?= !empty($_POST[$nom]) ? 'checked' : '' ?>>
            <?= $libelle ?>
          </label>
        <?php endforeach; ?>
      </div>

      <!-- ── BOUTON ─────────────────────────────────── -->
      <button type="submit" class="btn-submit">✅ Publier l'annonce</button>

    </form>
  </div>
</main>

<footer id="footer-contact">
  <div class="footer-brand">
    <a href="#" class="nav-logo">🏠 SmartHome<span> </span></a>
    <p>La plateforme intelligente qui simplifie la recherche de logement.</p>
  </div>
  <div class="footer-col">
    <h4>Navigation</h4>
    <a href="index.php#how">Comment ça marche</a>
    <a href="index.php#why">Pourquoi nous ?</a>
  </div>
  <div class="footer-col">
    <h4>Contact</h4>
    <a href="mailto:contact@smarthome.fr">contact@smarthome.fr</a>
    <a href="#">Support 24h/24</a>
  </div>
</footer>
<div class="footer-bottom">© 2026 SmartHome — Tous droits réservés</div>

<script>
// ── Prévisualisation de la photo avant envoi ──────────────────
const input    = document.getElementById('photo-input');
const preview  = document.getElementById('preview-img');
const previewC = document.getElementById('preview-container');
const previewN = document.getElementById('preview-name');
const btnSuppr = document.getElementById('btn-suppr-photo');
const zone     = document.getElementById('upload-zone');

input.addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;

  // Afficher la prévisualisation
  const reader = new FileReader();
  reader.onload = e => {
    preview.src    = e.target.result;
    previewN.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' Mo)';
    previewC.style.display = 'block';
    zone.style.display = 'none';
  };
  reader.readAsDataURL(file);
});

// Bouton supprimer la photo sélectionnée
btnSuppr.addEventListener('click', function () {
  input.value    = '';
  preview.src    = '';
  previewN.textContent = '';
  previewC.style.display = 'none';
  zone.style.display = 'block';
});
</script>

</body>
</html>