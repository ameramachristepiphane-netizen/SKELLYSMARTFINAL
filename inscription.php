<?php
session_start();

// ── Connexion MySQL directe ──────────────────────────────────
$host   = '127.0.0.1';
$dbname = 'smarthome';
$user   = 'root';   // ← adapte selon ton environnement
$pass   = 'root';       // ← adapte selon ton environnement

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Connexion impossible : ' . $e->getMessage());
}

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom']   ?? '');
    $nom    = trim($_POST['nom']      ?? '');
    $email  = trim($_POST['email']    ?? '');
    $mdp    = $_POST['password']      ?? '';
    $profil = $_POST['profil']        ?? '';

    if (!$prenom || !$nom || !$email || !$mdp || !$profil) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($mdp) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } else {
        // Mapping profil → ENUM situation de la BDD
        $situationMap = [
            'Étudiant'    => 'etudiant',
            'Travailleur' => 'travailleur',
            'Étranger'    => 'etranger',
        ];
        $situation = $situationMap[$profil] ?? 'autre';

        // Vérifier doublon email
        $check = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? LIMIT 1');
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($mdp, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, situation, actif)
                 VALUES (?, ?, ?, ?, "locataire", ?, 1)'
            );
            $stmt->execute([$nom, $prenom, $email, $hash, $situation]);

            // Connexion automatique après inscription
            $newUser = $pdo->prepare('SELECT id, prenom, nom, role FROM utilisateurs WHERE email = ? LIMIT 1');
            $newUser->execute([$email]);
            $u = $newUser->fetch();

            $_SESSION['user_id'] = $u['id'];
            $_SESSION['prenom']  = $u['prenom'];
            $_SESSION['nom']     = $u['nom'];
            $_SESSION['role']    = $u['role'];

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
  <title>Inscription — SmartHome</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
    <form action="inscription.php" method="POST" style="width:min(520px,100%);background:#fff;border:1px solid #e8eceb;border-radius:24px;padding:2rem;box-shadow:0 18px 55px rgba(0,0,0,0.06);">
      <h2 style="margin-bottom:1.4rem;color:#0d1f1c;">Inscription</h2>

      <?php if ($message): ?>
        <p style="margin-bottom:1rem;padding:0.95rem 1rem;background:#eaf8f4;color:#0b5f4f;border:1px solid #b9e7d6;border-radius:14px;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if ($error): ?>
        <p style="margin-bottom:1rem;padding:0.95rem 1rem;background:#feecec;color:#8f1b1b;border:1px solid #f1c1c1;border-radius:14px;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <label style="display:block;font-weight:600;margin-bottom:0.4rem;color:#42504d;">Prénom :</label>
      <input type="text" name="prenom" required style="width:100%;padding:0.85rem 1rem;border:1px solid #d1dbd9;border-radius:12px;margin-bottom:1rem;font-size:1rem;" />

      <label style="display:block;font-weight:600;margin-bottom:0.4rem;color:#42504d;">Nom :</label>
      <input type="text" name="nom" required style="width:100%;padding:0.85rem 1rem;border:1px solid #d1dbd9;border-radius:12px;margin-bottom:1rem;font-size:1rem;" />

      <label style="display:block;font-weight:600;margin-bottom:0.4rem;color:#42504d;">Email :</label>
      <input type="email" name="email" required style="width:100%;padding:0.85rem 1rem;border:1px solid #d1dbd9;border-radius:12px;margin-bottom:1rem;font-size:1rem;" />

      <label style="display:block;font-weight:600;margin-bottom:0.4rem;color:#42504d;">Mot de passe :</label>
      <input type="password" name="password" required style="width:100%;padding:0.85rem 1rem;border:1px solid #d1dbd9;border-radius:12px;margin-bottom:1rem;font-size:1rem;" />

      <label style="display:block;font-weight:600;margin-bottom:0.4rem;color:#42504d;">Profil :</label>
      <select name="profil" required style="width:100%;padding:0.85rem 1rem;border:1px solid #d1dbd9;border-radius:12px;margin-bottom:1.5rem;font-size:1rem;">
        <option value="Étudiant">Étudiant</option>
        <option value="Travailleur">Travailleur</option>
        <option value="Étranger">Étranger</option>
      </select>

      <button type="submit" style="width:100%;padding:0.95rem 1rem;background:#00b894;color:#fff;border:none;border-radius:14px;font-size:1rem;font-weight:700;cursor:pointer;">Créer un compte</button>
      <p style="margin-top:1rem;font-size:0.95rem;color:#637470;">Déjà inscrit ? <a href="connexion.php" style="color:#0d6e5e;text-decoration:none;font-weight:600;">Se connecter</a></p>
    </form>
  </main>
</body>
</html>
