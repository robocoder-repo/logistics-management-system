
<?php
require_once 'auth.php';
require_once 'company_management.php';

class AdminDashboard {
    private $db;
    private $auth;
    private $company_management;

    public function __construct($db, $auth, $company_management) {
        $this->db = $db;
        $this->auth = $auth;
        $this->company_management = $company_management;
    }

    public function getCompanyUsers($company_id) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to access this data
        }

        $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE company_id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    public function addUser($company_id, $username, $email, $password, $role) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to add users
        }

        $company_name = $this->company_management->getCompanyData($company_id)['name'];
        return $this->auth->register($company_name, $username, $email, $password, $role);
    }

    public function updateUser($user_id, $new_email, $new_role) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin()) {
            return false; // User not authorized to update users
        }

        $stmt = $this->db->prepare("UPDATE users SET email = ?, role = ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("ssii", $new_email, $new_role, $user_id, $_SESSION['company_id']);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser($user_id) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin()) {
            return false; // User not authorized to delete users
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND company_id = ?");
        $stmt->bind_param("ii", $user_id, $_SESSION['company_id']);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getDashboardSummary($company_id) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to access this data
        }

        $summary = array();

        // Get outbound deliveries for today
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM outbound WHERE company_id = ? AND delivery_date = CURDATE()");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary['outbound_today'] = $result->fetch_assoc()['count'];

        // Get inbound deliveries for today
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM inbound WHERE company_id = ? AND arrival_date = CURDATE()");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary['inbound_today'] = $result->fetch_assoc()['count'];

        // Get total stock items
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM stocks WHERE company_id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary['total_stock_items'] = $result->fetch_assoc()['count'];

        return $summary;
    }
}

// Usage example:
// $admin_dashboard = new AdminDashboard($db, $auth, $company_management);
// $users = $admin_dashboard->getCompanyUsers($_SESSION['company_id']);
// $admin_dashboard->addUser($_SESSION['company_id'], 'newuser', 'newuser@example.com', 'password', 'employee');
// $admin_dashboard->updateUser(2, 'updated@example.com', 'admin');
// $admin_dashboard->deleteUser(3);
// $summary = $admin_dashboard->getDashboardSummary($_SESSION['company_id']);
?>
