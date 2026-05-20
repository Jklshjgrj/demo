<?php
/**
 * Admin User Management
 * FixIt - Manage all registered citizens and admins
 */

require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!has_role('admin') && !has_role('superadmin')) {
    header('Location: ../index.php');
    exit;
}

$success = '';
$error = '';

// Handle role update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $uid  = (int)($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? '';

    $allowed_roles = ['citizen', 'admin', 'superadmin'];
    if ($uid && in_array($role, $allowed_roles)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $uid]);
            $success = 'User role updated successfully.';
        } catch (PDOException $e) {
            $error = 'Update failed: ' . $e->getMessage();
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $uid = (int)($_POST['user_id'] ?? 0);
    $me  = $_SESSION['user_id'];
    if ($uid && $uid !== $me) {
        try {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
            $success = 'User deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Delete failed: ' . $e->getMessage();
        }
    } else {
        $error = 'You cannot delete your own account.';
    }
}

// Filters
$search    = trim($_GET['search'] ?? '');
$role_f    = $_GET['role'] ?? '';

$query  = "SELECT u.*, 
    (SELECT COUNT(*) FROM reports WHERE user_id = u.id) as report_count,
    (SELECT COUNT(*) FROM report_comments WHERE user_id = u.id) as comment_count,
    (SELECT COALESCE(SUM(r2.upvotes),0) FROM reports r2 WHERE r2.user_id = u.id) as total_upvotes_received
    FROM users u WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($role_f) {
    $query .= " AND role = ?";
    $params[] = $role_f;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'User Management';
require_once '../includes/header.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h4 class="fw-bold mb-1">User Management</h4>
        <p class="text-muted small mb-0">Manage all registered citizens and administrators</p>
    </div>
    <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
        <?php echo count($users); ?> Users
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success py-2 px-4 mb-4 border-0 bg-success bg-opacity-10 text-success rounded-3 small fw-semibold"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger py-2 px-4 mb-4 border-0 bg-danger bg-opacity-10 text-danger rounded-3 small fw-semibold"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="fixit-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-5">
            <div class="position-relative">
                <i data-lucide="search" class="position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px;"></i>
                <input type="text" name="search" class="fixit-input ps-5" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="fixit-input">
                <option value="">All Roles</option>
                <option value="citizen"    <?php echo $role_f === 'citizen'    ? 'selected' : ''; ?>>Citizen</option>
                <option value="admin"      <?php echo $role_f === 'admin'      ? 'selected' : ''; ?>>Admin</option>
                <option value="superadmin" <?php echo $role_f === 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn-fixit btn-fixit-primary w-100 py-2">Search</button>
        </div>
        <div class="col-md-2">
            <a href="manage_users.php" class="btn-fixit btn-fixit-outline w-100 py-2">Clear</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="fixit-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-fixit align-middle mb-0">
            <thead class="text-dim small text-uppercase fw-bold letter-spacing-05">
                <tr>
                    <th class="border-0 px-4 py-4">User</th>
                    <th class="border-0 py-4">Email</th>
                    <th class="border-0 py-4">Role</th>
                    <th class="border-0 py-4">Reports</th>
                    <th class="border-0 py-4">Comments</th>
                    <th class="border-0 py-4">Upvotes Rcvd</th>
                    <th class="border-0 py-4">Joined</th>
                    <th class="border-0 py-4 text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <?php
                    $role_color = match($u['role']) {
                        'superadmin' => '#f59e0b',
                        'admin'      => '#00d4ff',
                        default      => '#10b981'
                    };
                    $initial = strtoupper(substr($u['full_name'], 0, 1));
                    $is_me   = $u['id'] == $_SESSION['user_id'];
                ?>
                <tr class="table-row-hover">
                    <!-- User Identity -->
                    <td class="border-0 px-4 py-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle fw-bold text-white d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                 style="width: 40px; height: 40px; background: linear-gradient(135deg, <?php echo $role_color; ?>88, <?php echo $role_color; ?>44); border: 2px solid <?php echo $role_color; ?>44; font-size: 0.9rem;">
                                <?php echo $initial; ?>
                            </div>
                            <div>
                                <div class="fw-bold text-visible">
                                    <?php echo htmlspecialchars($u['full_name']); ?>
                                    <?php if ($is_me): ?>
                                        <span class="badge ms-1 bg-primary bg-opacity-20 text-primary rounded-pill" style="font-size: 0.6rem;">You</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-dim small">#<?php echo $u['id']; ?></div>
                            </div>
                        </div>
                    </td>

                    <!-- Email -->
                    <td class="border-0 py-4">
                        <span class="small text-muted"><?php echo htmlspecialchars($u['email']); ?></span>
                    </td>

                    <!-- Role Badge -->
                    <td class="border-0 py-4">
                        <span class="badge rounded-pill px-3 py-2 fw-bold" style="background-color: <?php echo $role_color; ?>22; color: <?php echo $role_color; ?>; border: 1px solid <?php echo $role_color; ?>44; font-size: 0.68rem;">
                            <?php echo ucfirst($u['role']); ?>
                        </span>
                    </td>

                    <!-- Reports Count -->
                    <td class="border-0 py-4">
                        <div class="d-flex align-items-center gap-1">
                            <i data-lucide="file-text" class="text-primary" style="width: 13px; height: 13px;"></i>
                            <span class="fw-bold text-visible"><?php echo $u['report_count']; ?></span>
                        </div>
                    </td>

                    <!-- Comments Count -->
                    <td class="border-0 py-4">
                        <div class="d-flex align-items-center gap-1">
                            <i data-lucide="message-circle" class="text-secondary" style="width: 13px; height: 13px;"></i>
                            <span class="fw-bold text-visible"><?php echo $u['comment_count']; ?></span>
                        </div>
                    </td>

                    <!-- Upvotes Received -->
                    <td class="border-0 py-4">
                        <div class="d-flex align-items-center gap-1">
                            <i data-lucide="thumbs-up" class="text-warning" style="width: 13px; height: 13px;"></i>
                            <span class="fw-bold text-visible"><?php echo $u['total_upvotes_received']; ?></span>
                        </div>
                    </td>

                    <!-- Joined Date -->
                    <td class="border-0 py-4 text-muted small">
                        <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                    </td>

                    <!-- Actions -->
                    <td class="border-0 py-4 text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <!-- Edit Role Button -->
                            <button class="btn btn-sm btn-fixit btn-fixit-outline px-3"
                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode(['id' => $u['id'], 'name' => $u['full_name'], 'role' => $u['role']])); ?>)">
                                <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                            </button>
                            <!-- Delete Button -->
                            <?php if (!$is_me): ?>
                            <form method="POST" onsubmit="return confirm('Delete this user? All their reports will also be removed.');" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-sm px-3"
                                        style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); border-radius: 999px;">
                                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i data-lucide="users" class="mb-2" style="width: 40px; height: 40px; opacity: 0.3;"></i>
                        <div>No users found.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content fixit-card p-0 border-0">
            <div class="p-4 border-bottom border-white border-opacity-10">
                <h5 class="fw-bold mb-0">Change User Role</h5>
                <p class="text-muted small mb-0 mt-1" id="modal-user-name"></p>
            </div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="user_id" id="edit-user-id">
                    <div class="mb-4">
                        <label class="small text-muted fw-bold mb-2 d-block">Assign Role</label>
                        <select name="role" id="edit-role" class="fixit-input">
                            <option value="citizen">Citizen</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn-fixit btn-fixit-outline flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_user" class="btn-fixit btn-fixit-primary flex-grow-1">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const editModal = new bootstrap.Modal(document.getElementById('editModal'));

function openEditModal(user) {
    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-role').value = user.role;
    document.getElementById('modal-user-name').textContent = 'Editing: ' + user.name;
    editModal.show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
