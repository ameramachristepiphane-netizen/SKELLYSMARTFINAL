<?php
session_start();

// 1. Libération et destruction de toutes les variables de session
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Déconnexion — SmartHome</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet"/>
  
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'DM Sans', sans-serif;
      background-color: #f4f7f6;
      color: #0d1f1c;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    .logout-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      padding: 3rem 2rem;
      max-width: 450px;
      width: 100%;
      text-align: center;
      box-shadow: 0 10px 30px rgba(13, 31, 28, 0.04);
    }
    .logo {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: #0d1f1c;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 2rem;
    }
    .logo span {
      color: #00b894;
    }
    .icon-wrapper {
      width: 80px;
      height: 80px;
      background-color: #eaf8f4;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem auto;
    }
    .icon-wrapper text {
      font-size: 2rem;
    }
    h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: #0d1f1c;
      margin-bottom: 0.75rem;
    }
    p {
      color: #637470;
      font-size: 1.05rem;
      line-height: 1.6;
      margin-bottom: 2rem;
    }
    .btn-home {
      display: inline-block;
      background: #00b894;
      color: #ffffff;
      padding: 0.85rem 2rem;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 184, 148, 0.15);
    }
    .btn-home:hover {
      background: #00a383;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 184, 148, 0.25);
    }
    .loader-bar {
      width: 100%;
      height: 4px;
      background-color: #e2e8f0;
      border-radius: 2px;
      margin-top: 2.5rem;
      overflow: hidden;
      position: relative;
    }
    .loader-progress {
      width: 100%;
      height: 100%;
      background-color: #00b894;
      position: absolute;
      left: -100%;
      animation: load 3s linear forwards;
    }
    @keyframes load {
      to { left: 0; }
    }
  </style>
  
  <meta http-equiv="refresh" content="3;url=index.php">
</head>
<body>

  <div class="logout-card">
    <a href="index.php" class="logo">🏠 Smart<span>Home</span></a>
    
    <div class="icon-wrapper">
      <span>👋</span>
    </div>

    <h1>À bientôt !</h1>
    <p>Vous avez été déconnecté avec succès. Vous allez être redirigé vers l'accueil dans un instant.</p>

    <a href="index.php" class="btn-home">Retourner à l'accueil</a>

    <div class="loader-bar">
      <div class="loader-progress"></div>
    </div>
  </div>

</body>
</html>
