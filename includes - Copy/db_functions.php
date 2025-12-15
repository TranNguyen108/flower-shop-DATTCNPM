<?php
/**
 * Database Security Functions
 * Prepared Statements để bảo mật SQL Injection
 * Updated: December 2025
 */

// SELECT query với prepared statement - Returns mysqli_result
function db_select($conn, $query, $types = "", $params = []) {
    if (!empty($types) && !empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return false;
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }
    return mysqli_query($conn, $query);
}

// SELECT query trả về array - For chat system
function db_select_array($conn, $query, $types = "", $params = []) {
    if (!empty($types) && !empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($conn));
            return [];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    $result = mysqli_query($conn, $query);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

// INSERT query và trả về ID
function db_insert($conn, $query, $types, $params) {
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        return false;
    }
    return mysqli_insert_id($conn);
}

// UPDATE query
function db_update($conn, $query, $types, $params) {
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    return mysqli_stmt_execute($stmt);
}

// DELETE query
function db_delete($conn, $query, $types, $params) {
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    return mysqli_stmt_execute($stmt);
}

// COUNT query
function db_count($conn, $query, $types = "", $params = []) {
    $result = db_select($conn, $query, $types, $params);
    if ($result) {
        return mysqli_num_rows($result);
    }
    return 0;
}

// Lấy 1 row duy nhất
function db_fetch_one($conn, $query, $types = "", $params = []) {
    $result = db_select($conn, $query, $types, $params);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// CSRF Token Functions
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// XSS Protection - Escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

// Attempt to fix common UTF-8/latin1 mojibake
// Heuristic: try converting from Windows-1252/ISO-8859-1 to UTF-8 when suspicious chars appear
function fix_encoding($str) {
    if (!is_string($str) || $str === '') return $str;
    // If string already valid UTF-8, return as-is
    if (mb_detect_encoding($str, 'UTF-8', true) === 'UTF-8' && !preg_match('/[\xEF\xBF\xBD]/', $str)) {
        return $str;
    }
    // Try iconv from CP1252 → UTF-8
    $converted = @iconv('CP1252', 'UTF-8//IGNORE', $str);
    if ($converted !== false && $converted !== '') {
        // If still contains replacement char, try ISO-8859-1
        if (preg_match('/[\xEF\xBF\xBD]/', $converted)) {
            $converted2 = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $str);
            if ($converted2 !== false && $converted2 !== '') {
                return $converted2;
            }
        }
        return $converted;
    }
    // Fallback: strip invalid sequences
    return @iconv('UTF-8', 'UTF-8//IGNORE', $str);
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone (Vietnamese format)
function validate_phone($phone) {
    return preg_match('/^(0|\+84)(\s|\.)?((3[2-9])|(5[689])|(7[06-9])|(8[1-689])|(9[0-46-9]))(\d)(\s|\.)?(\d{3})(\s|\.)?(\d{3})$/', $phone);
}

// Password hashing (thay thế MD5)
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Generate random string
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Secure file upload
function secure_filename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    return substr($name, 0, 50) . '_' . time() . '.' . $ext;
}

// Validate image upload
function validate_image_upload($file, $max_size = 2097152) { // 2MB default
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Lỗi upload file'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File quá lớn (tối đa ' . ($max_size / 1024 / 1024) . 'MB)'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowed_types)) {
        return ['success' => false, 'message' => 'Định dạng file không được phép'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        return ['success' => false, 'message' => 'Phần mở rộng file không hợp lệ'];
    }
    
    return ['success' => true, 'message' => 'OK'];
}
?>
