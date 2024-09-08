<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'stock_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Add New Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO stocks (item_name, quantity, price, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sidi', $item_name, $quantity, $price, $user_id);
    if ($stmt->execute()) {
        $success_message = "Stock item added successfully!";
    } else {
        $error_message = "Error adding item: " . $stmt->error;
    }
}

// Edit Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_item'])) {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['edit_item_name'];
    $quantity = $_POST['edit_quantity'];
    $price = $_POST['edit_price'];

    $stmt = $conn->prepare("UPDATE stocks SET item_name=?, quantity=?, price=? WHERE id=? AND user_id=?");
    $stmt->bind_param('sidii', $item_name, $quantity, $price, $item_id, $user_id);
    if ($stmt->execute()) {
        $success_message = "Stock item updated successfully!";
    } else {
        $error_message = "Error updating item: " . $stmt->error;
    }
}

// Delete Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
    $item_id = $_POST['item_id'];

    $stmt = $conn->prepare("DELETE FROM stocks WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $item_id, $user_id);
    if ($stmt->execute()) {
        $success_message = "Stock item deleted successfully!";
    } else {
        $error_message = "Error deleting item: " . $stmt->error;
    }
}

// Fetch all items
$stocks = $conn->query("SELECT * FROM stocks WHERE user_id='$user_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Stock Management</title>
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

        form input[type="text"], form input[type="number"] {
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
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
        <a href="logout.php" class="btn" style="float: right;">Logout</a>

        <div class="nav-buttons">
            <a href="billing_dashboard.php">Go to Billing Dashboard</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Add New Stock Item -->
        <section>
            <h3>Add New Stock Item</h3>
            <form method="POST" action="">
                <label for="item_name">Item Name:</label>
                <input type="text" id="item_name" name="item_name" required>

                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" required>

                <label for="price">Price:</label>
                <input type="text" id="price" name="price" required>

                <input type="submit" name="add_item" value="Add Item">
            </form>
        </section>

        <!-- Edit and Delete Stock Items -->
        <section class="table-wrapper">
            <h3>Your Stock Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stocks->num_rows > 0): ?>
                        <?php while ($row = $stocks->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                <td>
                                    <!-- Edit form -->
                                    <form method="POST" action="" class="form-inline">
                                        <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="edit_item_name" value="<?php echo htmlspecialchars($row['item_name']); ?>" required>
                                        <input type="number" name="edit_quantity" value="<?php echo htmlspecialchars($row['quantity']); ?>" required>
                                        <input type="text" name="edit_price" value="<?php echo htmlspecialchars($row['price']); ?>" required>
                                        <input type="submit" name="edit_item" value="Update" class="btn">
                                    </form>

                                    <!-- Delete form -->
                                    <form method="POST" action="" class="form-inline">
                                        <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                                        <input type="submit" name="delete_item" value="Delete" class="btn btn-danger">
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No stock items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
