<?php

session_start(); //start the session to check if the user is logged in

include 'db.php'; //include the database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit; //redirect if the user is not logged in
}

//if the form is submitted, retrieve the ordered items
$ordered_items = isset($_POST['items']) ? $_POST['items'] : [];
$totalPrice = 0.00;

if (!empty($ordered_items)) {
    //update the stock in the database
    foreach ($ordered_items as $item_name => $quantity) {
        //fetch the current stock for the item
        $stmt = $pdo->prepare("SELECT stock FROM menuitems WHERE item_name = :item_name");
        $stmt->execute(['item_name' => $item_name]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item && $item['stock'] >= $quantity) {
            //update stock: reduce it by the quantity ordered
            $newStock = $item['stock'] - $quantity;
            $updateStmt = $pdo->prepare("UPDATE menuitems SET stock = :newStock WHERE item_name = :item_name");
            $updateStmt->execute([
                'newStock' => $newStock,
                'item_name' => $item_name
            ]);
        }
    }
}

?>




<meta charset="UTF-8">
<link rel="stylesheet" href="style.css"> <!--links the sylesheet-->
<title>Order Complete</title>
<div class="main-container">
    <h2 class="order-complete">Order Complete &#x2713;</h2>
    <div class="receipt-container-two">
        <h3>Receipt</h3><br>
        <ul id="receipt-list">
            <?php if (empty($ordered_items)): ?> <!--says no items ordered if empty-->
                <li>No items ordered.</li>
            <?php else: ?>
                <?php
                $totalPrice = 0;
                foreach ($ordered_items as $item_name => $quantity):
                    //fetch item details from the database for display
                    $stmt = $pdo->prepare("SELECT * FROM menuitems WHERE item_name = :item_name");
                    $stmt->execute(['item_name' => $item_name]);
                    $item = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($item):
                        $itemPrice = $item['price'] * $quantity;
                        $totalPrice += $itemPrice;
                ?>
                <li><?php echo htmlspecialchars($item['item_name']); ?> X <?php echo htmlspecialchars($quantity); ?> - £<?php echo number_format($itemPrice, 2); ?></li>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul><br>
        <p><strong>Total: £<span id="total-price"><?php echo number_format($totalPrice, 2); ?></span></strong></p><br>
        <button id="start-again-btn" class="finish-btn">New Order</button> <!--new order button-->
    </div>
</div>




<script>

//redirect to the main ordering dashboard when Start Again is clicked
document.getElementById('start-again-btn').addEventListener('click', () => {
    window.location.href = 'normaldashboard.php';
});

</script>