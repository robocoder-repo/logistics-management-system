<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Management System</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script src="barcode_scanner.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php
    require_once 'auth.php';
    require_once 'company_management.php';
    require_once 'admin_dashboard.php';
    require_once 'employee_dashboard.php';
    require_once 'inventory_management.php';

// Include database configuration
require_once 'db_config.php';

    $auth = new Auth($db);
    $company_management = new CompanyManagement($db, $auth);
    $admin_dashboard = new AdminDashboard($db, $auth, $company_management);
    $employee_dashboard = new EmployeeDashboard($db, $auth, $company_management);
    $inventory_management = new InventoryManagement($db, $auth, $company_management);

    // Check if user is logged in
    if ($auth->isLoggedIn()) {
        $company_id = $_SESSION['company_id'];
        $is_admin = $auth->isAdmin();

        // Display dashboard
        echo "<h1>Welcome, " . htmlspecialchars($_SESSION['username']) . "</h1>";
        echo "<div class='dashboard'>";
        
        // Display summary boxes
        $summary = $is_admin ? $admin_dashboard->getDashboardSummary($company_id) : $employee_dashboard->getDashboardSummary($company_id);
        
        echo "<div class='box' onclick='location.href="outbound.php"'>
                <h2>Outbound</h2>
                <p>Today's deliveries: " . htmlspecialchars($summary['outbound_today']) . "</p>
              </div>";
        echo "<div class='box' onclick='location.href="inbound.php"'>
                <h2>Inbound</h2>
                <p>Today's arrivals: " . htmlspecialchars($summary['inbound_today']) . "</p>
              </div>";
        echo "<div class='box' onclick='location.href="stocks.php"'>
                <h2>Stocks</h2>
                <p>Total items: " . htmlspecialchars($summary['total_stock_items']) . "</p>
              </div>";
        echo "<div class='box' onclick='openBarcodeScanner()'>
                <h2>Barcode Scanner</h2>
                <p>Click to scan</p>
              </div>";
        
        echo "</div>";

        // Add logout button
        echo "<button onclick='location.href="logout.php"'>Logout</button>";

    } else {
        // Display login form
        echo "<h1>Login</h1>";
        echo "<form action='login.php' method='post'>
                <input type='text' name='username' placeholder='Username' required><br>
                <input type='password' name='password' placeholder='Password' required><br>
                <input type='submit' value='Login'>
              </form>";
    }
    ?>

    <div id="scanner-container" style="display: none;"></div>

    <script>
        function openBarcodeScanner() {
            document.getElementById('scanner-container').style.display = 'block';
            initBarcodeScanner(function(barcode) {
                alert("Detected barcode: " + barcode);
                // Here you can send the barcode to your server or perform any other action
                document.getElementById('scanner-container').style.display = 'none';
            });
        }
    </script>
</body>
</html>
