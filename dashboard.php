<?php
require_once __DIR__ . '/config.php';

requireAdmin();

$user = currentUser();
$pdo = getPdo();

$message = '';
$editingUserId = null;
$editingUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sale'])) {
    $saleDate = trim($_POST['sale_date'] ?? '');
    $customerName = trim($_POST['customer_name'] ?? '');
    $totalAmount = (float) ($_POST['total_amount'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($saleDate === '' || $customerName === '') {
        $message = 'Completa la fecha y el nombre del cliente.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO sales (sale_date, customer_name, total_amount, description) VALUES (?, ?, ?, ?)');
        $stmt->execute([$saleDate, $customerName, $totalAmount, $description]);
        $message = 'Venta registrada correctamente.';
        header('Location: dashboard.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

    if ($name === '' || $email === '') {
        $message = 'Completa nombre y correo.';
    } else {
        if ($id > 0) {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password_hash = ?, role = ? WHERE id = ?');
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
                $stmt->execute([$name, $email, $role, $id]);
            }
            $message = 'Usuario actualizado correctamente.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            $message = 'Usuario creado correctamente.';
        }

        header('Location: dashboard.php');
        exit;
    }
}

if (isset($_GET['edit'])) {
    $editingUserId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$editingUserId]);
    $editingUser = $stmt->fetch();
}

$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$today = date('Y-m-d');
$dailySummary = $pdo->prepare('SELECT COALESCE(SUM(total_amount), 0) AS total, COUNT(*) AS quantity FROM sales WHERE sale_date = ?');
$dailySummary->execute([$today]);
$dailySales = $dailySummary->fetch();

$recentSales = $pdo->prepare('SELECT * FROM sales WHERE sale_date = ? ORDER BY created_at DESC LIMIT 10');
$recentSales->execute([$today]);
$recentSales = $recentSales->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | Flor de Cerezo</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="topbar" role="banner">
    <div class="container topbar-inner">
      <div class="brand">Flor de Cerezo · Dashboard</div>
      <div class="topbar-actions">
        <span class="welcome-pill">Hola, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
        <a class="btn btn-ghost" href="index.php">Landing</a>
        <a class="btn btn-ghost" href="index.php?logout=1">Salir</a>
      </div>
    </div>
  </header>

  <main class="dashboard-page">
    <div class="container dashboard-grid">
      <section class="panel">
        <h2>Gestión de usuarios</h2>
        <?php if ($message): ?>
          <p class="success-message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" class="stacked-form">
          <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($editingUser['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
          <label>
            Nombre
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($editingUser['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
          </label>
          <label>
            Correo
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($editingUser['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
          </label>
          <label>
            Contraseña
            <input type="password" name="password" placeholder="Deja vacío para no cambiarla">
          </label>
          <label>
            Rol
            <select name="role">
              <option value="user" <?= (($editingUser['role'] ?? 'user') === 'user') ? 'selected' : '' ?>>Usuario</option>
              <option value="admin" <?= (($editingUser['role'] ?? 'user') === 'admin') ? 'selected' : '' ?>>Administrador</option>
            </select>
          </label>
          <button type="submit" name="save_user" class="btn btn-primary">Guardar usuario</button>
        </form>
      </section>

      <section class="panel">
        <h2>Ventas de hoy</h2>
        <div class="stats-card">
          <div>
            <strong><?= number_format((float) $dailySales['total'], 2, '.', ',') ?></strong>
            <span>Total del día</span>
          </div>
          <div>
            <strong><?= (int) $dailySales['quantity'] ?></strong>
            <span>Ventas</span>
          </div>
        </div>

        <form method="post" class="stacked-form">
          <input type="date" name="sale_date" value="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required>
          <input type="text" name="customer_name" placeholder="Nombre del cliente" required>
          <input type="number" step="0.01" name="total_amount" placeholder="Monto" value="0" required>
          <input type="text" name="description" placeholder="Descripción">
          <button type="submit" name="save_sale" class="btn btn-primary">Registrar venta</button>
        </form>

        <h3>Últimas ventas</h3>
        <ul class="sales-list">
          <?php foreach ($recentSales as $sale): ?>
            <li>
              <span><?= htmlspecialchars($sale['customer_name'], ENT_QUOTES, 'UTF-8') ?></span>
              <strong>$<?= number_format((float) $sale['total_amount'], 2, '.', ',') ?></strong>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>

    <div class="container">
      <section class="panel">
        <h2>Usuarios registrados</h2>
        <table class="users-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $item): ?>
              <tr>
                <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($item['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($item['role'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><a class="btn btn-ghost" href="dashboard.php?edit=<?= (int) $item['id'] ?>">Editar</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </div>
  </main>

  <script src="scripts.js"></script>
</body>
</html>
