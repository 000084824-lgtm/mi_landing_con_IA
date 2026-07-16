<?php
session_start();

function getPdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
    $database = getenv('DB_NAME') ?: getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'landing_db';
    $username = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';

    // Log de variables para debugging
    error_log("DB Connection: host=$host, port=$port, database=$database, username=$username");

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        throw $e;
    }

    return $pdo;
}

function initializeDatabase(): void
{
    try {
        $pdo = getPdo();

        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(180) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin','user') NOT NULL DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS sales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sale_date DATE NOT NULL,
                customer_name VARCHAR(120) NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                description VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_sale_date (sale_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL);

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
        $stmt->execute(['admin']);

        // Siempre actualizar/reinsertar el admin para asegurar hash correcto
        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)')->execute([
                'Administrador',
                'admin@tuempresa.com',
                password_hash('Admin123!', PASSWORD_DEFAULT),
                'admin',
            ]);
            error_log("Admin user created");
        } else {
            // Actualizar hash del admin por si acaso
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?')->execute([
                password_hash('Admin123!', PASSWORD_DEFAULT),
                'admin@tuempresa.com'
            ]);
            error_log("Admin user hash updated");
        }

        // Insertar usuario regular de prueba si no existe
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute(['juan@example.com']);

        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)')->execute([
                'Juan Pérez',
                'juan@example.com',
                password_hash('User123!', PASSWORD_DEFAULT),
                'user',
            ]);
            error_log("Regular user created");
        } else {
            // Actualizar hash del usuario
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?')->execute([
                password_hash('User123!', PASSWORD_DEFAULT),
                'juan@example.com'
            ]);
            error_log("Regular user hash updated");
        }
    } catch (PDOException $e) {
        error_log("Database Initialization Error: " . $e->getMessage());
        // No lanzar excepción para permitir que la página se cargue
    }
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireAdmin(): void
{
    $user = currentUser();

    if (!$user || ($user['role'] ?? '') !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

initializeDatabase();
