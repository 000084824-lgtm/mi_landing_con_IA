<?php
require_once __DIR__ . '/config.php';

$user = currentUser();
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $foundUser = $stmt->fetch();

    if ($foundUser && password_verify($password, $foundUser['password_hash'])) {
        $_SESSION['user'] = [
            'id' => (int) $foundUser['id'],
            'name' => $foundUser['name'],
            'email' => $foundUser['email'],
            'role' => $foundUser['role'],
        ];

        if (($foundUser['role'] ?? 'user') === 'admin') {
            header('Location: dashboard.php');
            exit;
        }

        header('Location: index.php');
        exit;
    }

    $errorMessage = 'Correo o contraseña inválidos.';
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Flor de Cerezo — Regala un momento bonito</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <a class="skip-link" href="#hero-title">Saltar al contenido</a>
  <header class="topbar" role="banner">
    <div class="container topbar-inner">
      <div class="brand">Flor de Cerezo</div>
      <div class="topbar-actions">
        <?php if ($user): ?>
          <span class="welcome-pill">Hola, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php if (($user['role'] ?? 'user') === 'admin'): ?>
            <a class="btn btn-ghost" href="dashboard.php">Dashboard</a>
          <?php endif; ?>
          <a class="btn btn-ghost" href="index.php?logout=1">Salir</a>
        <?php else: ?>
          <span class="welcome-pill">Accede para continuar</span>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main>
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-content">
        <div class="eyebrow" aria-hidden="true">Flor de Cerezo</div>
        <h1 id="hero-title">Regala un momento bonito</h1>
        <p class="lead">Ramos hechos a mano para sorprender a quien quieras.</p>
        <div class="ctas">
          <a href="https://www.instagram.com/gabyd._1/" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Quiero mandar mi ramo</a>
          <a href="#ramos" class="btn btn-ghost">Ver ramos</a>
        </div>
        <div class="microcopy" aria-live="polite">Envío en 24h · Flores frescas</div>
      </div>

      <div class="hero-side">
        <figure class="hero-figure">
          <picture>
            <source srcset="https://picsum.photos/id/1015/480/320 480w, https://picsum.photos/id/1016/800/600 800w, https://picsum.photos/id/1018/1600/900 1600w" sizes="(max-width:900px) 100vw, 520px">
            <img src="https://picsum.photos/id/1016/800/600" alt="Ramo delicado en tonos pastel, presentado en caja para regalar" width="800" height="600" decoding="async">
          </picture>
        </figure>

        <section class="auth-card" aria-label="Acceso de usuarios">
          <?php if ($user): ?>
            <h2>Tu acceso está activo</h2>
            <p>Bienvenido de nuevo, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>. Puedes seguir navegando o entrar al panel si eres administrador.</p>
          <?php else: ?>
            <h2>Inicia sesión</h2>
            <p>Ingresa tu correo y contraseña para entrar según tu tipo de usuario.</p>
            <?php if ($errorMessage): ?>
              <p class="error-message"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <form method="post" class="auth-form">
              <input type="email" name="email" placeholder="Correo" required>
              <input type="password" name="password" placeholder="Contraseña" required>
              <button type="submit" name="login" class="btn btn-primary">Entrar</button>
            </form>
          <?php endif; ?>
        </section>
      </div>
    </section>
  </main>

  <script src="scripts.js"></script>
</body>
</html>
