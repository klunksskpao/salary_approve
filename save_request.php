<?php
// แสดง Error เพื่อการตรวจสอบ (ถ้าผ่านแล้วเอาออกได้)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {
        // --- ส่วนแก้ไขปัญหา Incorrect decimal value ---
        // ตรวจสอบค่าตัวเลข ถ้าเป็นค่าว่าง '' ให้เปลี่ยนเป็น 0 ทันที
        $salary = empty($_POST['salary']) ? 0 : $_POST['salary'];
        $position_allowance = empty($_POST['position_allowance']) ? 0 : $_POST['position_allowance'];
        $monthly_comp = empty($_POST['monthly_comp']) ? 0 : $_POST['monthly_comp'];
        $cost_living = empty($_POST['cost_living']) ? 0 : $_POST['cost_living'];
        $total_income = empty($_POST['total_income']) ? 0 : $_POST['total_income'];
        
        // --- จัดการเรื่องวันที่ (ป้องกัน Error เรื่องวันที่ว่างเปล่า) ---
        // ถ้าเป็นค่าว่าง ให้เป็น NULL เพื่อลง Database ได้
        $contract_date = empty($_POST['contract_date']) ? NULL : $_POST['contract_date'];
        $contract_end_date = empty($_POST['contract_end_date']) ? NULL : $_POST['contract_end_date'];
        $contract_no = empty($_POST['contract_no']) ? NULL : $_POST['contract_no'];

        // --- ข้อมูลอื่นๆ ---
        $emp_type = $_POST['emp_type'];
        $title = $_POST['title'];
        $fullname = $_POST['fullname'];
        $position = $_POST['position'];
        $department = $_POST['department'];
        $start_date = $_POST['start_date'];
        $purpose = $_POST['purpose'];
        $purpose_other = $_POST['purpose_other'] ?? '';
        $phone = $_POST['phone'];
        $email = $_POST['email'] ?? '';
        $signature_data = $_POST['signature_data'];

        // สร้าง Random Code 8 หลัก
        $tracking_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
        
        // SQL Command
        $sql = "INSERT INTO requests (
            tracking_code, emp_type, title, fullname, position, department, 
            start_date, salary, position_allowance, monthly_comp, cost_living, 
            total_income, purpose, purpose_other, phone, email, 
            contract_no, contract_date, contract_end_date, signature_img
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        
        $stmt = $pdo->prepare($sql);
        
        // Execute (ใช้ตัวแปรที่ผ่านการตรวจสอบด้านบนแล้ว)
        $stmt->execute([
            $tracking_code, 
            $emp_type, 
            $title, 
            $fullname, 
            $position, 
            $department, 
            $start_date, 
            $salary,            // ใช้ตัวแปรที่แก้เป็น 0 แล้ว
            $position_allowance,// ใช้ตัวแปรที่แก้เป็น 0 แล้ว
            $monthly_comp,      // ใช้ตัวแปรที่แก้เป็น 0 แล้ว
            $cost_living,       // ใช้ตัวแปรที่แก้เป็น 0 แล้ว
            $total_income,      // ใช้ตัวแปรที่แก้เป็น 0 แล้ว
            $purpose, 
            $purpose_other, 
            $phone, 
            $email, 
            $contract_no, 
            $contract_date,     // ใช้ตัวแปรที่แก้เป็น NULL แล้ว
            $contract_end_date, // ใช้ตัวแปรที่แก้เป็น NULL แล้ว
            $signature_data
        ]);

        // สำเร็จ -> ไปหน้าติดตามผล
        header("Location: track.php?code=" . $tracking_code);
        exit;

    } catch (PDOException $e) {
        // แสดง Error ชัดเจน
        echo "<h3>เกิดข้อผิดพลาดในการบันทึกข้อมูล (Database Error):</h3>";
        echo "<pre>" . $e->getMessage() . "</pre>";
    }

} else {
    echo "Access Denied";
}
?>