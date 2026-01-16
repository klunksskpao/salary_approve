<?php
session_start();
require 'db.php';
if(!isset($_SESSION['admin']) || !isset($_GET['id'])) die("Access Denied");

$id = $_GET['id'];
// Update Status
$pdo->prepare("UPDATE requests SET status='printed' WHERE id=?")->execute([$id]);

// Fetch Info
$req = $pdo->query("SELECT * FROM requests WHERE id=$id")->fetch();
$settings = $pdo->query("SELECT * FROM admin_settings WHERE id=1")->fetch();

if(!$req) die("Not Found");

// --- เตรียมข้อมูลสำหรับแทนที่ ---

// 1. สร้างตารางเงินเดือน (HTML Table)
$salary_table = '
<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <tr><td>1. อัตราเงินเดือน</td> <td style="text-align:right">'.number_format($req['salary'],2).' บาท</td></tr>
    <tr><td>2. เงินประจำตำแหน่ง</td> <td style="text-align:right">'.number_format($req['position_allowance'],2).' บาท</td></tr>
    <tr><td>3. ค่าตอบแทนรายเดือน</td> <td style="text-align:right">'.number_format($req['monthly_comp'],2).' บาท</td></tr>
    <tr><td>4. ค่าครองชีพ</td> <td style="text-align:right">'.number_format($req['cost_living'],2).' บาท</td></tr>
    <tr style="font-weight:bold; border-top: 1px solid black; border-bottom: 1px double black;">
        <td>รวมรายรับทั้งสิ้น</td> <td style="text-align:right">'.number_format($req['total_income'],2).' บาท</td>
    </tr>
</table>';

// 2. ข้อมูลสัญญา
$contract_info = "";
if($req['emp_type'] == 'B') {
    $contract_info = "โดยมีสัญญาจ้างเลขที่ {$req['contract_no']} ลงวันที่ {$req['contract_date']} ถึงวันที่ {$req['contract_end_date']}";
}

// 3. วัตถุประสงค์
$purpose_txt = ($req['purpose'] == 'อื่นๆ') ? $req['purpose_other'] : $req['purpose'];

// 4. วันที่ไทย
function thaiDate($date) {
    $months = [null, "มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    return date("j", strtotime($date)) . " " . $months[date("n", strtotime($date))] . " " . (date("Y", strtotime($date)) + 543);
}
$date_now_th = thaiDate(date("Y-m-d"));

// --- ดึง Template และแทนที่คำ ---
$template = empty($settings['cert_template']) ? 'กรุณาตั้งค่ารูปแบบในหน้า Admin' : $settings['cert_template'];

// คู่การแทนที่ [ คำค้นหา => ข้อมูลจริง ]
$replacements = [
    '{name}' => $req['title'] . $req['fullname'],
    '{position}' => $req['position'],
    '{department}' => $req['department'],
    '{contract_info}' => $contract_info,
    '{salary_table}' => $salary_table,
    '{purpose}' => $purpose_txt,
    '{date_now}' => $date_now_th,
    '{approver_name}' => $settings['approver_name'],
    '{approver_position}' => $settings['approver_position']
];

// ทำการแทนที่
foreach ($replacements as $key => $val) {
    $template = str_replace($key, $val, $template);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>หนังสือรับรองเงินเดือน</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: "Sarabun", sans-serif; 
            padding: 40px; 
            width: 210mm; /* ขนาด A4 */
            margin: auto;
        }
        @media print { 
            .no-print { display: none; } 
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom: 20px;">🖨️ พิมพ์หน้านี้</button>
    
    <?php echo $template; ?>

</body>
</html>