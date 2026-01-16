<?php
session_start();
require 'db.php';

// ตรวจสอบสิทธิ์ Admin
if(!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

// ตรวจสอบว่ามี ID ส่งมาไหม
if(!isset($_GET['id'])) {
    echo "ไม่พบรหัสคำขอ";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
$stmt->execute([$id]);
$req = $stmt->fetch();

if(!$req) {
    echo "ไม่พบข้อมูล";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำขอ - <?php echo $req['tracking_code']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .label-text { font-weight: bold; color: #555; }
        .data-box { background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px; border: 1px solid #eee; }
    </style>
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>รายละเอียดคำขอ (ID: <?php echo $req['tracking_code']; ?>)</h3>
            <a href="admin.php" class="btn btn-secondary">ย้อนกลับ</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                สถานะปัจจุบัน: <strong><?php echo strtoupper($req['status']); ?></strong>
            </div>
            <div class="card-body">
                
                <h5 class="text-primary border-bottom pb-2">1. ข้อมูลส่วนตัว</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="label-text">ประเภทบุคลากร</div>
                        <div class="data-box"><?php echo ($req['emp_type'] == 'A') ? 'ข้าราชการ' : 'พนักงานจ้าง'; ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="label-text">ชื่อ-นามสกุล</div>
                        <div class="data-box"><?php echo $req['title'] . $req['fullname']; ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="label-text">ตำแหน่ง</div>
                        <div class="data-box"><?php echo $req['position']; ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="label-text">สังกัด</div>
                        <div class="data-box"><?php echo $req['department']; ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="label-text">วันที่บรรจุ</div>
                        <div class="data-box"><?php echo $req['start_date']; ?></div>
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-3">2. ข้อมูลการติดต่อ & วัตถุประสงค์</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="label-text">เบอร์โทรศัพท์</div>
                        <div class="data-box"><?php echo $req['phone']; ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="label-text">อีเมล์</div>
                        <div class="data-box"><?php echo $req['email'] ? $req['email'] : '-'; ?></div>
                    </div>
                    <div class="col-md-12">
                        <div class="label-text">ขอหนังสือรับรองเพื่อ</div>
                        <div class="data-box text-danger">
                            <?php echo $req['purpose']; ?> 
                            <?php echo ($req['purpose'] == 'อื่นๆ') ? '('.$req['purpose_other'].')' : ''; ?>
                        </div>
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2 mt-3">3. รายละเอียดเงินเดือน</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>รายการ</th>
                                <th class="text-end">จำนวนเงิน (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>อัตราเงินเดือน</td>
                                <td class="text-end"><?php echo number_format($req['salary'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>เงินประจำตำแหน่ง</td>
                                <td class="text-end"><?php echo number_format($req['position_allowance'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>ค่าตอบแทนรายเดือน</td>
                                <td class="text-end"><?php echo number_format($req['monthly_comp'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>ค่าครองชีพ</td>
                                <td class="text-end"><?php echo number_format($req['cost_living'], 2); ?></td>
                            </tr>
                            <tr class="table-success fw-bold">
                                <td>รวมรายรับทั้งสิ้น</td>
                                <td class="text-end"><?php echo number_format($req['total_income'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if($req['emp_type'] == 'B'): ?>
                <h5 class="text-primary border-bottom pb-2 mt-3">4. ข้อมูลสัญญาจ้าง</h5>
                <div class="row bg-light p-2 rounded mx-1 border">
                    <div class="col-md-4">
                        <span class="fw-bold">เลขที่สัญญา:</span> <?php echo $req['contract_no']; ?>
                    </div>
                    <div class="col-md-4">
                        <span class="fw-bold">วันที่เริ่ม:</span> <?php echo $req['contract_date']; ?>
                    </div>
                    <div class="col-md-4">
                        <span class="fw-bold">วันที่สิ้นสุด:</span> <?php echo $req['contract_end_date']; ?>
                    </div>
                </div>
                <?php endif; ?>

                <h5 class="text-primary border-bottom pb-2 mt-3">5. ลายเซ็นผู้ขอ</h5>
                <div class="text-center border p-3">
                    <?php if(!empty($req['signature_img'])): ?>
                        <img src="<?php echo $req['signature_img']; ?>" style="max-height: 100px;">
                    <?php else: ?>
                        <p class="text-muted">ไม่มีลายเซ็น</p>
                    <?php endif; ?>
                </div>

                <div class="mt-4 text-center">
                    <?php if($req['status'] == 'pending'): ?>
                        <a href="admin.php?approve=<?php echo $req['id']; ?>" class="btn btn-success btn-lg px-4" onclick="return confirm('ยืนยันยอมรับคำขอนี้?')">
                            ✅ อนุมัติคำขอ (Approve)
                        </a>
                    <?php endif; ?>

                    <a href="print_cert.php?id=<?php echo $req['id']; ?>" target="_blank" class="btn btn-primary btn-lg px-4">
                        🖨️ พิมพ์ใบรับรอง
                    </a>

                    <a href="admin.php?delete=<?php echo $req['id']; ?>" class="btn btn-danger btn-lg px-4" onclick="return confirm('ต้องการลบข้อมูลนี้หรือไม่?')">
                        🗑️ ลบข้อมูล
                    </a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>