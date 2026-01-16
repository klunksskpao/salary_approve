<?php
session_start();
require 'db.php';

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

// 2. ตรวจสอบว่ามี ID ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    die("Error: ไม่พบ ID");
}

$id = $_GET['id'];

// 3. ดึงข้อมูลเก่ามาแสดง
$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
$stmt->execute([$id]);
$req = $stmt->fetch();

if (!$req) {
    die("ไม่พบข้อมูล");
}

// 4. บันทึกการแก้ไข (Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // จัดการค่าว่างให้เป็น 0 (เหมือนตอน save_request)
        $salary = empty($_POST['salary']) ? 0 : $_POST['salary'];
        $position_allowance = empty($_POST['position_allowance']) ? 0 : $_POST['position_allowance'];
        $monthly_comp = empty($_POST['monthly_comp']) ? 0 : $_POST['monthly_comp'];
        $cost_living = empty($_POST['cost_living']) ? 0 : $_POST['cost_living'];
        $total_income = empty($_POST['total_income']) ? 0 : $_POST['total_income'];
        
        $contract_date = empty($_POST['contract_date']) ? NULL : $_POST['contract_date'];
        $contract_end_date = empty($_POST['contract_end_date']) ? NULL : $_POST['contract_end_date'];
        $contract_no = empty($_POST['contract_no']) ? NULL : $_POST['contract_no'];

        $sql = "UPDATE requests SET 
                emp_type=?, title=?, fullname=?, position=?, department=?, 
                start_date=?, salary=?, position_allowance=?, monthly_comp=?, cost_living=?, 
                total_income=?, purpose=?, purpose_other=?, phone=?, email=?, 
                contract_no=?, contract_date=?, contract_end_date=?
                WHERE id=?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['emp_type'], $_POST['title'], $_POST['fullname'], $_POST['position'], $_POST['department'],
            $_POST['start_date'], $salary, $position_allowance, $monthly_comp, $cost_living,
            $total_income, $_POST['purpose'], $_POST['purpose_other'], $_POST['phone'], $_POST['email'],
            $contract_no, $contract_date, $contract_end_date,
            $id
        ]);

        echo "<script>alert('บันทึกข้อมูลเรียบร้อย'); window.location='admin_view.php?id=$id';</script>";
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลคำขอ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> .hidden { display: none; } </style>
</head>
<body class="bg-light py-5">
<div class="container">
    <div class="card p-4 mx-auto" style="max-width: 800px;">
        <h3 class="mb-4 text-center">แก้ไขข้อมูลคำขอ (<?php echo $req['tracking_code']; ?>)</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label>ประเภทบุคลากร</label>
                <select name="emp_type" id="emp_type" class="form-select" onchange="toggleForm()">
                    <option value="A" <?php if($req['emp_type']=='A') echo 'selected'; ?>>ข้าราชการ</option>
                    <option value="B" <?php if($req['emp_type']=='B') echo 'selected'; ?>>พนักงานจ้าง</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>คำนำหน้า</label>
                    <select name="title" class="form-select">
                        <option <?php if($req['title']=='นาย') echo 'selected'; ?>>นาย</option>
                        <option <?php if($req['title']=='นาง') echo 'selected'; ?>>นาง</option>
                        <option <?php if($req['title']=='นางสาว') echo 'selected'; ?>>นางสาว</option>
                    </select>
                </div>
                <div class="col-md-9 mb-3">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname" class="form-control" value="<?php echo $req['fullname']; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>ตำแหน่ง</label>
                    <input type="text" name="position" class="form-control" value="<?php echo $req['position']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>สังกัด</label>
                    <input type="text" name="department" class="form-control" value="<?php echo $req['department']; ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label>วันที่บรรจุ/เริ่มงาน</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $req['start_date']; ?>">
            </div>

            <h5 class="mt-3">ข้อมูลรายได้</h5>
            <div class="row">
                <div class="col-md-4 mb-3"><label>เงินเดือน</label><input type="number" step="0.01" name="salary" class="form-control income-cal" value="<?php echo $req['salary']; ?>"></div>
                <div class="col-md-4 mb-3"><label>ประจำตำแหน่ง</label><input type="number" step="0.01" name="position_allowance" class="form-control income-cal" value="<?php echo $req['position_allowance']; ?>"></div>
                <div class="col-md-4 mb-3"><label>ค่าตอบแทน</label><input type="number" step="0.01" name="monthly_comp" class="form-control income-cal" value="<?php echo $req['monthly_comp']; ?>"></div>
                <div class="col-md-4 mb-3"><label>ค่าครองชีพ</label><input type="number" step="0.01" name="cost_living" class="form-control income-cal" value="<?php echo $req['cost_living']; ?>"></div>
                <div class="col-md-4 mb-3"><label>รวมสุทธิ</label><input type="number" step="0.01" name="total_income" id="total_income" class="form-control" value="<?php echo $req['total_income']; ?>" readonly></div>
            </div>

            <div class="mb-3">
                <label>วัตถุประสงค์</label>
                <select name="purpose" id="purpose" class="form-select" onchange="toggleOtherPurpose()">
                    <?php 
                    $options = ["กู้เงินจากธนาคารออมสิน", "กู้เงินจากธนาคารอิสลามแห่งประเทศไทย", "ทำบัตรเครดิต/เดบิต", "กู้เงินจากธนาคารกรุงไทย", "กู้เงินซื้อรถ", "ค้ำประกันการเช่าซื้อรถยนต์", "ค้ำประกันเงินกู้คนพิการ", "ค้ำประกันการกู้เงิน", "อื่นๆ"];
                    foreach($options as $opt) {
                        $sel = ($req['purpose'] == $opt) ? 'selected' : '';
                        echo "<option value='$opt' $sel>$opt</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3 <?php echo ($req['purpose'] == 'อื่นๆ') ? '' : 'hidden'; ?>" id="div_purpose_other">
                <label>ระบุ (อื่นๆ)</label>
                <input type="text" name="purpose_other" class="form-control" value="<?php echo $req['purpose_other']; ?>">
            </div>

            <div id="type_b_fields" class="<?php echo ($req['emp_type'] == 'B') ? '' : 'hidden'; ?> p-3 mb-3 bg-white border rounded">
                <h5>ข้อมูลสัญญาจ้าง</h5>
                <div class="row">
                    <div class="col-md-4"><label>เลขที่</label><input type="text" name="contract_no" class="form-control" value="<?php echo $req['contract_no']; ?>"></div>
                    <div class="col-md-4"><label>ลงวันที่</label><input type="date" name="contract_date" class="form-control" value="<?php echo $req['contract_date']; ?>"></div>
                    <div class="col-md-4"><label>สิ้นสุด</label><input type="date" name="contract_end_date" class="form-control" value="<?php echo $req['contract_end_date']; ?>"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3"><label>เบอร์โทร</label><input type="text" name="phone" class="form-control" value="<?php echo $req['phone']; ?>"></div>
                <div class="col-md-6 mb-3"><label>อีเมล์</label><input type="email" name="email" class="form-control" value="<?php echo $req['email']; ?>"></div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="admin.php" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto Calculate
    const inputs = document.querySelectorAll('.income-cal');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            let total = 0;
            inputs.forEach(item => total += parseFloat(item.value || 0));
            document.getElementById('total_income').value = total.toFixed(2);
        });
    });

    function toggleForm() {
        const type = document.getElementById('emp_type').value;
        const bFields = document.getElementById('type_b_fields');
        if(type === 'B') bFields.classList.remove('hidden');
        else bFields.classList.add('hidden');
    }

    function toggleOtherPurpose() {
        const val = document.getElementById('purpose').value;
        const otherDiv = document.getElementById('div_purpose_other');
        if(val === 'อื่นๆ') otherDiv.classList.remove('hidden');
        else otherDiv.classList.add('hidden');
    }
</script>
</body>
</html>