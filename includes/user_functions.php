<?php
require_once '../config/config.php';
require_once '../config/auth.php';

/**
 * User Management Functions
 * For Masjid Al-Muhajirin Information System
 */

/**
 * Get all users with optional filtering
 */
function getAllUsers($filters = []) {
    global $pdo;
    
    $sql = "SELECT id, username, full_name, role, status, last_login, created_at FROM users WHERE 1=1";
    $params = [];
    
    if (!empty($filters['role'])) {
        $sql .= " AND role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (username LIKE ? OR full_name LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get user by ID
 */
function getUserById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, full_name, role, status, last_login, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get user by username
 */
function getUserByUsername($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, full_name, role, status, last_login, created_at FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Create new user
 */
function createUser($data) {
    global $pdo;
    
    // Validate required fields
    $required_fields = ['username', 'password', 'full_name', 'role'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return [
                'success' => false,
                'message' => "Field $field is required"
            ];
        }
    }
    
    // Validate username uniqueness
    if (getUserByUsername($data['username'])) {
        return [
            'success' => false,
            'message' => 'Username sudah digunakan'
        ];
    }
    
    // Validate role
    $valid_roles = ['admin_masjid', 'admin_bimbel', 'viewer'];
    if (!in_array($data['role'], $valid_roles)) {
        return [
            'success' => false,
            'message' => 'Role tidak valid'
        ];
    }
    
    // Validate password strength
    if (strlen($data['password']) < 6) {
        return [
            'success' => false,
            'message' => 'Password minimal 6 karakter'
        ];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, status) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $data['username'],
            hashPassword($data['password']),
            $data['full_name'],
            $data['role'],
            $data['status'] ?? 'active'
        ]);
        
        if ($result) {
            $user_id = $pdo->lastInsertId();
            logSecurityEvent('USER_CREATED', "New user created: {$data['username']} (ID: $user_id)");
            
            return [
                'success' => true,
                'message' => 'User berhasil dibuat',
                'user_id' => $user_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal membuat user'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ];
    }
}

/**
 * Update user data
 */
function updateUser($id, $data) {
    global $pdo;
    
    // Get existing user
    $existing_user = getUserById($id);
    if (!$existing_user) {
        return [
            'success' => false,
            'message' => 'User tidak ditemukan'
        ];
    }
    
    // Check username uniqueness if username is being changed
    if (!empty($data['username']) && $data['username'] !== $existing_user['username']) {
        if (getUserByUsername($data['username'])) {
            return [
                'success' => false,
                'message' => 'Username sudah digunakan'
            ];
        }
    }
    
    // Validate role if provided
    if (!empty($data['role'])) {
        $valid_roles = ['admin_masjid', 'admin_bimbel', 'viewer'];
        if (!in_array($data['role'], $valid_roles)) {
            return [
                'success' => false,
                'message' => 'Role tidak valid'
            ];
        }
    }
    
    // Build update query
    $update_fields = [];
    $params = [];
    
    if (!empty($data['username'])) {
        $update_fields[] = "username = ?";
        $params[] = $data['username'];
    }
    
    if (!empty($data['full_name'])) {
        $update_fields[] = "full_name = ?";
        $params[] = $data['full_name'];
    }
    
    if (!empty($data['role'])) {
        $update_fields[] = "role = ?";
        $params[] = $data['role'];
    }
    
    if (!empty($data['status'])) {
        $update_fields[] = "status = ?";
        $params[] = $data['status'];
    }
    
    if (empty($update_fields)) {
        return [
            'success' => false,
            'message' => 'Tidak ada data yang diupdate'
        ];
    }
    
    $update_fields[] = "updated_at = NOW()";
    $params[] = $id;
    
    try {
        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            logSecurityEvent('USER_UPDATED', "User updated: {$existing_user['username']} (ID: $id)");
            
            return [
                'success' => true,
                'message' => 'User berhasil diupdate'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate user'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ];
    }
}

/**
 * Change user password
 */
function changePassword($id, $old_password, $new_password) {
    global $pdo;
    
    // Get user with password
    try {
        $stmt = $pdo->prepare("SELECT username, password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan'
            ];
        }
        
        // Verify old password
        if (!verifyPassword($old_password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Password lama tidak benar'
            ];
        }
        
        // Validate new password
        if (strlen($new_password) < 6) {
            return [
                'success' => false,
                'message' => 'Password baru minimal 6 karakter'
            ];
        }
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([hashPassword($new_password), $id]);
        
        if ($result) {
            logSecurityEvent('PASSWORD_CHANGED', "Password changed for user: {$user['username']} (ID: $id)");
            
            return [
                'success' => true,
                'message' => 'Password berhasil diubah'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengubah password'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ];
    }
}

/**
 * Reset user password (admin function)
 */
function resetPassword($id, $new_password) {
    global $pdo;
    
    // Get user
    $user = getUserById($id);
    if (!$user) {
        return [
            'success' => false,
            'message' => 'User tidak ditemukan'
        ];
    }
    
    // Validate new password
    if (strlen($new_password) < 6) {
        return [
            'success' => false,
            'message' => 'Password minimal 6 karakter'
        ];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([hashPassword($new_password), $id]);
        
        if ($result) {
            logSecurityEvent('PASSWORD_RESET', "Password reset for user: {$user['username']} (ID: $id)");
            
            return [
                'success' => true,
                'message' => 'Password berhasil direset'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mereset password'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ];
    }
}

/**
 * Delete user (soft delete by setting status to inactive)
 */
function deleteUser($id) {
    global $pdo;
    
    // Get user
    $user = getUserById($id);
    if (!$user) {
        return [
            'success' => false,
            'message' => 'User tidak ditemukan'
        ];
    }
    
    // Prevent deleting the last admin
    if ($user['role'] === 'admin_masjid') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin_masjid' AND status = 'active'");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] <= 1) {
            return [
                'success' => false,
                'message' => 'Tidak dapat menghapus admin terakhir'
            ];
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            logSecurityEvent('USER_DELETED', "User deactivated: {$user['username']} (ID: $id)");
            
            return [
                'success' => true,
                'message' => 'User berhasil dihapus'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menghapus user'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ];
    }
}

/**
 * Get user statistics
 */
function getUserStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total users by role
        $stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users WHERE status = 'active' GROUP BY role");
        $stmt->execute();
        $role_stats = $stmt->fetchAll();
        
        foreach ($role_stats as $stat) {
            $stats['by_role'][$stat['role']] = $stat['count'];
        }
        
        // Total active users
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_active'] = $result['count'];
        
        // Total inactive users
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_inactive'] = $result['count'];
        
        // Recent logins (last 7 days)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['recent_logins'] = $result['count'];
        
        return $stats;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Validate user authentication
 */
function validateUserAuth($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, full_name, role, status FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan'
            ];
        }
        
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Akun tidak aktif'
            ];
        }
        
        if (!verifyPassword($password, $user['password'])) {
            logSecurityEvent('LOGIN_FAILED', "Failed login attempt for username: $username");
            return [
                'success' => false,
                'message' => 'Password tidak benar'
            ];
        }
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ];
    }
}
?>