<?php
require 'db.php';

// รองรับการเข้าถึงได้ 2 แบบ: ผ่าน ID (Admin) หรือ ผ่าน Tracking Code (User)
$req = null;

if (isset($_GET['id'])) {
    // กรณี Admin กดดูผ่าน ID
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $req = $stmt->fetch();
} elseif (isset($_GET['code'])) {
    // กรณี User ดูผ่านรหัสติดตาม
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE tracking_code = ?");
    $stmt->execute([$_GET['code']]);
    $req = $stmt->fetch();
}

if (!$req) {
    die("ไม่พบข้อมูลคำขอ");
}

// ฟังก์ชันแปลงวันที่เป็นไทยแบบย่อ
function thaiDate($date) {
    if(!$date) return "-";
    $timestamp = strtotime($date);
    $months = [null, "ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
    $d = date("j", $timestamp);
    $m = $months[date("n", $timestamp)];
    $y = date("Y", $timestamp) + 543;
    return "$d $m $y";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบคำขอหนังสือรับรองเงินเดือน - <?php echo $req['tracking_code']; ?></title>
    <style>
        body { font-family: "Sarabun", sans-serif; margin: 0; padding: 20px; color: #000; }
        .container { width: 210mm; margin: 0 auto; padding: 20px; border: 1px solid #ccc; background: white; }
        h2, h3, h4 { text-align: center; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; vertical-align: top; }
        th { background-color: #f0f0f0; text-align: left; width: 35%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .section-header { background-color: #e0e0e0; font-weight: bold; text-align: center; padding: 5px; border: 1px solid #000; margin-top: 20px; }
        .signature-box { margin-top: 30px; text-align: right; }
        .signature-img { max-height: 60px; display: block; margin-left: auto; margin-right: 0; }
        
        @media print {
            body { background: none; padding: 0; }
            .container { border: none; width: 100%; margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">🖨️ พิมพ์หน้านี้</button>
    </div>

    <div class="container">
    <div class="header">
        <img src="./logo.png" width="30" style="display:block; margin: 0 auto 10px auto;">
        </div>
        <h3>แบบคำขอหนังสือรับรองเงินเดือน</h3>
        <div class="text-right"><strong>วันที่ขอ:</strong> <?php echo thaiDate($req['req_date']); ?></div>
        <div class="text-right"><strong>รหัสอ้างอิง:</strong> <?php echo $req['tracking_code']; ?></div>

        <div class="section-header">1. ข้อมูลผู้ยื่นคำขอ</div>
        <table>
            <tr>
                <th>สถานะบุคลากร</th>
                <td><?php echo ($req['emp_type']=='A') ? '☑ ข้าราชการ' : '☑ พนักงานจ้าง'; ?></td>
            </tr>
            <tr>
                <th>ชื่อ-นามสกุล</th>
                <td><?php echo $req['title'] . $req['fullname']; ?></td>
            </tr>
            <tr>
                <th>ตำแหน่ง</th>
                <td><?php echo $req['position']; ?></td>
            </tr>
            <tr>
                <th>สังกัด</th>
                <td><?php echo $req['department']; ?></td>
            </tr>
            <tr>
                <th>วันที่บรรจุ/เริ่มงาน</th>
                <td><?php echo thaiDate($req['start_date']); ?></td>
            </tr>
        </table>

        <div class="section-header">2. รายละเอียดรายได้ (บาท)</div>
        <table>
            <tr>
                <th>อัตราเงินเดือน</th>
                <td class="text-right"><?php echo number_format($req['salary'], 2); ?></td>
            </tr>
            <tr>
                <th>เงินประจำตำแหน่ง</th>
                <td class="text-right"><?php echo number_format($req['position_allowance'], 2); ?></td>
            </tr>
            <tr>
                <th>ค่าตอบแทนรายเดือน</th>
                <td class="text-right"><?php echo number_format($req['monthly_comp'], 2); ?></td>
            </tr>
            <tr>
                <th>ค่าครองชีพ</th>
                <td class="text-right"><?php echo number_format($req['cost_living'], 2); ?></td>
            </tr>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <th>รวมรายรับทั้งสิ้น</th>
                <td class="text-right"><?php echo number_format($req['total_income'], 2); ?></td>
            </tr>
        </table>

        <?php if($req['emp_type'] == 'B'): ?>
        <div class="section-header">3. ข้อมูลสัญญาจ้าง</div>
        <table>
            <tr>
                <th>สัญญาจ้างเลขที่</th>
                <td><?php echo $req['contract_no']; ?></td>
            </tr>
            <tr>
                <th>วันที่ลงนามสัญญา</th>
                <td><?php echo thaiDate($req['contract_date']); ?></td>
            </tr>
            <tr>
                <th>วันที่สิ้นสุดสัญญา</th>
                <td><?php echo thaiDate($req['contract_end_date']); ?></td>
            </tr>
        </table>
        <?php endif; ?>

        <div class="section-header">4. วัตถุประสงค์และการติดต่อ</div>
        <table>
            <tr>
                <th>ขอหนังสือรับรองเพื่อ</th>
                <td>
                    <?php echo $req['purpose']; ?>
                    <?php if($req['purpose'] == 'อื่นๆ') echo " (" . $req['purpose_other'] . ")"; ?>
                </td>
            </tr>
            <tr>
                <th>เบอร์โทรศัพท์</th>
                <td><?php echo $req['phone']; ?></td>
            </tr>
            <tr>
                <th>อีเมล์</th>
                <td><?php echo $req['email'] ? $req['email'] : '-'; ?></td>
            </tr>
        </table>

        <div class="signature-box">
            <p>ลงชื่อผู้ยื่นคำขอ</p>
            <?php if($req['signature_img']): ?>
                <img src="<?php echo $req['signature_img']; ?>" class="signature-img">
            <?php else: ?>
                <br><br>
            <?php endif; ?>
            <p>( <?php echo $req['title'] . $req['fullname']; ?> )</p>
            <p>วันที่ ........../........../..........</p>
        </div>
    </div>

</body>
</html>