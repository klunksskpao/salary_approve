<?php 
require 'db.php'; 
$req = null;
if(isset($_GET['code'])) {
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE tracking_code = ?");
    $stmt->execute([$_GET['code']]);
    $req = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ติดตามสถานะ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            .print-area { display: block; }
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h3 class="text-center no-print">ติดตามสถานะคำขอ</h3>
    <form class="mb-4 no-print" method="GET">
        <div class="input-group">
            <input type="text" name="code" class="form-control" placeholder="กรอกรหัสคำขอ 8 หลัก" value="<?php echo $_GET['code'] ?? ''; ?>">
            <button class="btn btn-primary">ค้นหา</button>
        </div>
    </form>

    <?php if($req): ?>
        <div class="card p-4">
            <div class="alert alert-info no-print">
                สถานะ: <strong><?php echo strtoupper($req['status']); ?></strong> (รหัส: <?php echo $req['tracking_code']; ?>)
            </div>
            
            <div class="print-area">
                <h4 class="text-center">บันทึกข้อความ</h4>
                <p><strong>เรื่อง</strong> ขอหนังสือรับรองเงินเดือน</p>
                <p><strong>เรียน</strong> นายกเทศมนตรี/ผู้บริหารท้องถิ่น</p>
                <p class="mt-4">
                    ข้าพเจ้า <?php echo $req['title'] . $req['fullname']; ?> ตำแหน่ง <?php echo $req['position']; ?> สังกัด <?php echo $req['department']; ?>
                    มีความประสงค์ขอหนังสือรับรองเงินเดือน เพื่อนำไปใช้สำหรับ 
                    <u><?php echo ($req['purpose'] == 'อื่นๆ') ? $req['purpose_other'] : $req['purpose']; ?></u>
                </p>
                <p>จึงเรียนมาเพื่อโปรดพิจารณาอนุเคราะห์</p>
                <div style="margin-top: 50px; text-align: right;">
                    <img src="<?php echo $req['signature_img']; ?>" width="150"><br>
                    (<?php echo $req['title'] . $req['fullname']; ?>)<br>
                    ผู้ยื่นคำขอ
                </div>
            </div>
            
            <div class="mt-3 no-print text-center">
                <button onclick="window.print()" class="btn btn-success">พิมพ์ใบคำขอ</button>
                <?php if($req['status'] == 'printed'): ?>
                    <a href="#" class="btn btn-warning disabled">ดาวน์โหลดใบรับรอง (ติดต่อเจ้าหน้าที่)</a>
                <?php endif; ?>
            </div>
            <div class="mt-3 no-print text-center">
                
                <a href="print_req_form.php?code=<?php echo $req['tracking_code']; ?>" target="_blank" class="btn btn-success btn-lg">
                    🖨️ พิมพ์แบบฟอร์มคำขอ (ฉบับเต็ม)
                </a>

            </div>            
        </div>
    <?php elseif(isset($_GET['code'])): ?>
        <div class="alert alert-danger">ไม่พบข้อมูล</div>
    <?php endif; ?>
</div>
</body>
</html>