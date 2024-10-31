
<?php
require_once 'auth.php';
require_once 'company_management.php';

class InventoryManagement {
    private $db;
    private $auth;
    private $company_management;

    public function __construct($db, $auth, $company_management) {
        $this->db = $db;
        $this->auth = $auth;
        $this->company_management = $company_management;
    }

    public function addStockItem($company_id, $item_name, $barcode, $quantity, $location) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to add stock items
        }

        $stmt = $this->db->prepare("INSERT INTO stocks (company_id, item_name, barcode, quantity, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $company_id, $item_name, $barcode, $quantity, $location);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            return false;
        }
    }

    public function updateStockItem($company_id, $stock_id, $item_name, $barcode, $quantity, $location) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to update stock items
        }

        $stmt = $this->db->prepare("UPDATE stocks SET item_name = ?, barcode = ?, quantity = ?, location = ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("sssiii", $item_name, $barcode, $quantity, $location, $stock_id, $company_id);

        return $stmt->execute();
    }

    public function deleteStockItem($company_id, $stock_id) {
        if (!$this->auth->isLoggedIn() || !$this->auth->isAdmin() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to delete stock items
        }

        $stmt = $this->db->prepare("DELETE FROM stocks WHERE id = ? AND company_id = ?");
        $stmt->bind_param("ii", $stock_id, $company_id);

        return $stmt->execute();
    }

    public function addOutboundItem($company_id, $stock_id, $quantity, $delivery_date, $delivery_time, $recipient, $delivery_address) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to add outbound items
        }

        $stmt = $this->db->prepare("INSERT INTO outbound (company_id, stock_id, quantity, delivery_date, delivery_time, recipient, delivery_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissss", $company_id, $stock_id, $quantity, $delivery_date, $delivery_time, $recipient, $delivery_address);

        if ($stmt->execute()) {
            // Update stock quantity
            $this->updateStockQuantity($company_id, $stock_id, -$quantity);
            return $this->db->insert_id;
        } else {
            return false;
        }
    }

    public function updateOutboundItem($company_id, $outbound_id, $stock_id, $quantity, $delivery_date, $delivery_time, $recipient, $delivery_address, $status) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to update outbound items
        }

        $stmt = $this->db->prepare("UPDATE outbound SET stock_id = ?, quantity = ?, delivery_date = ?, delivery_time = ?, recipient = ?, delivery_address = ?, status = ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("iisssssii", $stock_id, $quantity, $delivery_date, $delivery_time, $recipient, $delivery_address, $status, $outbound_id, $company_id);

        return $stmt->execute();
    }

    public function addInboundItem($company_id, $stock_id, $quantity, $arrival_date, $arrival_time, $supplier) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to add inbound items
        }

        $stmt = $this->db->prepare("INSERT INTO inbound (company_id, stock_id, quantity, arrival_date, arrival_time, supplier) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $company_id, $stock_id, $quantity, $arrival_date, $arrival_time, $supplier);

        if ($stmt->execute()) {
            // Update stock quantity
            $this->updateStockQuantity($company_id, $stock_id, $quantity);
            return $this->db->insert_id;
        } else {
            return false;
        }
    }

    public function updateInboundItem($company_id, $inbound_id, $stock_id, $quantity, $arrival_date, $arrival_time, $supplier, $status) {
        if (!$this->auth->isLoggedIn() || $_SESSION['company_id'] != $company_id) {
            return false; // User not authorized to update inbound items
        }

        $stmt = $this->db->prepare("UPDATE inbound SET stock_id = ?, quantity = ?, arrival_date = ?, arrival_time = ?, supplier = ?, status = ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("iisssssii", $stock_id, $quantity, $arrival_date, $arrival_time, $supplier, $status, $inbound_id, $company_id);

        return $stmt->execute();
    }

    private function updateStockQuantity($company_id, $stock_id, $quantity_change) {
        $stmt = $this->db->prepare("UPDATE stocks SET quantity = quantity + ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("iii", $quantity_change, $stock_id, $company_id);
        return $stmt->execute();
    }
}

// Usage example:
// $inventory_management = new InventoryManagement($db, $auth, $company_management);
// $inventory_management->addStockItem($_SESSION['company_id'], 'Item Name', 'BARCODE123', 100, 'Warehouse A');
// $inventory_management->updateStockItem($_SESSION['company_id'], 1, 'Updated Item Name', 'BARCODE456', 150, 'Warehouse B');
// $inventory_management->deleteStockItem($_SESSION['company_id'], 2);
// $inventory_management->addOutboundItem($_SESSION['company_id'], 1, 10, '2023-06-15', '14:00:00', 'John Doe', '123 Main St, City');
// $inventory_management->updateOutboundItem($_SESSION['company_id'], 1, 1, 15, '2023-06-16', '15:00:00', 'Jane Doe', '456 Elm St, Town', 'in_transit');
// $inventory_management->addInboundItem($_SESSION['company_id'], 1, 50, '2023-06-17', '10:00:00', 'Supplier Inc.');
// $inventory_management->updateInboundItem($_SESSION['company_id'], 1, 1, 60, '2023-06-18', '11:00:00', 'New Supplier Ltd.', 'received');
?>
