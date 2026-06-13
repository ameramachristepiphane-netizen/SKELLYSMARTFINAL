<?php
session_start();

// ── Connexion MySQL directe ──────────────────────────────────
$host    = '127.0.0.1';
$dbname  = 'smarthome';
$user    = 'root';   // ← adapte selon ton environnement
$pass    = 'root';       // ← adapte selon ton environnement

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Connexion impossible : ' . $e->getMessage());
}

// ── Redirection si déjà connecté ────────────────────────────
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message    = '';
$error      = '';
$statusText = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']    ?? '');
    $mdp   = $_POST['password']      ?? '';

    if (!$email || !$mdp) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $stmt = $pdo->prepare('SELECT id, prenom, nom, mot_de_passe, role, actif FROM utilisateurs WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($mdp, $user['mot_de_passe'])) {
            $error = 'Email ou mot de passe incorrect.';
        } elseif (!$user['actif']) {
            $error = 'Ce compte est désactivé. Contactez le support.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['role']    = $user['role'];

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
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
    <form action="connexion.php" method="POST" style="width:min(420px,100%);background:#fff;border:1px solid #e8eceb;border-radius:24px;padding:2rem;box-shadow:0 18px 55px rgba(0,0,0,0.06);">
      <h2 style="margin-bottom:1.4rem;color:#0d1f1c;">Connexion</h2>

      <?php if ($statusText): ?>
        <p style="margin-bottom:1rem;padding:0.95rem 1rem;background:#f0faf8;color:#0c5f51;border:1px solid #c7ede4;border-radius:14px;"><?= $statusText ?></p>
      <?php endif; ?>

      <?php if ($message): ?>
        <p style="margin-bottom:1rem;padding:0.95rem 1rem;background:#eaf8f4;color:#0b5f4f;border:1px solid #b9e7d6;border-radius:14px;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if ($error): ?>
        <p style="margin-bottom:1rem;padding:0.95rem 1rem;background:#feecec;color:#8f1b1b;border:1px solid #f1c1c1;border-radius:14px;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <label style="display:block;font-weight:600;margin-bottom:0.4rem;color:#42504d;">Email :</label>
      <input type="email" name="email" required style="width:100%;padding:0.85rem 1rem;border:1px solid #d1dbd9;border-radius:12px;margin-bottom:1rem;font-size:1rem;" />

      <label style="display:block;font-weight:600;margin-bottom:0.4rem;color:#42504d;">Mot de passe :</label>
      <input type="password" name="password" required style="width:100%;padding:0.85rem 1rem;border:1px solid #d1dbd9;border-radius:12px;margin-bottom:1.5rem;font-size:1rem;" />

      <button type="submit" style="width:100%;padding:0.95rem 1rem;background:#00b894;color:#fff;border:none;border-radius:14px;font-size:1rem;font-weight:700;cursor:pointer;">Se connecter</button>
      <p style="margin-top:1rem;font-size:0.95rem;color:#637470;">Pas encore de compte ? <a href="inscription.php" style="color:#0d6e5e;text-decoration:none;font-weight:600;">Inscrivez-vous</a></p>
    </form>
  </main>
</body>
</html>
