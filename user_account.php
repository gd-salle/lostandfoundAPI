<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'dbconn.php';

$data = json_decode(file_get_contents("php://input"));

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'register') {
    if (!empty($data->email) && !empty($data->username) && !empty($data->password)) {
        // Check if email or username already exists
        $email = htmlspecialchars(strip_tags($data->email));
        $username = htmlspecialchars(strip_tags($data->username));
        $password = password_hash($data->password, PASSWORD_BCRYPT); // Encrypt the password

        $checkQuery = "SELECT COUNT(*) as count FROM user_accounts WHERE email = :email OR username = :username";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            // If email already exists
            if ($row['count'] == 2) {
                echo json_encode(['success' => false, 'message' => 'Email and Username already exist.']);
            } elseif ($row['count'] == 1 && $email == $row['email']) {
                echo json_encode(['success' => false, 'message' => 'Email already exist.']);
            } elseif ($row['count'] == 1 && $username == $row['username']) {
                echo json_encode(['success' => false, 'message' => 'Username already exist.']);
            }
        } else {
            // Registration
            $query = "INSERT INTO user_accounts (email, username, password) VALUES (:email, :username, :password)";
            $stmt = $db->prepare($query);

            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User could not be registered.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incomplete data.']);
    }
} elseif ($action == 'login') {
    if (!empty($data->username) && !empty($data->password)) {
        // Login
        $username = htmlspecialchars(strip_tags($data->username));
        $password = htmlspecialchars(strip_tags($data->password));

        $query = "SELECT password FROM user_accounts WHERE username = :username";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':username', $username);

        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'An error occurred.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incomplete data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
