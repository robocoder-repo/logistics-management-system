
<?php
require_once 'auth.php';
require_once 'inventory_management.php';

// Include database configuration
require_once 'db_config.php';

$auth = new Auth($db);
$inventory_management = new InventoryManagement($db, $auth, null);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stock_id = $_POST['stock_id'];
    $quantity = $_POST['quantity'];
    $arrival_date = $_POST['arrival_date'];
    $arrival_time = $_POST['arrival_time'];
    $supplier = $_POST['supplier'];

    $result = $inventory_management->addInboundItem($company_id, $stock_id, $quantity, $arrival_date, $arrival_time, $supplier);

    if ($result) {
        $success_message = "Inbound item added successfully.";
    } else {
        $error_message = "Failed to add inbound item.";
    }
}

// Fetch inbound items
$inbound_items = $inventory_management->getInboundItems($company_id);

// Fetch stock items for the dropdown
$stock_items = $inventory_management->getStockItems($company_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbound - Logistics Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Inbound Items</h1>
    
    <?php
    if (isset($success_message)) {
        echo "<p class='success'>$success_message</p>";
    }
    if (isset($error_message)) {
        echo "<p class='error'>$error_message</p>";
    }
    ?>

    <h2>Add New Inbound Item</h2>
    <form action="inbound.php" method="post">
        <select name="stock_id" required>
            <option value="">Select Stock Item</option>
            <?php foreach ($stock_items as $item): ?>
                <option value="<?php echo $item['id']; ?>"><?php echo $item['item_name']; ?></option>
            <?php endforeach; ?>
        </select><br>
        <input type="number" name="quantity" placeholder="Quantity" required><br>
        <input type="date" name="arrival_date" required><br>
        <input type="time" name="arrival_time" required><br>
        <input type="text" name="supplier" placeholder="Supplier" required><br>
        <input type="submit" value="Add Inbound Item">
    </form>

    <h2>Inbound Items List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Arrival Date</th>
            <th>Arrival Time</th>
            <th>Supplier</th>
            <th>Status</th>
        </tr>
        <?php foreach ($inbound_items as $item): ?>
        <tr>
            <td><?php echo $item['id']; ?></td>
            <td><?php echo $item['item_name']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td><?php echo $item['arrival_date']; ?></td>
            <td><?php echo $item['arrival_time']; ?></td>
            <td><?php echo $item['supplier']; ?></td>
            <td><?php echo $item['status']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
