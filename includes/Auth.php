<?php
class Auth {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Register new user
     */
    public function register($username, $email, $password, $full_name) {
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Check if user exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User registered successfully'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        $stmt = $this->conn->prepare("SELECT id, username, email, password, full_name, role, status FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        $user = $result->fetch_assoc();

        // Check if user is active
        if ($user['status'] === 'inactive') {
            return ['success' => false, 'message' => 'Your account has been deactivated'];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        return ['success' => true, 'message' => 'Login successful'];
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user
     */
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['role'] === 'admin';
    }

    /**
     * Logout user
     */
    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
        return true;
    }

    /**
     * Update user password
     */
    public function changePassword($user_id, $old_password, $new_password) {
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $user = $result->fetch_assoc();

        if (!password_verify($old_password, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }
}
