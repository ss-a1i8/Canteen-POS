<?php

session_start(); //starts the session

include 'db.php'; //this includes the database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit; //if their is not a session active it redirects to login page
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit; //this unsets and destroys a session
}

$query = $pdo->query("SELECT * FROM menuitems");
$items = $query->fetchALL(PDO::FETCH_ASSOC);
//this gets everything from the table menu items in the database
?>




<title>Main Dashboard</title>
<link rel="stylesheet" href="style.css">
<div class="main-container">
    <div class="logoutheading">
        <h2>Canteen Menu Items</h2>
        <button class="logout-btn" id="logout-btn">Logout</button> <!--this is the logout button-->
    </div>
    <div class="grid-container">
        <?php foreach ($items as $item): ?> <!--loops through every menu item-->
            <?php if($item["stock"] > 0): ?> <!--this checks if the stock is more than zero-->
                <button class="menu-items"
                    data-name="<?php echo htmlspecialchars($item["item_name"]); ?>"
                    data-price="<?php echo htmlspecialchars($item["price"]); ?>">
                    <strong>Item: </strong><?php echo htmlspecialchars($item["item_name"]); ?> <br><br>
                    <strong>Stock: </strong><?php echo htmlspecialchars($item["stock"]); ?> <br><br>
                    <strong>Price: </strong>£<?php echo htmlspecialchars($item["price"]); ?> <br><br>
                    <!--displays the button for menu items with details such as item, stock and price from database-->
                </button>
            <?php else: ?>
                <button class="menu-items" disabled> <!--this displays the disabled buttons with no stock-->
                    <strong>Item: </strong><?php echo htmlspecialchars($item["item_name"]); ?> <br><br>
                    <strong>Stock: </strong><?php echo htmlspecialchars($item["stock"]); ?> <br><br>
                    <strong>Price: </strong>£<?php echo htmlspecialchars($item["price"]); ?> <br><br>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="receipt-container">
        <h3>Receipt</h3>
        <ul id="receipt-list"></ul>
        <p><strong>Total: £<span id="total-price">0.00</span></strong></p>
        <form method="POST" id="finish-form"> <!--this posts the details of the form-->
        </form>
        <div class="reciept-buttons">
            <button id="reset-receipt" class="reset-button">Reset</button> <!--this is the reset button-->
            <button id="finishbtn" class="finish-button">Finish</button> <!--this is the finish button-->
        </div>
    </div>
</div>




<script>

document.getElementById('logout-btn').addEventListener('click', () => {
    window.location.href = '?logout=true';
}); //this is the logout functionality

document.querySelectorAll('.menu-items').forEach(button => {
    button.addEventListener('click', () => {
        const itemName = button.getAttribute('data-name');
        const itemPrice = parseFloat(button.getAttribute('data-price'));

        //this gets the receipt list and check if this item is already listed
        const receiptList = document.getElementById('receipt-list');
        let listItem = receiptList.querySelector(`li[data-name="${itemName}"]`);

        if (!listItem) {
            //if the item is not in the receipt yet, create a new list item
            listItem = document.createElement('li');
            listItem.setAttribute('data-name', itemName);
            listItem.textContent = `${itemName} - £${itemPrice.toFixed(2)} X 1`;
            receiptList.appendChild(listItem);
        } else {
            //if the item is already in the receipt, update the quantity
            const currentQuantity = parseInt(listItem.textContent.match(/X (\d+)/)[1]);  // Find the current quantity
            listItem.textContent = `${itemName} - £${itemPrice.toFixed(2)} X ${currentQuantity + 1}`;
        }

        //update the total price based on the new or updated item
        const totalPriceElem = document.getElementById('total-price');
        const currentTotal = parseFloat(totalPriceElem.textContent);
        totalPriceElem.textContent = (currentTotal + itemPrice).toFixed(2);

        //update the hidden form input for the item quantity
        const form = document.getElementById('finish-form');
        let input = form.querySelector(`input[name="items[${itemName}]"]`);

        if (!input) {
            //add a new hidden input for the item if it doesn't exist
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${itemName}]`;
            input.value = '1';
            form.appendChild(input);
        } else {
            //update the existing input's value by increasing the quantity
            input.value = parseInt(input.value) + 1;
        }
    });
});


//this is to reset the reciept
document.getElementById('reset-receipt').addEventListener('click', () => {
    //this clears the actual reciept list and total price
    const receiptList = document.getElementById('receipt-list');
    receiptList.innerHTML = '';
    document.getElementById('total-price').textContent = '0.00';
    //this clears the form input
    document.getElementById('finish-form').innerHTML = '';
});

//this is to finish the order
document.getElementById('finishbtn').addEventListener('click', () => {
    const receiptList = document.getElementById('receipt-list');
    if (receiptList.children.length === 0) {
        return;
    }
    //this submits the form
    const form = document.getElementById('finish-form');
    form.action = 'ordercomplete.php';
    form.submit();
});

</script>