<?php
/**
 * Authentication API Endpoints
 * 
 * File: /api/v1/auth.php
 * 
 * Endpoints:
 * - POST /api/v1/auth.php?action=register - Customer registration
 * - POST /api/v1/auth.php?action=login - Customer login
 * - POST /api/v1/auth.php?action=logout - Logout
 * - POST /api/v1/auth.php?action=refresh-token - Refresh expired token
 * - POST /api/v1/auth.php?action=forgot-password - Request password reset
 * - POST /api/v1/auth.php?action=reset-password - Reset password with token
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../../includes/AuthService.php';
require_once __DIR__ . '/../../includes/CustomerService.php';

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

if ($method === 'POST') {
    
    // ================================================================
    // REGISTER Endpoint
    // ================================================================
    
    if ($action === 'register') {
        $data = get_json_input();
        validate_required_fields($data, ['tenant_id', 'username', 'password', 'first_name', 'last_name', 'email', 'contact_no']);
        
        validate_email($data['email']);
        validate_phone($data['contact_no']);
        
        try {
            // Check password strength
            if (!AuthService::isPasswordStrong($data['password'])) {
                api_error(
                    'Password must be at least 8 characters with uppercase, lowercase, digit, and special character',
                    'PASSWORD_WEAK',
                    422
                );
            }
            
            $db = get_db();
            $authService = new AuthService();
            $tenant_id = intval($data['tenant_id'] ?? 0);
            if ($tenant_id <= 0) {
                api_error('Tenant is required', 'TENANT_REQUIRED', 422);
            }

            $tenant = $db->queryOne(
                "SELECT tenant_id FROM tenants WHERE tenant_id = ? AND is_active = 1 LIMIT 1",
                [$tenant_id]
            );
            if (!$tenant) {
                api_error('Tenant not found or inactive', 'TENANT_INVALID', 404);
            }

            $customerService = new CustomerService($tenant_id);
            
            // Check if username exists globally
            $existing = $db->queryOne(
                "SELECT user_id FROM users WHERE LOWER(username) = LOWER(?)",
                [$data['username']]
            );
            
            if ($existing) {
                api_error('Username already exists', 'DUPLICATE_USERNAME', 409);
            }
            
            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Create user account
                $password_hash = AuthService::hashPassword($data['password']);
                
                $sql = "INSERT INTO users (tenant_id, username, password_hash, full_name, email, contact_no, role, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, 'CUSTOMER', 1)";
                
                $db->exec($sql, [
                    $tenant_id,
                    $data['username'],
                    $password_hash,
                    $data['first_name'] . ' ' . $data['last_name'],
                    $data['email'],
                    $data['contact_no']
                ]);
                
                $user_id = $db->lastInsertId();
                
                // Generate customer number
                $customer_no = 'CUST-' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
                
                // Create customer profile
                $customer_id = $customerService->createCustomer([
                    'user_id' => $user_id,
                    'customer_no' => $customer_no,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'contact_no' => $data['contact_no'],
                    'email' => $data['email']
                ]);
                
                $db->commit();
                
                // Return success with user data
                api_response(true, [
                    'user_id' => $user_id,
                    'customer_id' => $customer_id,
                    'username' => $data['username'],
                    'full_name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'customer_no' => $customer_no
                ], 'Registration successful', 201);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            log_error('Registration error', ['error' => $e->getMessage()]);
            api_error('Registration failed', 'REGISTRATION_ERROR', 500);
        }
    }
    
    // ================================================================
    // LOGIN Endpoint
    // ================================================================
    
    elseif ($action === 'login') {
        $data = get_json_input();
        validate_required_fields($data, ['tenant_id', 'username', 'password']);
        
        try {
            $authService = new AuthService();
            $tenant_id = intval($data['tenant_id'] ?? 0);
            if ($tenant_id <= 0) {
                api_error('Tenant is required', 'TENANT_REQUIRED', 422);
            }
            
            // Authenticate customer
            $user = $authService->authenticateCustomer($data['username'], $data['password'], $tenant_id);
            
            if (!$user) {
                log_error('Failed login attempt', ['username' => $data['username'], 'tenant_id' => $tenant_id]);
                api_error('Invalid credentials', 'INVALID_CREDENTIALS', 401);
            }
            
            $token = bin2hex(random_bytes(32));
            store_auth_token($token, $user['user_id'], 86400);
            
            api_response(true, [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 86400,
                'user' => [
                    'user_id' => $user['user_id'],
                    'tenant_id' => $user['tenant_id'],
                    'customer_id' => $user['customer_id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email']
                ]
            ], 'Login successful', 200);
            
        } catch (Exception $e) {
            log_error('Login error', ['error' => $e->getMessage()]);
            api_error('Login failed', 'LOGIN_ERROR', 500);
        }
    }
    
    // ================================================================
    // LOGOUT Endpoint
    // ================================================================
    
    elseif ($action === 'logout') {
        $user = require_auth(); // Validate user
        
        try {
            $token = get_auth_token();
            revoke_auth_token($token);
            
            api_response(true, null, 'Logged out successfully', 200);
            
        } catch (Exception $e) {
            log_error('Logout error', ['error' => $e->getMessage()]);
            api_error('Logout failed', 'LOGOUT_ERROR', 500);
        }
    }
    
    // ================================================================
    // FORGOT PASSWORD Endpoint
    // ================================================================
    
    elseif ($action === 'forgot-password') {
        $data = get_json_input();
        validate_required_fields($data, ['tenant_id', 'email']);
        validate_email($data['email']);
        
        try {
            $db = get_db();
            $tenant_id = intval($data['tenant_id'] ?? 0);
            if ($tenant_id <= 0) {
                api_error('Tenant is required', 'TENANT_REQUIRED', 422);
            }
            
            // Find user by email
            $user = $db->queryOne(
                "SELECT user_id, email, username FROM users WHERE email = ? AND tenant_id = ? AND role = 'CUSTOMER'",
                [$data['email'], $tenant_id]
            );
            
            if (!$user) {
                // Don't reveal if email exists or not (security)
                api_response(true, null, 'If email exists, reset link will be sent', 200);
            }
            
            $authService = new AuthService();
            $token = $authService->generateResetToken($user['user_id']);
            
            // TODO: Send email with reset link
            // Example: mail($user['email'], 'Password Reset Request', "Reset link: ...");
            
            log_error('Password reset requested', ['user_id' => $user['user_id']]);
            
            api_response(true, [
                'reset_token' => $token // In production, send via email only, not in API response
            ], 'Password reset link sent to email', 200);
            
        } catch (Exception $e) {
            log_error('Forgot password error', ['error' => $e->getMessage()]);
            api_error('Failed to process password reset', 'RESET_ERROR', 500);
        }
    }
    
    // ================================================================
    // RESET PASSWORD Endpoint
    // ================================================================
    
    elseif ($action === 'reset-password') {
        $data = get_json_input();
        validate_required_fields($data, ['reset_token', 'new_password']);
        
        try {
            if (!AuthService::isPasswordStrong($data['new_password'])) {
                api_error(
                    'Password must meet strength requirements',
                    'PASSWORD_WEAK',
                    422
                );
            }
            
            $authService = new AuthService();
            $success = $authService->verifyAndResetPassword($data['reset_token'], $data['new_password']);
            
            if (!$success) {
                api_error('Invalid or expired reset token', 'INVALID_TOKEN', 400);
            }
            
            api_response(true, null, 'Password reset successfully', 200);
            
        } catch (Exception $e) {
            log_error('Reset password error', ['error' => $e->getMessage()]);
            api_error('Failed to reset password', 'RESET_ERROR', 500);
        }
    }
    
    // ================================================================
    // REFRESH TOKEN Endpoint
    // ================================================================
    
    elseif ($action === 'refresh-token') {
        $data = get_json_input();
        validate_required_fields($data, ['refresh_token']);
        
        try {
            // TODO: Implement refresh token logic
            // Validate old token and issue new one
            
            $new_token = bin2hex(random_bytes(32));
            
            api_response(true, [
                'token' => $new_token,
                'token_type' => 'Bearer',
                'expires_in' => 86400
            ], 'Token refreshed', 200);
            
        } catch (Exception $e) {
            log_error('Token refresh error', ['error' => $e->getMessage()]);
            api_error('Failed to refresh token', 'REFRESH_ERROR', 500);
        }
    }
    
    else {
        api_error('Invalid action', 'INVALID_ACTION', 400);
    }
    
} else {
    api_error('Method not allowed', 'METHOD_NOT_ALLOWED', 405);
}

?>
