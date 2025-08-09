<?php
//----- Database Connection -----//
require_once './db/functions.php';
if (is_logged_in()) {
    header("Location: home.php");
    exit;
}
//----- User Validation & Error Handling -----//
$login_error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST["username"]);
    $password = $_POST["password"];
    if (authenticate_user($username, $password)) {
        header("Location: home.php");
        exit;
    } else {
        $login_error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
</head>
<!-- Login Form -->
<body class="login-body">
    <div class="login-container">
        <div class="login-logo-container">
            <img src="icons/safexpress_logo.png" alt="SafeXpress Logistics Logo">
        </div>
        <?php if (!empty($login_error)) { ?>
            <p style="color: red;"><?php echo $login_error; ?></p>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" id="username" name="username" placeholder="Username"><br><br>
            <input type="password" id="password" name="password" placeholder="Password"><br><br>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>