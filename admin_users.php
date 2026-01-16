<?php
session_start();
require 'db.php';

// Security Check: ต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied: คุณไม่มีสิทธิ์เข้าถึงหน้านี้");
}

// เพิ่ม/แก้ไข User
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $role = $_POST['role'];
    
    if(!empty($_POST['password'])) {
        // ถ้ากรอกรหัสผ่านใหม่ ให้ Hash
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if(isset($_POST['id']) && $_POST['id'] != '') {
        // Update
        if(!empty($_POST['password'])) {
            $sql = "UPDATE users SET username=?, password=?, fullname=?, role=? WHERE id=?";
            $params = [$username, $password, $fullname, $role, $_POST['id']];
        } else {
            $sql = "UPDATE users SET username=?, fullname=?, role=? WHERE id=?";
            $params = [$username, $fullname, $role, $_POST['id']];
        }
        $pdo->prepare($sql)->execute($params);
    } else {
        // Insert
        $sql = "INSERT INTO users (username, password, fullname, role) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$username, $password, $fullname, $role]);
    }
    header("Location: admin_users.php");
    exit;
}

// ลบ User
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$_GET['delete']]);
    header("Location: admin_users.php");
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
$editUser = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between mb-4">
            <h3>👥 จัดการผู้ใช้งานระบบ</h3>
            <a href="admin.php" class="btn btn-secondary">กลับหน้าหลัก</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card p-3">
                    <h5><?php echo $editUser ? 'แก้ไขผู้ใช้งาน' : 'เพิ่มผู้ใช้งานใหม่'; ?></h5>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $editUser['id'] ?? ''; ?>">
                        <div class="mb-2">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $editUser['username'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-2">
                            <label>Password <?php echo $editUser ? '(เว้นว่างถ้าไม่เปลี่ยน)' : ''; ?></label>
                            <input type="password" name="password" class="form-control" <?php echo $editUser ? '' : 'required'; ?>>
                        </div>
                        <div class="mb-2">
                            <label>ชื่อ-นามสกุล</label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo $editUser['fullname'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>สิทธิ์ (Role)</label>
                            <select name="role" class="form-select">
                                <option value="admin" <?php if(($editUser['role']??'')=='admin') echo 'selected'; ?>>Admin (ดูแลระบบ)</option>
                                <option value="approver" <?php if(($editUser['role']??'')=='approver') echo 'selected'; ?>>Approver (ผู้อนุมัติ)</option>
                                <option value="finance" <?php if(($editUser['role']??'')=='finance') echo 'selected'; ?>>Finance (การเงิน)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">บันทึก</button>
                        <?php if($editUser): ?>
                            <a href="admin_users.php" class="btn btn-outline-secondary w-100 mt-2">ยกเลิก</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card p-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>ชื่อ-สกุล</th>
                                <th>สิทธิ์</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td><?php echo $u['username']; ?></td>
                                <td><?php echo $u['fullname']; ?></td>
                                <td>
                                    <?php 
                                        if($u['role']=='admin') echo '<span class="badge bg-danger">Admin</span>';
                                        elseif($u['role']=='approver') echo '<span class="badge bg-primary">ผู้อนุมัติ</span>';
                                        else echo '<span class="badge bg-success">การเงิน</span>';
                                    ?>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                                    <?php if($u['username'] != 'admin'): // ห้ามลบ admin หลัก ?>
                                        <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันลบ?')">ลบ</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>