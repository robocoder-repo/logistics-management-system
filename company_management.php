
<?php
require_once 'auth.php';

class CompanyManagement {
    private $db;
    private $auth;

    public function __construct($db, $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function registerCompany($company_name, $admin_username, $admin_email, $admin_password) {
        // Start transaction
        $this->db->begin_transaction();

        try {
            // Register company
            $stmt = $this->db->prepare("INSERT INTO companies (name) VALUES (?)");
            $stmt->bind_param("s", $company_name);
            $stmt->execute();
            $company_id = $this->db->insert_id;

            // Register admin user
            $result = $this->auth->register($company_name, $admin_username, $admin_email, $admin_password, 'admin');

            if (!$result) {
                throw new Exception("Failed to register admin user");
            }

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            return false;
        }
    }

    public function getCompanyData($company_id) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to access this company's data
        }

        $stmt = $this->db->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function updateCompanyData($company_id, $new_name) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to update this company's data
        }

        $stmt = $this->db->prepare("UPDATE companies SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $company_id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

// Usage example:
// $company_management = new CompanyManagement($db, $auth);
// $company_management->registerCompany('New Company', 'admin', 'admin@example.com', 'password');
// $company_data = $company_management->getCompanyData($_SESSION['company_id']);
// $company_management->updateCompanyData($_SESSION['company_id'], 'Updated Company Name');
?>
