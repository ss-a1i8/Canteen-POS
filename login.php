<?php

session_start(); //this starts the session

include 'db.php'; //this links the database file

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $_POST['username'];
    $password = $_POST['password']; //this gets the posted username and password and stores it

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    //this prepares and executes a statement which gets everything for the username and stores it in users

    if ($user && password_verify($password, $user['password'])) {
    //password verify will automatically un-hash the password and see if it matches
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; //this stores the user_id, username and role in the session

        if ($user['role'] == 'management') {
            header("Location: admindashboard.php"); //takes the user to admin dashboard if their role is 'management'
        } else {
            header("Location: normaldashboard.php"); //this takes the normal user to normal dashboard
        }
        exit();
    } else {
        $errormsg = "Incorrect username or password"; //this sets the error message
    }
}
?>




<link rel="stylesheet" href="style.css">
<title>Canteen Login</title>
<div class="container">
    <h2>Login</h2>
    <form action="login.php" method="POST"> <!--this is the form to post the login details-->
        <label for="username">Username: </label>
        <input type="text" id="username" name="username" required placeholder="Enter Username"><br>
        <!--this is the input box-->

        <label for="password">Password: </label>
        <input type="password" id="password" name="password" required placeholder="Enter Password"><br>
        <!--this an input box-->

        <?php if (isset($errormsg)): ?> <!--this diplays the error message-->
            <div class="error-box">
                <p><?php echo $errormsg; ?></p>
            </div>
        <?php endif; ?><br>
        
        <input type="submit" value="Login"><br><br> <!--this is the submit button-->

        <a href="register.php" class="login-link">Not got an account? Register</a>
        <!--this is the link which goes to register-->
    </form>
</div>