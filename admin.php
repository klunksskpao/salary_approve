<?php
session_start();
require 'db.php';

// 1. ตรวจสอบว่า Login หรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role']; // ดึงสิทธิ์มาเก็บตัวแปรให้ใช้ง่ายๆ

// ... (ส่วน Code Action: Delete/Approve เหมือนเดิม แต่ต้องเพิ่มการดักสิทธิ์) ...
if(isset($_GET['delete'])) {
    if($role != 'admin') die("คุณไม่มีสิทธิ์ลบข้อมูล"); // Security Check
    $pdo->prepare("DELETE FROM requests WHERE id=?")->execute([$_GET['delete']]);
    header("Location: admin.php"); exit;
}
if(isset($_GET['approve'])) {
    if($role != 'admin' && $role != 'approver') die("คุณไม่มีสิทธิ์อนุมัติ"); // Security Check
    $pdo->prepare("UPDATE requests SET status='approved' WHERE id=?")->execute([$_GET['approve']]);
    header("Location: admin.php"); exit;
}

// --- 3. ส่วน Search & Pagination Logic (หัวใจสำคัญ) ---

// ตั้งค่าจำนวนรายการต่อหน้า
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// รับค่าคำค้นหา
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%$search%";

// สร้าง SQL พื้นฐาน
$sql_base = "FROM requests WHERE (fullname LIKE ? OR tracking_code LIKE ? OR department LIKE ?)";

// 3.1 หาจำนวนรายการทั้งหมด (เพื่อคำนวณหน้า)
$stmt_count = $pdo->prepare("SELECT COUNT(*) $sql_base");
$stmt_count->execute([$search_param, $search_param, $search_param]);
$total_rows = $stmt_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// 3.2 ดึงข้อมูลจริง (ใส่ LIMIT)
$stmt = $pdo->prepare("SELECT * $sql_base ORDER BY id DESC LIMIT $start, $limit");
$stmt->execute([$search_param, $search_param, $search_param]);
$requests = $stmt->fetchAll();

// ดึงข้อมูลผู้รับรอง
$signer = $pdo->query("SELECT * FROM admin_settings WHERE id=1")->fetch();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pagination { justify-content: center; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body class="p-4 bg-light">
    
    <div class="d-flex justify-content-between mb-4 align-items-center">
        <div>
            <h2>ระบบจัดการคำขอหนังสือรับรองเงินเดือน</h2>
            <span class="badge bg-secondary">ผู้ใช้งาน: <?php echo $_SESSION['fullname']; ?> (<?php echo strtoupper($role); ?>)</span>
        </div>
        <div>
            <?php if($role == 'admin'): ?>
                <a href="admin_users.php" class="btn btn-dark btn-sm">👥 จัดการผู้ใช้งาน</a>
                <a href="admin_template.php" class="btn btn-warning btn-sm">🎨 แก้ไขรูปแบบ</a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <table class="table table-bordered table-striped">
        <thead>
           <th>จัดการ</th>
        </thead>
        <tbody>
            <?php foreach($requests as $r): ?>
            <tr>
                <td>
                    <a href="admin_view.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-info text-white">🔍</a>

                    <?php if($role == 'admin' || $role == 'finance'): ?>
                        <a href="admin_edit.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                    <?php endif; ?>
                    
                    <?php if(($role == 'admin' || $role == 'approver') && $r['status'] == 'pending'): ?>
                        <a href="?approve=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary">✓</a>
                    <?php endif; ?>
                    
                    <a href="print_req_form.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-secondary">🖨️</a>
                    
                    <?php if($role == 'admin'): ?>
                        <a href="?delete=<?php echo $r['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ลบ?')">🗑️</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
                
    <div class="accordion mb-4" id="accordionSettings">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                    ⚙️ ตั้งค่าชื่อผู้รับรองในใบสำคัญ (คลิกเพื่อแก้ไข)
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionSettings">
                <div class="accordion-body">
                    <form method="POST" class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="approver_name" class="form-control" value="<?php echo $signer['approver_name']; ?>" placeholder="ชื่อผู้รับรอง">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="approver_position" class="form-control" value="<?php echo $signer['approver_position']; ?>" placeholder="ตำแหน่ง">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="update_signer" class="btn btn-primary w-100">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="col-form-label fw-bold">🔍 ค้นหา:</label>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="ชื่อ, รหัสคำขอ, หรือ สังกัด" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">ค้นหา</button>
                    <?php if($search != ''): ?>
                        <a href="admin.php" class="btn btn-secondary">ล้างค่า</a>
                    <?php endif; ?>
                </div>
                <div class="col text-end">
                    <span class="text-muted">พบทั้งหมด <?php echo $total_rows; ?> รายการ</span>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อ-สกุล</th>
                        <th>สังกัด</th>
                        <th>ประเภท</th>
                        <th>สถานะ</th>
                        <th style="width: 250px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($requests) > 0): ?>
                        <?php foreach($requests as $r): ?>
                        <tr>
                            <td><?php echo $r['tracking_code']; ?></td>
                            <td><?php echo $r['title'] . $r['fullname']; ?></td>
                            <td><?php echo $r['department']; ?></td>
                            <td><?php echo ($r['emp_type']=='A')?'ข้าราชการ':'พนักงานจ้าง'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($r['status']=='pending'?'warning':($r['status']=='printed'?'success':'info')); ?>">
                                    <?php echo $r['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin_view.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-info text-white" title="ดูรายละเอียด">🔍</a>
                                <a href="admin_edit.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-warning" title="แก้ไข">✏️</a>
                                
                                <?php if($r['status'] == 'pending'): ?>
                                    <a href="?approve=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary" title="อนุมัติ">✓</a>
                                <?php endif; ?>
                                
                                <a href="print_req_form.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-secondary" title="พิมพ์ใบคำขอ">🖨️</a>
                                <a href="print_cert.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-success" title="พิมพ์ใบรับรอง">🎓</a>
                                
                                <a href="?delete=<?php echo $r['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ลบรายการนี้?')" title="ลบ">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูลคำขอ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination">
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">ก่อนหน้า</a>
            </li>

            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">ถัดไป</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>