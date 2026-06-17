<?php
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
$error = '';

// Récupérer les données actuelles de l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = ?');
$stmt->execute([$userId]);
$userData = $stmt->fetch();

if (!$userData) {
    header('Location: connexion.php');
    exit;
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $currentPassword = trim($_POST['current_password'] ?? '');

    // Validations
    if (!$prenom || !$nom || !$email) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        // Si changement de mot de passe
        if (!empty($newPassword)) {
            if (!password_verify($currentPassword, $userData['mot_de_passe'])) {
                $error = 'Le mot de passe actuel est incorrect.';
            } elseif (strlen($newPassword) < 8) {
                $error = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE utilisateurs SET prenom = ?, nom = ?, email = ?, telephone = ?, mot_de_passe = ? WHERE id = ?');
                $stmt->execute([$prenom, $nom, $email, $telephone, $hashedPassword, $userId]);
                $_SESSION['prenom'] = $prenom;
                $_SESSION['nom'] = $nom;
                $message = 'Profil mis à jour avec succès.';
                $userData['prenom'] = $prenom;
                $userData['nom'] = $nom;
                $userData['email'] = $email;
                $userData['telephone'] = $telephone;
            }
        } else {
            // Mise à jour sans changement de mot de passe
            $stmt = $pdo->prepare('UPDATE utilisateurs SET prenom = ?, nom = ?, email = ?, telephone = ? WHERE id = ?');
            $stmt->execute([$prenom, $nom, $email, $telephone, $userId]);
            $_SESSION['prenom'] = $prenom;
            $_SESSION['nom'] = $nom;
            $message = 'Profil mis à jour avec succès.';
            $userData['prenom'] = $prenom;
            $userData['nom'] = $nom;
            $userData['email'] = $email;
            $userData['telephone'] = $telephone;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Profil — SmartHome</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'DM Sans', sans-serif;
      background-color: #f4f7f6;
      color: #0d1f1c;
    }
    .profil-container {
      max-width: 600px;
      margin: 120px auto 60px;
      padding: 0 20px;
      background: #fff;
      border-radius: 24px;
      border: 1px solid #e2e8f0;
      padding: 2.5rem;
      box-shadow: 0 10px 30px rgba(13,31,28,0.04);
    }
    .profil-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    .profil-header h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .profil-header p {
      color: #637470;
      font-size: 0.95rem;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.4rem;
      color: #42504d;
      font-size: 0.9rem;
    }
    input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
      width: 100%;
      padding: 0.85rem 1rem;
      border: 1px solid #d1dbd9;
      border-radius: 12px;
      font-size: 1rem;
      font-family: inherit;
      color: #0d1f1c;
      box-sizing: border-box;
      transition: border-color 0.3s ease;
    }
    input:focus {
      outline: none;
      border-color: #00b894;
    }
    .alert {
      padding: 0.95rem 1rem;
      border-radius: 14px;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
    }
    .alert-error {
      background: #feecec;
      color: #8f1b1b;
      border: 1px solid #f1c1c1;
    }
    .alert-success {
      background: #d4f5ea;
      color: #085041;
      border: 1px solid #b9e7d6;
    }
    .section-title {
      font-family: 'Syne', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      margin-top: 2rem;
      margin-bottom: 1rem;
      padding-bottom: 0.8rem;
      border-bottom: 1px solid #e2e8f0;
      color: #0d1f1c;
    }
    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 2rem;
    }
    .btn-submit {
      flex: 1;
      background: #00b894;
      color: #fff;
      border: none;
      padding: 1rem;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .btn-submit:hover {
      background: #00a383;
    }
    .btn-back {
      flex: 1;
      background: #f4f7f6;
      color: #0d1f1c;
      border: 1px solid #d1dbd9;
      padding: 1rem;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.3s ease;
    }
    .btn-back:hover {
      background: #e2e8f0;
    }
  </style>
</head>
<body>

<!-- Barre de navigation -->
<nav>
  <a href="index.php" class="nav-logo">🏠 Smart<span>Home</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Accueil</a></li>
    <li><span class="user-welcome">Bonjour <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?></span></li>
    <li><a href="profil.php" style="color: #00b894; font-weight: 600;">Mon Profil</a></li>
    <li><a href="logout.php" class="nav-logout">Se déconnecter</a></li>
  </ul>
</nav>

<div class="profil-container">
  <div class="profil-header">
    <h1>Mon Profil</h1>
    <p>Modifiez vos informations personnelles</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form action="profil.php" method="POST">
    <!-- Informations personnelles -->
    <div class="form-group">
      <label for="prenom">Prénom :</label>
      <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($userData['prenom']) ?>" required>
    </div>

    <div class="form-group">
      <label for="nom">Nom :</label>
      <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($userData['nom']) ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email :</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
    </div>

    <div class="form-group">
      <label for="telephone">Téléphone :</label>
      <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>">
    </div>

    <!-- Changement de mot de passe (optionnel) -->
    <h2 class="section-title">Modifier votre mot de passe</h2>
    <p style="color: #637470; font-size: 0.9rem; margin-bottom: 1rem;">Laissez vide si vous ne souhaitez pas le modifier</p>

    <div class="form-group">
      <label for="current_password">Mot de passe actuel :</label>
      <input type="password" id="current_password" name="current_password" placeholder="••••••••">
    </div>

    <div class="form-group">
      <label for="new_password">Nouveau mot de passe :</label>
      <input type="password" id="new_password" name="new_password" placeholder="8 caractères minimum">
    </div>

    <div class="form-group">
      <label for="confirm_password">Confirmer le mot de passe :</label>
      <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••">
    </div>

    <div class="button-group">
      <button type="submit" class="btn-submit">Enregistrer les modifications</button>
      <a href="index.php" class="btn-back">Retour</a>
    </div>
  </form>
</div>

</body>
</html>
