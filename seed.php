<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = getPdo();

    // Eliminar usuarios existentes
    $pdo->exec('TRUNCATE TABLE users');

    // Insertar usuarios con hashes correctos
    $users = [
        [
            'name' => 'Administrador',
            'email' => 'admin@tuempresa.com',
            'password' => 'Admin123!',
            'role' => 'admin'
        ],
        [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'User123!',
            'role' => 'user'
        ]
    ];

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');

    foreach ($users as $user) {
        $hash = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt->execute([
            $user['name'],
            $user['email'],
            $hash,
            $user['role']
        ]);
        
        echo "Insertado: {$user['email']} (rol: {$user['role']})<br>";
        error_log("Seeded user: {$user['email']} with password: {$user['password']}");
    }

    echo "<h2>✅ Base de datos poblada correctamente</h2>";
    echo "<p>Usuarios de prueba creados:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@tuempresa.com / Admin123!</li>";
    echo "<li><strong>Usuario:</strong> juan@example.com / User123!</li>";
    echo "</ul>";

} catch (PDOException $e) {
    error_log("Seed Error: " . $e->getMessage());
    echo "<h2>❌ Error al popular la BD</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
