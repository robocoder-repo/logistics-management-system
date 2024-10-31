
<?php
session_start();

class Auth {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($company_name, $username, $email, $password, $role) {
        // Check if company exists, if not create it
        $company_id = $this->getOrCreateCompany($company_name);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users (company_id, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $company_id, $username, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT id, company_id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['company_id'] = $user['company_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                return true;
            }
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    private function getOrCreateCompany($company_name) {
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE name = ?");
        $stmt->bind_param("s", $company_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $company = $result->fetch_assoc();
            return $company['id'];
        } else {
            $stmt = $this->db->prepare("INSERT INTO companies (name) VALUES (?)");
            $stmt->bind_param("s", $company_name);
            $stmt->execute();
            return $this->db->insert_id;
        }
    }
}

// Database connection
$db = new mysqli('localhost', 'username', 'password', 'logistics_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$auth = new Auth($db);

// Usage examples:
// $auth->register('Company Name', 'username', 'email@example.com', 'password', 'admin');
// $auth->login('username', 'password');
// $auth->logout();
// if ($auth->isLoggedIn()) { ... }
// if ($auth->isAdmin()) { ... }
?>
