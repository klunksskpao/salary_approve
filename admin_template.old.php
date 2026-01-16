<?php
session_start();
require 'db.php';

if(!isset($_SESSION['admin'])) { header("Location: admin.php"); exit; }

// ค่าเริ่มต้น (Default Template) กรณีเพิ่งเริ่มระบบหรือกด Reset
$default_template = '
<div style="text-align: center; font-weight: bold; font-size: 24px; margin-bottom: 30px;">
    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Emblem_of_Thailand.svg/150px-Emblem_of_Thailand.svg.png" width="60" style="display:block; margin: 0 auto 10px auto;">
    หนังสือรับรองเงินเดือน
</div>
<div style="line-height: 1.8; font-size: 16px;">
    <p style="text-align: center;">ที่ .....................................</p>
    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;หนังสือฉบับนี้ให้ไว้เพื่อรับรองว่า <b>{name}</b> ตำแหน่ง <b>{position}</b> สังกัด <b>{department}</b></p>
    <p>{contract_info}</p>
    <p>ได้รับเงินเดือนและรายได้ดังนี้:</p>
    
    {salary_table}

    <p class="mt-4">หนังสือรับรองฉบับนี้ออกให้เพื่อใช้สำหรับ <b>{purpose}</b> เท่านั้น</p>
    <p style="text-align:right; margin-top: 30px;">ให้ไว้ ณ วันที่ {date_now}</p>
    
    <div style="margin-top: 50px; text-align: center; float: right; width: 40%;">
        <br><br><br>
        ( {approver_name} )<br>
        {approver_position}
    </div>
</div>';

// 1. บันทึกข้อมูล
if(isset($_POST['save_template'])) {
    $pdo->prepare("UPDATE admin_settings SET cert_template=? WHERE id=1")->execute([$_POST['content']]);
    echo "<script>alert('บันทึกรูปแบบเรียบร้อย');</script>";
}

// 2. คืนค่าเริ่มต้น
if(isset($_POST['reset_template'])) {
    $pdo->prepare("UPDATE admin_settings SET cert_template=? WHERE id=1")->execute([$default_template]);
    echo "<script>alert('คืนค่าเริ่มต้นเรียบร้อย');</script>";
}

// ดึงข้อมูลปัจจุบัน
$setting = $pdo->query("SELECT cert_template FROM admin_settings WHERE id=1")->fetch();
$current_template = empty($setting['cert_template']) ? $default_template : $setting['cert_template'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขรูปแบบใบรับรอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h3>🎨 แก้ไขรูปแบบหนังสือรับรองเงินเดือน</h3>
            <a href="admin.php" class="btn btn-secondary">กลับหน้าหลัก</a>
        </div>

        <div class="alert alert-info">
            <strong>วิธีใช้งาน:</strong> ท่านสามารถพิมพ์ข้อความ จัดหน้า หรือใส่รูปภาพได้ตามต้องการ <br>
            ห้ามลบคำในปีกกา <code>{...}</code> เพราะระบบจะนำข้อมูลจริงมาแทนที่คำเหล่านั้น
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card p-3">
                    <h6>ตัวแปรที่ใช้ได้ (ก๊อปปี้ไปวางในเนื้อหา):</h6>
                    <div>
                        <code>{name}</code> ชื่อ-สกุล, 
                        <code>{position}</code> ตำแหน่ง, 
                        <code>{department}</code> สังกัด, 
                        <code>{contract_info}</code> ข้อมูลสัญญา (ถ้ามี), 
                        <code>{salary_table}</code> ตารางเงินเดือน (สร้างอัตโนมัติ), 
                        <code>{purpose}</code> วัตถุประสงค์, 
                        <code>{date_now}</code> วันที่ปัจจุบัน, 
                        <code>{approver_name}</code> ชื่อผู้เซ็น, 
                        <code>{approver_position}</code> ตำแหน่งผู้เซ็น
                    </div>
                </div>
            </div>
        </div>

        <form method="post">
            <textarea id="summernote" name="content"><?php echo $current_template; ?></textarea>
            
            <div class="mt-3 d-flex justify-content-between">
                <button type="submit" name="reset_template" class="btn btn-danger" onclick="return confirm('ข้อมูลที่แก้ไขจะหายไป ยืนยันที่จะคืนค่าเริ่มต้น?')">คืนค่ารูปแบบมาตรฐาน</button>
                <button type="submit" name="save_template" class="btn btn-success btn-lg">บันทึกรูปแบบ</button>
            </div>
        </form>
    </div>

    <script>
        $('#summernote').summernote({
            placeholder: 'ออกแบบหนังสือรับรองที่นี่...',
            tabsize: 2,
            height: 600,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    </script>
</body>
</html>