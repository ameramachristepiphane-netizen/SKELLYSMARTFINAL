<?php
// Démarre ou reprend la session pour l'utilisateur
session_start();

// ── Paramètres de connexion MySQL (PDO) ──────────────────────────────────
// Modifie ces valeurs selon ton environnement local/production
$host    = '127.0.0.1';
$dbname  = 'smarthome';
$user    = 'root';   // ← adapte selon ton environnement
$pass    = 'root';   // ← adapte selon ton environnement

// Tentative de connexion à la base via PDO avec gestion d'erreur
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // En cas d'échec, on stoppe avec le message d'erreur (à ne pas exposer en prod)
    die('Connexion impossible : ' . $e->getMessage());
}

// ── Redirection si l'utilisateur est déjà connecté ─────────────────────
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Variables pour messages affichés dans la vue
$message    = '';
$error      = '';
$statusText = '';

// Traitement du formulaire de connexion (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère et nettoie les champs envoyés
    $email = trim($_POST['email']    ?? '');
    $mdp   = $_POST['password']      ?? '';

    // Validations basiques
    if (!$email || !$mdp) {
        $error = 'Veuillez remplir tous les champs.'; // champs manquants
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.'; // email mal formé
    } else {
        // Prépare la requête pour récupérer l'utilisateur par email
        $stmt = $pdo->prepare('SELECT id, prenom, nom, mot_de_passe, role, actif FROM utilisateurs WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérifie l'existence de l'utilisateur et la validité du mot de passe
        if (!$user || !password_verify($mdp, $user['mot_de_passe'])) {
            $error = 'Email ou mot de passe incorrect.'; // identifiants invalides
        } elseif (!$user['actif']) {
            $error = 'Ce compte est désactivé.'; // compte inactif
        } else {
            // ── Connexion réussie : on stocke les infos essentielles en session ──
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_role']   = $user['role'];
            $_SESSION['user_prenom'] = $user['prenom']; // correction : stocke le prénom

            // Redirection vers la page d'accueil
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
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <!-- Feuille de style globale -->
  <link rel="stylesheet" href="style.css">
  <!-- Styles inline spécifiques à la page de connexion -->
  <style>
    /* Mise en page principale : centre la carte de connexion */
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
    /* Conteneur de la carte de connexion */
    .auth-container {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      padding: 2.5rem;
      max-width: 450px;
      width: 100%;
      box-shadow: 0 10px 30px rgba(13,31,28,0.04);
    }
    /* Logo / titre haut */
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
    .auth-logo span {
      color: #00b894; /* accent */
    }
    /* Titre de la page */
    h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #0d1f1c;
    }
    .subtitle {
      color: #637470;
      font-size: 0.95rem;
      margin-bottom: 2rem;
    }
    .form-group {
      margin-bottom: 1.25rem;
    }
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.4rem;
      color: #42504d;
      font-size: 0.9rem;
    }
    /* Styles pour les champs email et mot de passe */
    input[type="email"], input[type="password"] {
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
      border-color: #00b894; /* accent au focus */
    }
    /* Styles d'alerte pour erreurs / succès */
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
      background: #eaf8f4;
      color: #0b5f4f;
      border: 1px solid #b9e7d6;
    }
    /* Bouton de soumission */
    .btn-submit {
      width: 100%;
      background: #00b894;
      color: #ffffff;
      border: none;
      padding: 1rem;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
      margin-top: 1rem;
    }
    .btn-submit:hover {
      background: #00a383;
    }
    .auth-footer {
      text-align: center;
      margin-top: 2rem;
      font-size: 0.95rem;
      color: #637470;
    }
    .auth-footer a {
      color: #00b894;
      text-decoration: none;
      font-weight: 600;
    }
    .auth-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="auth-container">
    <!-- Logo cliquable renvoyant à l'accueil -->
    <a href="index.php" class="auth-logo">🏠 Smart<span>Home</span></a>
    
    <h1>Connexion</h1>
    <p class="subtitle">Accédez à votre espace pour gérer vos recherches ou vos biens.</p>

    <!-- Affichage d'un message de succès éventuel -->
    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- Affichage d'une erreur si présente -->
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- Formulaire de connexion (POST) -->
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