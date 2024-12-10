<?php

session_start(); //this starts the session

include 'db.php'; //this is linking the database connection file

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'management') {
    header('Location: login.php');
    exit; //checks if is user has a session active and if the role isnt management then it redirects back to the login page
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit; //this unsets and destroys the session and redirects to the login page when logout button is pressed
}

//deletes menu items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    $delete_item_id = $_POST['delete_item_id'];
    $stmt = $pdo->prepare("DELETE FROM menuitems WHERE item_id = :item_id");
    $stmt->execute(['item_id' => $delete_item_id]);
    //prepares and executes the query to delete the item with the matching item_id
}

//this handles the POST request to update stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stock'])) {
    foreach ($_POST['stock'] as $item_id => $new_stock) {
        if (is_numeric($new_stock) && $new_stock !== '') { //this checks if the input is a number and is not empty
            $stmt = $pdo->prepare("UPDATE menuitems SET stock = :stock WHERE item_id = :item_id");
            $stmt->execute(['stock' => $new_stock, 'item_id' => $item_id]);
            //this prepares and executes the query to update the stock for the menuitem whichs id matches the input
        }
    }
}

if (isset($_POST['name']) && isset($_POST['price'])) { //handles the POST request for item names and price updating
    foreach ($_POST['name'] as $item_id => $new_name) {
        if (!empty($new_name)) { //checks if the new name is not empty
            $stmt = $pdo->prepare("UPDATE menuitems SET item_name = :item_name WHERE item_id = :item_id");
            $stmt->execute(['item_name' => $new_name, 'item_id' => $item_id]);
            //this prepares and executes the query to update the menu item name where the item name matches the items id
        }
    }
    
    foreach ($_POST['price'] as $item_id => $new_price) { //handles the POST request to update names and price
        if (is_numeric($new_price) && $new_price !== '') { //checks if the new price is a number and not empty
            $stmt = $pdo->prepare("UPDATE menuitems SET price = :price WHERE item_id = :item_id");
            $stmt->execute(['price' => $new_price, 'item_id' => $item_id]);
            //this perpares and executes the query to update the price where the item ids match
        }
    }
}

//new item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-btn'])) {
    $new_name = trim($_POST['new_item_name']);
    $new_price = $_POST['new_item_price'];
    $new_stock = $_POST['new_item_stock'];

    //checks if an item with the same name already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM menuitems WHERE item_name = :item_name");
    $stmt->execute(['item_name' => $new_name]);

    if ($stmt->fetchColumn() == 0) { 
        //if no item with the same name exist it inserts the new item
        $stmt = $pdo->prepare("INSERT INTO menuitems (item_name, price, stock) VALUES (:item_name, :price, :stock)");
        $stmt->execute([
            'item_name' => $new_name,
            'price' => $new_price,
            'stock' => $new_stock,
        ]);
    }
}


$query = $pdo->query("SELECT * FROM menuitems");
$items = $query->fetchAll(PDO::FETCH_ASSOC); //this gets everything from the table menuitems


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'register_admin') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirmpassword = $_POST['confirmpassword'];
        //this checks if the register admin form has been submited and stores the details

        //this hashes the password and stores it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        //this prepaers and executes a statment which gets everything from the table users where username matches the stored one

        if (strlen($username) <= 3) { //this is checking if the username is under or equal to 3 characters
            $errormsg = "Username must be more than 3 character long";
            //shows this error message if username is under or equal to 3 characters

        } elseif (strlen($password) <= 7) { //this is checking if the password is under or equal to 7 characters
            $errormsg = "Password must be at least 8 characters long";
            //shows an error message if password is under or equal to 7 characters

        } elseif ($password != $confirmpassword) { //this checks if the password and confirm password match
            $errormsg = "Password and confirm password do not match";

        } elseif ($stmt->rowCount() > 0) { //checking if any rows are returned for same username to see if already registered or not
            $errormsg = "Username already taken. Please choose another one";
            //shows this error message if already registered

        } else { //otherwise it just inserts the login details into the database
            $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
            $insert->execute(['username' => $username, 'password' => $hashedPassword, 'role' => 'management']);
        
            $goodmsg = "Admin Registration Successful"; //shows a good message to the user
        }
    }
}

?>




<link rel="stylesheet" href="style.css"> <!--this links to the css file-->
<title>Admin Dashboard</title>
<div class="edit-details-container">
    <div class="main-container-stock">
        <h2>Stock</h2>
        <div class="items-container">
            <form method="POST" action=""> <!--this is the form for updating the stock-->
                <?php foreach ($items as $item): ?> <!--this is a loop which goes through every menu item in table-->
                    <div>
                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong><br><br> <!--shows item name-->
                        <p>Stock: <?php echo htmlspecialchars($item['stock']); ?></p> <!--shows item stock-->
                        <input type="number" min="0" max="1000" name="stock[<?php echo htmlspecialchars($item['item_id']); ?>]" placeholder="Edit Stock">
                        <!--the echo htmlspecialchars item id gets the item id of the item to loop the correct item id to the correct input box-->
                    </div> <!--the min and max value prevent the input from being extreme-->
                <?php endforeach; ?>
                <button type="submit" class="save-button">Update Stock</button> <!--this is the save/update buton-->
            </form>
        </div>
    </div>
    <div class="main-container-details">
        <div class="adminlogoutcontainer">
            <h2>Menu Item Details</h2>
            <button class="logout-btn" id="logout-btn">Logout</button> <!--this is the logout button-->
        </div>
        <div class="items-container">
            <form method="POST" action=""> <!--this is the form to update the price of a menu item-->
                <?php foreach ($items as $item): ?> <!--also looks through every menu item-->
                    <div>
                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong><br><br>
                        <p>Price: £<?php echo htmlspecialchars($item['price']); ?></p>
                        <input type="name" name="name[<?php echo htmlspecialchars($item['item_id']); ?>]" placeholder="Edit Name">
                        <input type="number" min="0" max="100" step="0.01" name="price[<?php echo htmlspecialchars($item['item_id']); ?>]" placeholder="Edit Price">
                        <!--this basically displays the item details for the admin editing details contaniner with the correct item id for the input boxes-->
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="save-button">Update Details</button> <!--this is the save/update button-->
            </form>
        </div>
    </div>
    <div two-admin-box>
        <div class="new-admin">
            <h2>Register New Admin</h2><br>
            <form method="POST" class="new-admin-form"> <!--tihs is the form to register a new admin-->
                <input type="hidden" name="form_type" value="register_admin">
                <label for="username">Username: </label>
                <input type="text" id="username" name="username" placeholder="Enter Username" required><br>
                <!--this is the username input box-->
        
                <label for="password">Password: </label>
                <input type="password" id="password" name="password" placeholder="Enter Password" required><br>
                <!--password box which masks the password when entering-->

                <label for="confirmpassword">Confirm Password: </label>
                <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm Password" required><br>
                <!--password confirm to ensure the password is correct-->
                
                <?php if(isset($errormsg)): ?>
                    <div class="error-box">
                        <p><?php echo $errormsg; ?></p>
                    </div>
                <?php endif; ?> <!--these displays the messages-->

                <?php if(isset($goodmsg)): ?>
                    <div class="good-box">
                        <p><?php echo $goodmsg; ?></p>
                    </div>
                <?php endif; ?><br>           

                <input type="submit" value="Register Admin"> <!--this is the save/register button-->
            </form>
        </div>
        <div class="addanddelete-container">
            <div class="add-section">
                <form method="POST"> <!--this is the form for adding a new menu item-->
                    <div class="inline">
                        <label class="diff-label" for="new-name">Item Name:</label>
                        <input type="text" id="new-name" name="new_item_name" placeholder="Enter Name" required><br>
                    </div> <!--labels and input-->

                    <div class="inline">
                        <label class="diff-label" for="new-price">Item Price:</label>
                        <input type="number" id="new-price" name="new_item_price" min="0.00" max="100.00" step="0.01" placeholder="Enter Price" required><br>
                    </div>

                    <div class="inline">
                        <label class="diff-label" for="new-stock">Item Stock:</label>
                        <input type="number" id="new-stock" name="new_item_stock" min="0" max="10000" step="1" placeholder="Enter Stock" required>
                    </div>
                    
                    <button type="submit" name="add-btn" class="addbtn">Add</button> <!--save/submit button-->
                </form>
            </div>
            <hr><br>
            <div class="delete-section">
                <form method="POST"> <!--this is the form for deleting menu items-->
                    <div class="inline">
                        <label class="diff-label" for="select-item">Delete:</label>
                        <select id="select-item" name="delete_item_id">
                            <option value="" disabled selected>Select</option>
                            <?php foreach ($items as $item): ?> <!--loops through each item and gets their name and id-->
                                <option value="<?php echo htmlspecialchars($item['item_id']); ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="deletebtn">Delete</button> <!--this is the delete/submit button-->
                </form>
            </div>
        </div>
    </div>
</div>




<script>

document.getElementById('logout-btn').addEventListener('click', () => {
    window.location.href = '?logout=true';
});
/*this listens and when the logout button has been clicked it sets the logout status to true*/

</script>