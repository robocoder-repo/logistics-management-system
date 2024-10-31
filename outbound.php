
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
    $delivery_date = $_POST['delivery_date'];
    $delivery_time = $_POST['delivery_time'];
    $recipient = $_POST['recipient'];
    $delivery_address = $_POST['delivery_address'];

    $result = $inventory_management->addOutboundItem($company_id, $stock_id, $quantity, $delivery_date, $delivery_time, $recipient, $delivery_address);

    if ($result) {
        $success_message = "Outbound item added successfully.";
    } else {
        $error_message = "Failed to add outbound item.";
    }
}

// Fetch outbound items
$outbound_items = $inventory_management->getOutboundItems($company_id);

// Fetch stock items for the dropdown
$stock_items = $inventory_management->getStockItems($company_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outbound - Logistics Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Outbound Items</h1>
    
    <?php
    if (isset($success_message)) {
        echo "<p class='success'>$success_message</p>";
    }
    if (isset($error_message)) {
        echo "<p class='error'>$error_message</p>";
    }
    ?>

    <h2>Add New Outbound Item</h2>
    <form action="outbound.php" method="post">
        <select name="stock_id" required>
            <option value="">Select Stock Item</option>
            <?php foreach ($stock_items as $item): ?>
                <option value="<?php echo $item['id']; ?>"><?php echo $item['item_name']; ?> (<?php echo $item['quantity']; ?> available)</option>
            <?php endforeach; ?>
        </select><br>
        <input type="number" name="quantity" placeholder="Quantity" required><br>
        <input type="date" name="delivery_date" required><br>
        <input type="time" name="delivery_time" required><br>
        <input type="text" name="recipient" placeholder="Recipient" required><br>
        <textarea name="delivery_address" placeholder="Delivery Address" required></textarea><br>
        <input type="submit" value="Add Outbound Item">
    </form>

    <h2>Outbound Items List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Delivery Date</th>
            <th>Delivery Time</th>
            <th>Recipient</th>
            <th>Delivery Address</th>
            <th>Status</th>
        </tr>
        <?php foreach ($outbound_items as $item): ?>
        <tr>
            <td><?php echo $item['id']; ?></td>
            <td><?php echo $item['item_name']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td><?php echo $item['delivery_date']; ?></td>
            <td><?php echo $item['delivery_time']; ?></td>
            <td><?php echo $item['recipient']; ?></td>
            <td><?php echo $item['delivery_address']; ?></td>
            <td><?php echo $item['status']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
