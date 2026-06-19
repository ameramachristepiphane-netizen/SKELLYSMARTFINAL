<?php
// connexion.php — page d'authentification
// Gère :
// - la connexion des utilisateurs
// - la détection d'un administrateur (redirige vers admin.php)
// - la mise en place des variables de session (`user_id`, `prenom`, `nom`, `role`)
// Important : accepte des mots de passe hachés (password_verify) ou en clair
session_start();

$error = '';

// Redirection si déjà authentifié
if (!empty($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Traitement du formulaire de connexion (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Veuillez renseigner votre email et votre mot de passe.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
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
            die('Erreur de connexion à la base de données.');
        }

        // Recherche dans la table `utilisateurs`
        $stmt = $pdo->prepare('SELECT id, prenom, nom, mot_de_passe, role, actif FROM utilisateurs WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $userData = $stmt->fetch();

        $authenticated = false;
        $isAdmin = false;

        // Vérification sur la table utilisateurs (attendue normalement)
        if ($userData) {
          if (password_verify($password, $userData['mot_de_passe'])) {
            if (empty($userData['actif'])) {
              $error = 'Votre compte est inactif. Veuillez contacter l\'administrateur.';
            } else {
              $authenticated = true;
              $isAdmin = ($userData['role'] === 'admin');
            }
          }
        }

        // Si pas authentifié via `utilisateurs`, tenter sur la table `administrateur`
        if (!$authenticated) {
          // Certains anciens admins pouvaient avoir des mots de passe non hachés
          $stmt2 = $pdo->prepare('SELECT id_admin AS id, prenom, nom, `mot de passe` AS mot_de_passe, role FROM administrateur WHERE email = ? LIMIT 1');
          $stmt2->execute([$email]);
          $adminData = $stmt2->fetch();

          if ($adminData) {
            // La table `administrateur` peut contenir des mots de passe en clair
            // ou hachés. On autorise les deux formats.
            if (password_verify($password, $adminData['mot_de_passe']) || $password === $adminData['mot_de_passe']) {
              $authenticated = true;
              $isAdmin = true;
              // map pour usage uniforme
              $userData = [
                'id' => $adminData['id'],
                'prenom' => $adminData['prenom'],
                'nom' => $adminData['nom'],
                'role' => 'admin'
              ];
            }
          }
        }

        if (!$authenticated) {
          $error = 'Email ou mot de passe incorrect.';
        } else {
          // Renseigner la session avec les données utilisateur
          $_SESSION['user_id'] = $userData['id'];
          $_SESSION['prenom']  = $userData['prenom'];
          $_SESSION['nom']     = $userData['nom'];
          $_SESSION['role']    = $userData['role'];

          if (!$isAdmin) {
            try {
              $pdo->exec('ALTER TABLE utilisateurs ADD COLUMN dernier_connexion DATETIME NULL');
            } catch (PDOException $e) {
              // ignore if column already exists
            }
            $updateLogin = $pdo->prepare('UPDATE utilisateurs SET dernier_connexion = NOW() WHERE id = ?');
            $updateLogin->execute([$userData['id']]);
          }

          if ($isAdmin) {
            $_SESSION['admin_id']     = $userData['id'];
            $_SESSION['admin_prenom'] = $userData['prenom'];
            header('Location: admin.php');
            exit;
          }

          header('Location: index.php');
          exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — SmartHome</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'DM Sans', sans-serif;
      background-color: #f4f7f6;
      color: #0d1f1c;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
    }
    .auth-container {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      padding: 2.5rem;
      max-width: 450px;
      width: 100%;
      box-shadow: 0 10px 30px rgba(13,31,28,0.04);
    }
    .auth-logo {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: #0d1f1c;
      text-decoration: none;
      display: block;
      text-align: center;
      margin-bottom: 2rem;
    }
    .auth-logo span { color: #00b894; }
    h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #0d1f1c;
    }
    .subtitle { color: #637470; font-size: 0.95rem; margin-bottom: 2rem; }
    .form-group { margin-bottom: 1.25rem; }
    label { display: block; font-weight: 600; margin-bottom: 0.4rem; color: #42504d; font-size: 0.9rem; }
    input[type="email"], input[type="password"] {
      width: 100%; padding: 0.85rem 1rem; border: 1px solid #d1dbd9; border-radius: 12px;
      font-size: 1rem; font-family: inherit; color: #0d1f1c; box-sizing: border-box;
      transition: border-color 0.3s ease;
    }
    input:focus { outline: none; border-color: #00b894; }
    .alert { padding: 0.95rem 1rem; border-radius: 14px; margin-bottom: 1.5rem; font-size: 0.95rem; }
    .alert-error { background: #feecec; color: #8f1b1b; border: 1px solid #f1c1c1; }
    .btn-submit {
      width: 100%; background: #00b894; color: #ffffff; border: none; padding: 1rem;
      border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer;
      transition: background 0.3s ease; margin-top: 1rem;
    }
    .btn-submit:hover { background: #00a383; }
    .auth-footer { text-align: center; margin-top: 2rem; font-size: 0.95rem; color: #637470; }
    .auth-footer a { color: #00b894; text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>

  <div class="auth-container">
    <a href="index.php" class="auth-logo">🏠 Smart<span>Home</span></a>
    <h1>Connexion</h1>
    <p class="subtitle">Accédez à votre espace pour gérer vos recherches ou vos biens.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="connexion.php" method="POST">
      <div class="form-group">
        <label for="email">Adresse Email :</label>
        <input type="email" id="email" name="email" required placeholder="exemple@smarthome.fr">
      </div>

      <div class="form-group">
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
      </div>

      <button type="submit" class="btn-submit">Se connecter</button>
    </form>

    <div class="auth-footer">
      Nouveau sur SmartHome ? <a href="inscription.php">Créer un compte</a>
    </div>
  </div>

</body>
</html>