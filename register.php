<?php

include 'db.php';
//this links the database file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];
    //this checks if the form details have been posted and stores them

    //this hashes the password and stores it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    //this prepaers and executes a statment which gets everything from the table users where username matches the stored one


    if (strlen($username) <= 3) { //this is checking if the username is under or equal to 3 characters
        $errormsg = "Username must be more than 3 characters long";
        //shows this error message if username is under or equal to 3 characters
    
    } elseif (strlen($password) <= 7) { //this is checking if the password is under or equal to 7 characters
        $errormsg = "Password must be at least 8 characters long";
        //shows an error message if password is under or equal to 7 characters
    
    } elseif ($password != $confirmpassword) { //checks if confirm password and password match
        $errormsg = "Password and confirm password do not match";
    
    } else if ($stmt->rowCount() > 0) { //checking if any rows are returned for same username to see if already registered or not
        $errormsg = "Username already taken. Please choose another one";
        //shows this error message if already registered

    } else { //otherwise it just inserts the login details into the database
        $insert = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $insert->execute(['username' => $username, 'password' => $hashedPassword]);
    
        $goodmsg = "Registration Successful. Please log in"; //good message is set
    }
}
?>




<link rel="stylesheet" href="style.css"> <!--links the stylesheet-->
<title>Canteen Register</title>
<div class="container">
    <h2>Register</h2>
    <form action="register.php" method="POST"> <!--this is the form which posts the form details-->
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
        <?php endif; ?> <!--these are the php loops which display the messages-->

        <?php if(isset($goodmsg)): ?>
            <div class="good-box">
                <p><?php echo $goodmsg; ?></p>
            </div>
        <?php endif; ?><br>
    
        <input type="submit" value="Register"><br><br> <!--this is the submit button-->

        <a href="login.php" class="login-link">Already a user? Log in</a>
        <!--this is the redirect link which goes to register-->
    </form>
</div>