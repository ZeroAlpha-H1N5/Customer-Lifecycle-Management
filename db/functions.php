<?php
//----- Database Configuration & Session Starter -----//
session_start();
require_once 'db_config.php';
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
//----- User Input Sanitization Function -----//
function sanitize_input($data) {
    if ($data === null) {
        return null;
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
//----- User Authentication -----//
function authenticate_user($username, $password) {
    $conn = db_connect();
    $username = sanitize_input($username);
    $sql = "SELECT user_id, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $row['role'];
            $conn->close();
            return true;
        } else {
            $conn->close();
            return false;
        }
    } else {
        $conn->close();
        return false;
    }
}
//----- Session/User Checker -----//
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
//----- User Role/Permission Validation -----//
function has_permission($required_role) {
    if (is_logged_in()) {
        return ($_SESSION['role'] === $required_role);
    }
    return false;
}
?>