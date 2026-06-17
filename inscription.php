<?php
// Démarrage de la session pour gérer l'utilisateur après inscription
session_start();

// ── Configuration de connexion MySQL (PDO) ──────────────────────────────────
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
    die('Connexion impossible : ' . $e->getMessage());
}

// Messages affichés à l'utilisateur
$message = '';
$error   = '';

// Traitement du formulaire uniquement en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des champs envoyés
    $prenom = trim($_POST['prenom']         ?? '');
    $nom    = trim($_POST['nom']            ?? '');
    $email  = trim($_POST['email']          ?? '');
    $mdp    = $_POST['password']            ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $profil = $_POST['profil']              ?? '';

    // Validation basique des champs
    if (!$prenom || !$nom || !$email || !$mdp || !$confirmPassword || !$profil) {
        $error = 'Tous les champs sont obligatoires.'; // champs manquants
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.'; // format email incorrect
    } elseif (strlen($mdp) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.'; // trop court
    } elseif ($mdp !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.'; // confirmation incorrecte
    } else {
        // Mapping du libellé sélectionné vers la valeur attendue en base
        $situationMap = [
            'Étudiant'    => 'etudiant',
            'Travailleur' => 'travailleur',
            'Étranger'    => 'etranger',
        ];
        $situation = $situationMap[$profil] ?? 'autre';

        // Vérification d'un éventuel doublon d'email
        $check = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? LIMIT 1');
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'Cette adresse email est déjà utilisée.'; // email existant
        } else {
            // Hachage sécurisé du mot de passe
            $hash = password_hash($mdp, PASSWORD_BCRYPT);

            // Préparation de l'insertion en base
            // Remarque : 'role' fixé ici à "locataire" et 'actif' à 1
            $stmt = $pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, situation, actif)
                 VALUES (?, ?, ?, ?, "locataire", ?, 1)'
            );

            // Exécution de la requête d'insertion
            if ($stmt->execute([$nom, $prenom, $email, $hash, $situation])) {
                // Récupération des informations de l'utilisateur nouvellement créé
                $newUser = $pdo->prepare('SELECT id, prenom, nom, role FROM utilisateurs WHERE email = ? LIMIT 1');
                $newUser->execute([$email]);
                $u = $newUser->fetch();

                // Connexion automatique : on stocke les infos utiles en session
                // Note: on n'autorise pas l'accès admin via l'inscription.
                // Les administrateurs doivent se connecter via la page `connexion.php`.
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['prenom']  = $u['prenom'];
                $_SESSION['nom']     = $u['nom'];
                $_SESSION['role']    = $u['role'];

                // Redirection vers la page d'accueil après inscription
                header('Location: index.php');
                exit;
            } else {
                // Cas d'erreur à l'insertion
                $error = 'Une erreur est survenue lors de l’inscription.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription — SmartHome</title>
  <!-- Google Fonts utilisées -->
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <!-- Feuille de style globale du site -->
  <link rel="stylesheet" href="style.css">
  <!-- Styles inline spécifiques à la page d'inscription (pour isolation) -->
  <style>
    /* Corps de la page : centrage et spacing */
    body {
      font-family: 'DM Sans', sans-serif;
      background-color: #f4f7f6;
      color: #0d1f1c;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 40px 20px;
    }
    /* Conteneur du formulaire */
    .auth-container {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      padding: 2.5rem;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 10px 30px rgba(13,31,28,0.04);
    }
    /* Logo / titre en haut du formulaire */
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
    .auth-logo span { color: #00b894; /* accent coloré sur "Home" */ }
    /* Titre principal */
    h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #0d1f1c;
    }
    /* Sous-titre descriptif */
    .subtitle {
      color: #637470;
      font-size: 0.95rem;
      margin-bottom: 2rem;
    }
    /* Groupe de champ du formulaire */
    .form-group { margin-bottom: 1.25rem; }
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.4rem;
      color: #42504d;
      font-size: 0.9rem;
    }
    /* Styles communs aux inputs et selects */
    input[type="text"], input[type="email"], input[type="password"], select {
      width: 100%;
      padding: 0.85rem 1rem;
      border: 1px solid #d1dbd9;
      border-radius: 12px;
      font-size: 1rem;
      font-family: inherit;
      color: #0d1f1c;
      box-sizing: border-box;
      transition: border-color 0.3s ease;
      background-color: #fff;
    }
    /* Focus visible sur champs */
    input:focus, select:focus { outline: none; border-color: #00b894; }
    /* Alerte d'erreur */
    .alert {
      padding: 0.95rem 1rem;
      border-radius: 14px;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
    }
    .alert-error { background: #feecec; color: #8f1b1b; border: 1px solid #f1c1c1; }
    /* Bouton principal d'envoi */
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
    .btn-submit:hover { background: #00a383; }
    /* Pied de formulaire avec lien connexion */
    .auth-footer { text-align: center; margin-top: 2rem; font-size: 0.95rem; color: #637470; }
    .auth-footer a { color: #00b894; text-decoration: none; font-weight: 600; }
    .auth-footer a:hover { text-decoration: underline; }
  </style>
</head>
<body>

  <div class="auth-container">
    <!-- Logo vers l'accueil -->
    <a href="index.php" class="auth-logo">🏠 Smart<span>Home</span></a>
    
    <!-- Titre et sous-titre -->
    <h1>Créer un compte</h1>
    <p class="subtitle">Rejoignez SmartHome pour trouver votre logement facilement.</p>

    <!-- Affichage d'une éventuelle erreur après soumission -->
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- Formulaire d'inscription (méthode POST) -->
    <form action="inscription.php" method="POST">
      <div class="form-group">
        <label for="prenom">Prénom :</label>
        <!-- Valeur conservée en cas d'erreur (réaffichage sécurisé) -->
        <input type="text" id="prenom" name="prenom" required placeholder="John" value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>">
      </div>

      <div class="form-group">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required placeholder="Doe" value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
      </div>

      <div class="form-group">
        <label for="email">Adresse Email :</label>
        <input type="email" id="email" name="email" required placeholder="exemple@smarthome.fr" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
      </div>

      <div class="form-group">
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required placeholder="8 caractères minimum">
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirmez le mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Retapez votre mot de passe">
      </div>

      <div class="form-group">
        <label for="profil">Profil :</label>
        <!-- Choix de profil utilisé côté serveur pour la colonne 'situation' -->
        <select id="profil" name="profil" required>
          <option value="Étudiant">Étudiant</option>
          <option value="Travailleur">Travailleur</option>
          <option value="Étranger">Étranger</option>
        </select>
      </div>

      <button type="submit" class="btn-submit">S'inscrire</button>
    </form>

    <div class="auth-footer">
      Déjà membre ? <a href="connexion.php">Se connecter</a>
    </div>
  </div>

</body>
</html>