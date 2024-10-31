
<?php
require_once 'auth.php';
require_once 'company_management.php';

class EmployeeDashboard {
    private $db;
    private $auth;
    private $company_management;

    public function __construct($db, $auth, $company_management) {
        $this->db = $db;
        $this->auth = $auth;
        $this->company_management = $company_management;
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

    public function getOutboundItems($company_id) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to access this data
        }

        $stmt = $this->db->prepare("SELECT o.*, s.item_name, s.barcode FROM outbound o JOIN stocks s ON o.stock_id = s.id WHERE o.company_id = ? ORDER BY o.delivery_date ASC");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $outbound_items = array();
        while ($row = $result->fetch_assoc()) {
            $outbound_items[] = $row;
        }

        return $outbound_items;
    }

    public function getInboundItems($company_id) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to access this data
        }

        $stmt = $this->db->prepare("SELECT i.*, s.item_name, s.barcode FROM inbound i JOIN stocks s ON i.stock_id = s.id WHERE i.company_id = ? ORDER BY i.arrival_date ASC");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $inbound_items = array();
        while ($row = $result->fetch_assoc()) {
            $inbound_items[] = $row;
        }

        return $inbound_items;
    }

    public function getStockItems($company_id) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to access this data
        }

        $stmt = $this->db->prepare("SELECT * FROM stocks WHERE company_id = ? ORDER BY item_name ASC");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $stock_items = array();
        while ($row = $result->fetch_assoc()) {
            $stock_items[] = $row;
        }

        return $stock_items;
    }

    public function searchStockItems($company_id, $search_term) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to access this data
        }

        $search_term = "%{$search_term}%";
        $stmt = $this->db->prepare("SELECT * FROM stocks WHERE company_id = ? AND (item_name LIKE ? OR barcode LIKE ?) ORDER BY item_name ASC");
        $stmt->bind_param("iss", $company_id, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();

        $stock_items = array();
        while ($row = $result->fetch_assoc()) {
            $stock_items[] = $row;
        }

        return $stock_items;
    }
}

// Usage example:
// $employee_dashboard = new EmployeeDashboard($db, $auth, $company_management);
// $summary = $employee_dashboard->getDashboardSummary($_SESSION['company_id']);
// $outbound_items = $employee_dashboard->getOutboundItems($_SESSION['company_id']);
// $inbound_items = $employee_dashboard->getInboundItems($_SESSION['company_id']);
// $stock_items = $employee_dashboard->getStockItems($_SESSION['company_id']);
// $search_results = $employee_dashboard->searchStockItems($_SESSION['company_id'], 'search_term');
?>
