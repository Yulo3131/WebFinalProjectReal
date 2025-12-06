<?php
// filepath: api/config.php

// 1. Get credentials from Environment Variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$port = getenv('DB_PORT');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');

// 2. Initialize MySQLi
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

// 3. Configure SSL (Required for TiDB/Serverless databases)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// 4. Connect securely
if (!$conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connect Error: " . mysqli_connect_error());
}

// 5. Custom Session Handler (Database-backed sessions)
class DbSessionHandler implements SessionHandlerInterface {
    private $link;

    public function __construct($link) {
        $this->link = $link;
    }

    public function open($savePath, $sessionName): bool { return true; }
    public function close(): bool { return true; }

    public function read($id): string|false {
        $stmt = $this->link->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['data'];
        }
        return '';
    }

    public function write($id, $data): bool {
        $access = time();
        $stmt = $this->link->prepare("REPLACE INTO sessions (id, access, data) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $id, $access, $data);
        return $stmt->execute();
    }

    public function destroy($id): bool {
        $stmt = $this->link->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function gc($max_lifetime): int|false {
        $old = time() - $max_lifetime;
        $stmt = $this->link->prepare("DELETE FROM sessions WHERE access < ?");
        $stmt->bind_param("i", $old);
        return $stmt->execute() ? 1 : 0;
    }
}

$handler = new DbSessionHandler($conn);
session_set_save_handler($handler, true);
?>
