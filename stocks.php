
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
$is_admin = $auth->isAdmin();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_admin) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $item_name = $_POST['item_name'];
                $barcode = $_POST['barcode'];
                $quantity = $_POST['quantity'];
                $location = $_POST['location'];
                $result = $inventory_management->addStockItem($company_id, $item_name, $barcode, $quantity, $location);
                if ($result) {
                    $success_message = "Stock item added successfully.";
                } else {
                    $error_message = "Failed to add stock item.";
                }
                break;
            case 'edit':
                $stock_id = $_POST['stock_id'];
                $item_name = $_POST['item_name'];
                $barcode = $_POST['barcode'];
                $quantity = $_POST['quantity'];
                $location = $_POST['location'];
                $result = $inventory_management->updateStockItem($company_id, $stock_id, $item_name, $barcode, $quantity, $location);
                if ($result) {
                    $success_message = "Stock item updated successfully.";
                } else {
                    $error_message = "Failed to update stock item.";
                }
                break;
            case 'delete':
                $stock_id = $_POST['stock_id'];
                $result = $inventory_management->deleteStockItem($company_id, $stock_id);
                if ($result) {
                    $success_message = "Stock item deleted successfully.";
                } else {
                    $error_message = "Failed to delete stock item.";
                }
                break;
        }
    }
}

// Handle search
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search_term)) {
    $stock_items = $inventory_management->searchStockItems($company_id, $search_term);
} else {
    $stock_items = $inventory_management->getStockItems($company_id);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks - Logistics Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Stock Items</h1>
    
    <?php
    if (isset($success_message)) {
        echo "<p class='success'>$success_message</p>";
    }
    if (isset($error_message)) {
        echo "<p class='error'>$error_message</p>";
    }
    ?>

    <?php if ($is_admin): ?>
    <h2>Add New Stock Item</h2>
    <form action="stocks.php" method="post">
        <input type="hidden" name="action" value="add">
        <input type="text" name="item_name" placeholder="Item Name" required><br>
        <input type="text" name="barcode" placeholder="Barcode" required><br>
        <input type="number" name="quantity" placeholder="Quantity" required><br>
        <input type="text" name="location" placeholder="Location" required><br>
        <input type="submit" value="Add Stock Item">
    </form>
    <?php endif; ?>

    <h2>Search Stock Items</h2>
    <form action="stocks.php" method="get">
        <input type="text" name="search" placeholder="Search by name or barcode" value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="submit" value="Search">
    </form>

    <h2>Stock Items List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Barcode</th>
            <th>Quantity</th>
            <th>Location</th>
            <?php if ($is_admin): ?>
            <th>Actions</th>
            <?php endif; ?>
        </tr>
        <?php foreach ($stock_items as $item): ?>
        <tr>
            <td><?php echo $item['id']; ?></td>
            <td><?php echo $item['item_name']; ?></td>
            <td><?php echo $item['barcode']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td><?php echo $item['location']; ?></td>
            <?php if ($is_admin): ?>
            <td>
                <form action="stocks.php" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="stock_id" value="<?php echo $item['id']; ?>">
                    <input type="text" name="item_name" value="<?php echo $item['item_name']; ?>" required>
                    <input type="text" name="barcode" value="<?php echo $item['barcode']; ?>" required>
                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" required>
                    <input type="text" name="location" value="<?php echo $item['location']; ?>" required>
                    <input type="submit" value="Update">
                </form>
                <form action="stocks.php" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="stock_id" value="<?php echo $item['id']; ?>">
                    <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this item?');">
                </form>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
