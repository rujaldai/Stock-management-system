<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'stock_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Generate Invoice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_invoice'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['invoice_quantity'];

    $stmt = $conn->prepare("SELECT item_name, price, quantity FROM stocks WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $item_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $item_name = $item['item_name'];
        $price = $item['price'];
        $available_quantity = $item['quantity'];

        if ($quantity > $available_quantity) {
            $error_message = "Not enough stock available.";
        } else {
            $total = $price * $quantity;

            $stmt = $conn->prepare("INSERT INTO invoices (item_name, quantity, total, user_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sidi', $item_name, $quantity, $total, $user_id);
            if ($stmt->execute()) {
                // Update stock quantity
                $new_quantity = $available_quantity - $quantity;
                $stmt = $conn->prepare("UPDATE stocks SET quantity=? WHERE id=? AND user_id=?");
                $stmt->bind_param('iii', $new_quantity, $item_id, $user_id);
                $stmt->execute();

                $success_message = "Invoice generated successfully!";
            } else {
                $error_message = "Error generating invoice: " . $stmt->error;
            }
        }
    } else {
        $error_message = "Item not found.";
    }
}

// Fetch all items for billing
$items = $conn->query("SELECT id, item_name, quantity FROM stocks WHERE user_id='$user_id'");

// Fetch all invoices
$invoices = $conn->query("SELECT * FROM invoices WHERE user_id='$user_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Dashboard - Stock Management</title>
    <style>
       body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            background: url('11667324_20946011.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            width: 900px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }

        .alert-success {
            background-color: #4CAF50;
            color: white;
        }

        .alert-danger {
            background-color: #f44336;
            color: white;
        }

        form {
            margin-bottom: 20px;
        }

        form label {
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: bold;
            color: #666;
        }

        form input[type="text"], form input[type="number"], form select {
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            width: 100%;
        }

        form input[type="submit"] {
            padding: 12px;
            background-color: #74ebd5;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form input[type="submit"]:hover {
            background-color: #57c5ba;
        }

        .table-wrapper {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 18px;
        }

        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #74ebd5;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .btn {
            background-color: #74ebd5;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            margin-right: 5px;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #57c5ba;
        }

        .btn-danger {
            background-color: #f44336;
            color: white;
        }

        .btn-danger:hover {
            background-color: #e63939;
        }

        .form-inline {
            display: flex;
            gap: 5px;
        }

        .form-inline input[type="submit"] {
            margin-top: 0;
            padding: 8px 10px;
            font-size: 14px;
        }

        .nav-buttons {
            margin-bottom: 20px;
            text-align: center;
        }

        .nav-buttons a {
            background-color: #74ebd5;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin: 0 10px;
        }

        .nav-buttons a:hover {
            background-color: #57c5ba;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Billing Dashboard</h2>
        <a href="dashboard.php" class="btn">Back to Stock Dashboard</a>
        <a href="logout.php" class="btn" style="float: right;">Logout</a>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Generate Invoice Section -->
        <section>
            <h3>Generate Invoice</h3>
            <form method="POST" action="">
                <label for="item_id">Select Item (Remaining Quantity):</label>
                <select id="item_id" name="item_id" required>
                    <option value="">Select item</option>
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <option value="<?php echo $item['id']; ?>">
                            <?php echo htmlspecialchars($item['item_name']) . ' (' . $item['quantity'] . ' left)'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="invoice_quantity">Quantity:</label>
                <input type="number" id="invoice_quantity" name="invoice_quantity" required>

                <input type="submit" name="generate_invoice" value="Generate Invoice">
            </form>
        </section>

        <!-- Invoice Table -->
        <section class="table-wrapper">
            <h3>Your Invoices</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($invoices->num_rows > 0): ?>
                        <?php while ($invoice = $invoices->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($invoice['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['total']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No invoices found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
