<?php
/**
 * API Gateway Configuration File
 * 
 * This file serves as a starting point for building the REST API layer
 * that connects the mobile application to the database services.
 * 
 * Usage:
 * 1. Create /api/v1/ directory
 * 2. Copy this file content to /api/v1/config.php
 * 3. Create individual endpoint files for each service
 * 4. Test each endpoint with mobile client
 */

// ============================================================================
// CORS Configuration
// ============================================================================

function setup_cors() {
    // Configure allowed origins (restrict in production)
    $allowed_origins = [
        'http://localhost:8080',
        'http://localhost:3000',
        // Add your mobile app package name here when deployed
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
    
    if ($origin && in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 3600');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// ============================================================================
// Response Handler
// ============================================================================

/**
 * Standard API Response Format
 */
function api_response($success, $data = null, $message = null, $http_code = 200) {
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => $success,
        'timestamp' => date('Y-m-d\TH:i:s\Z'),
        'version' => 'v1'
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * API Error Response
 */
function api_error($message, $error_code = null, $http_code = 400) {
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d\TH:i:s\Z'),
        'version' => 'v1'
    ];
    
    if ($error_code) {
        $response['error_code'] = $error_code;
    }
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// Authentication Token Handler
// ============================================================================

/**
 * Get authentication token from header
 */
function get_auth_token() {
    $headers = getallheaders();
    $authorization = $headers['Authorization'] ?? '';
    
    if (preg_match('/^Bearer\s+(.+)$/', $authorization, $matches)) {
        return $matches[1];
    }
    
    return null;
}

function ensure_api_auth_tokens_table() {
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $db = get_db();
    $db->exec(
        "CREATE TABLE IF NOT EXISTS api_auth_tokens (
            token_id INT AUTO_INCREMENT PRIMARY KEY,
            token_hash CHAR(64) NOT NULL,
            user_id INT NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            revoked_at DATETIME NULL,
            UNIQUE KEY unique_token_hash (token_hash),
            KEY idx_api_tokens_user (user_id),
            KEY idx_api_tokens_expiry (expires_at),
            CONSTRAINT fk_api_tokens_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );

    $initialized = true;
}

function auth_token_hash($token) {
    return hash('sha256', (string)$token);
}

function store_auth_token($token, $user_id, $ttl_seconds = 86400) {
    $user_id = intval($user_id);
    if ($user_id <= 0 || trim((string)$token) === '') {
        return false;
    }

    $hash = auth_token_hash($token);
    $expires_at = date('Y-m-d H:i:s', time() + max(60, intval($ttl_seconds)));

    if (function_exists('apcu_store')) {
        apcu_store('auth_token:' . $hash, $user_id, $ttl_seconds);
    }

    ensure_api_auth_tokens_table();
    get_db()->exec(
        "INSERT INTO api_auth_tokens (token_hash, user_id, expires_at)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), expires_at = VALUES(expires_at), revoked_at = NULL",
        [$hash, $user_id, $expires_at]
    );

    return true;
}

function revoke_auth_token($token) {
    $token = trim((string)$token);
    if ($token === '') {
        return;
    }

    $hash = auth_token_hash($token);
    if (function_exists('apcu_delete')) {
        apcu_delete('auth_token:' . $hash);
    }

    ensure_api_auth_tokens_table();
    get_db()->exec(
        "UPDATE api_auth_tokens
         SET revoked_at = NOW()
         WHERE token_hash = ? AND revoked_at IS NULL",
        [$hash]
    );
}

/**
 * Verify JWT token and return user data
 * 
 * NOTE: This is a simplified example. Use proper JWT library in production.
 * Recommended: firebase/php-jwt from Composer
 */
function verify_auth_token($token) {
    $token = trim((string)$token);
    if (strlen($token) < 20) {
        return null;
    }

    $hash = auth_token_hash($token);
    $user_id = null;

    if (function_exists('apcu_fetch')) {
        $cached = apcu_fetch('auth_token:' . $hash);
        if ($cached !== false) {
            $user_id = intval($cached);
        }
    }

    if (!$user_id) {
        ensure_api_auth_tokens_table();
        $token_row = get_db()->queryOne(
            "SELECT user_id
             FROM api_auth_tokens
             WHERE token_hash = ?
               AND revoked_at IS NULL
               AND expires_at > NOW()
             LIMIT 1",
            [$hash]
        );

        if (!$token_row) {
            return null;
        }

        $user_id = intval($token_row['user_id'] ?? 0);
    }

    if ($user_id <= 0) {
        return null;
    }

    $user = get_db()->queryOne(
        "SELECT user_id, tenant_id, username, full_name, role, email, contact_no, is_active
         FROM users
         WHERE user_id = ? AND is_active = 1
         LIMIT 1",
        [$user_id]
    );

    if (!$user) {
        revoke_auth_token($token);
        return null;
    }

    if (function_exists('apcu_store')) {
        apcu_store('auth_token:' . $hash, $user_id, 86400);
    }

    return $user;
}

/**
 * Require authentication
 */
function require_auth() {
    $token = get_auth_token();
    
    if (!$token) {
        api_error('Missing authentication token', 'TOKEN_MISSING', 401);
    }
    
    $user = verify_auth_token($token);
    
    if (!$user) {
        api_error('Invalid or expired token', 'TOKEN_INVALID', 401);
    }
    
    return $user;
}

// ============================================================================
// Input Validation
// ============================================================================

/**
 * Get and validate JSON request body
 */
function get_json_input() {
    $input = file_get_contents('php://input');
    
    try {
        return json_decode($input, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        api_error('Invalid JSON input', 'INVALID_JSON', 400);
    }
}

/**
 * Validate required fields
 */
function validate_required_fields($data, $fields) {
    $missing = [];
    
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        api_error(
            'Missing required fields: ' . implode(', ', $missing),
            'VALIDATION_ERROR',
            422
        );
    }
}

/**
 * Validate email format
 */
function validate_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_error('Invalid email format', 'INVALID_EMAIL', 422);
    }
}

/**
 * Validate phone number (Philippine format)
 */
function validate_phone($phone) {
    if (!preg_match('/^09\d{9}$/', $phone)) {
        api_error('Invalid phone number format', 'INVALID_PHONE', 422);
    }
}

// ============================================================================
// Rate Limiting
// ============================================================================

/**
 * Implement rate limiting (per user)
 * 
 * NOTE: Use Redis for distributed rate limiting in production
 */
function check_rate_limit($user_id, $limit = 100, $window = 60) {
    // Use session or cache to track requests
    // In production, use Redis:
    
    // Check if APCu functions are available
    if (function_exists('apcu_fetch') && function_exists('apcu_store')) {
        // Use APCu if available
        $cache_key = "api_rate_limit:{$user_id}";
        $requests = @apcu_fetch($cache_key) ?? 0;  // @ suppresses warnings
        
        if ($requests >= $limit) {
            http_response_code(429);
            header('Retry-After: ' . $window);
            api_error('Rate limit exceeded', 'RATE_LIMIT_EXCEEDED', 429);
        }
        
        @apcu_store($cache_key, $requests + 1, $window);
        
        // Return remaining requests
        return $limit - ($requests + 1);
    }
    
    // Fallback to session-based rate limiting if APCu not available
    $cache_key = "rate_limit_{$user_id}";
    
    if (!isset($_SESSION[$cache_key])) {
        $_SESSION[$cache_key] = ['count' => 0, 'time' => time()];
    }
    
    $entry = $_SESSION[$cache_key];
    
    // Reset counter if window has passed
    if ((time() - $entry['time']) > $window) {
        $_SESSION[$cache_key] = ['count' => 1, 'time' => time()];
        return $limit - 1;
    }
    
    // Check if limit exceeded
    if ($entry['count'] >= $limit) {
        http_response_code(429);
        header('Retry-After: ' . $window);
        api_error('Rate limit exceeded', 'RATE_LIMIT_EXCEEDED', 429);
    }
    
    $_SESSION[$cache_key]['count']++;
    return $limit - $_SESSION[$cache_key]['count'];
}

// ============================================================================
// Logging
// ============================================================================

/**
 * Log API requests for audit trail
 */
function log_api_request($user_id, $method, $endpoint, $status_code, $response_time) {
    $log_file = __DIR__ . '/../../logs/api_access.log';
    
    $log_entry = sprintf(
        "[%s] %s %s - User:%d Status:%d Time:%.3fms\n",
        date('Y-m-d H:i:s'),
        $method,
        $endpoint,
        $user_id ?? 0,
        $status_code,
        $response_time
    );
    
    error_log($log_entry, 3, $log_file);
}

/**
 * Log errors for debugging
 */
function log_error($message, $context = []) {
    $log_file = __DIR__ . '/../../logs/api_errors.log';
    
    $log_entry = sprintf(
        "[%s] ERROR: %s - Context: %s\n",
        date('Y-m-d H:i:s'),
        $message,
        json_encode($context)
    );
    
    error_log($log_entry, 3, $log_file);
}

// ============================================================================
// Database Connection
// ============================================================================

/**
 * Get database instance
 */
function get_db() {
    require_once __DIR__ . '/../../includes/pdo_db.php';
    return Database::getInstance();
}

// ============================================================================
// Initialize API
// ============================================================================

// Set up CORS headers
setup_cors();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to client

// Ensure UTF-8
mb_internal_encoding('UTF-8');

?>
