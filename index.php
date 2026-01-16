<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบขอหนังสือรับรองเงินเดือน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="icon" sizes="512x512" href="https://www.pao-sisaket.go.th/getimage/system_images/title.ico">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        .hidden { display: none; }
        canvas { border: 1px solid #ccc; background: #fff; width: 500px; height: auto; }
        .signature-pad-container {
            /* กำหนดความกว้างสูงสุดเพื่อให้ Canvas ย่อ/ขยายได้ */
            max-width: 500px; 
            /* กำหนดความกว้างเริ่มต้น (อาจปรับเปลี่ยนได้ตามความเหมาะสม) */
            width: 100%; 
            /* หากต้องการให้ Canvas มีอัตราส่วนที่คงที่ ให้เพิ่ม height ด้วย */
            height: 250px;
            /* เพิ่มเส้นขอบเพื่อให้เห็นขอบเขตของพื้นที่เซ็นชื่อได้ชัดเจน */
            border: 1px solid #ccc;
            /* ทำให้พื้นหลังเป็นสีขาว (ตามความจำเป็น) */
            background-color: #fff;
        }

        #signature-pad {
            /* ทำให้ Canvas ขยายเต็มความกว้างและความสูงของ container */
            width: 500px;
            height: 250px;
            /* คลาส img-fluid ของ Bootstrap 5 จะช่วยให้รูปภาพ/canvas ตอบสนองต่อการปรับขนาด */
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
<div class="header">
        <img src="./logo.png" width="60" style="display:block; margin: 0 auto 10px auto;">
</div>
    <h2 class="text-center mb-4">แบบฟอร์มขอหนังสือรับรองเงินเดือน สำนักคลัง อบจ.ศรีสะเกษ</h2>
    
    <form action="save_request.php" method="POST" id="reqForm">
        <div class="card p-4">
            <div class="mb-3">
                <label class="form-label">ประเภทบุคลากร</label>
                <select name="emp_type" id="emp_type" class="form-select" required onchange="toggleForm()">
                    <option value="A">ข้าราชการ</option>
                    <option value="B">พนักงานจ้าง</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-2 mb-3">
                    <label>คำนำหน้า</label>
                    <select name="title" class="form-select">
                        <option>นาย</option>
                        <option>นาง</option>
                        <option>นางสาว</option>
                    </select>
                </div>
                <div class="col-md-10 mb-3">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>ตำแหน่ง</label>
                    <input type="text" name="position" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>สังกัด (หน่วยที่)</label>
                    <select id="sheet-options" name="department" class="form-select">
                        <option value="">กำลังโหลดข้อมูล...</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>บรรจุเมื่อ</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>

            <h5 class="mt-3">ข้อมูลรายได้</h5>
            <div class="row">
                <div class="col-md-4 mb-3"><label>อัตราเงินเดือน</label><input type="number" step="0.01" name="salary" id="salary" class="form-control income-cal" required></div>
                <div class="col-md-4 mb-3"><label>เงินประจำตำแหน่ง</label><input type="number" step="0.01" name="position_allowance" id="position_allowance" class="form-control income-cal"></div>
                <div class="col-md-4 mb-3"><label>ค่าตอบแทนรายเดือน</label><input type="number" step="0.01" name="monthly_comp" id="monthly_comp" class="form-control income-cal"></div>
                <div class="col-md-4 mb-3"><label>ค่าครองชีพ</label><input type="number" step="0.01" name="cost_living" id="cost_living" class="form-control income-cal"></div>
                <div class="col-md-4 mb-3"><label>รวมรายรับทั้งสิ้น</label><input type="number" step="0.01" name="total_income" id="total_income" class="form-control" readonly></div>
            </div>

            <div class="mb-3">
                <label>ขอใบรับรองเพื่อ</label>
                <select name="purpose" id="purpose" class="form-select" onchange="toggleOtherPurpose()">
                    <option value="กู้เงินจากธนาคารออมสิน">กู้เงินจากธนาคารออมสิน</option>
                    <option value="กู้เงินจากธนาคารอิสลามแห่งประเทศไทย">กู้เงินจากธนาคารอิสลามแห่งประเทศไทย</option>
                    <option value="ทำบัตรเครดิต/เดบิต">ทำบัตรเครดิต/เดบิต</option>
                    <option value="กู้เงินจากธนาคารกรุงไทย">กู้เงินจากธนาคารกรุงไทย</option>
                    <option value="กู้เงินซื้อรถ">กู้เงินซื้อรถ</option>
                    <option value="ค้ำประกันการเช่าซื้อรถยนต์">ค้ำประกันการเช่าซื้อรถยนต์</option>
                    <option value="ค้ำประกันเงินกู้คนพิการ">ค้ำประกันเงินกู้คนพิการ</option>
                    <option value="ค้ำประกันการกู้เงิน">ค้ำประกันการกู้เงิน</option>
                    <option value="อื่นๆ">อื่นๆ</option>
                </select>
            </div>
            <div class="mb-3 hidden" id="div_purpose_other">
                <label>โปรดระบุ เช่น (ทำบัตรของอีออน)(สาขาของกรุงไทย)(ค้ำประกันกู้เงินอะไร)(อื่นๆ เช่น กู้เงินสหกรณ์ออมทรัพย์อะไร)</label>
                <input type="text" name="purpose_other" class="form-control">
            </div>

            <div id="type_b_fields" class="hidden p-3 mb-3 bg-white border rounded">
                <h5>ข้อมูลสัญญาจ้าง (สำหรับพนักงานจ้าง)</h5>
                <div class="row">
                    <div class="col-md-4 mb-2"><label>สัญญาจ้างเลขที่</label><input type="text" name="contract_no" class="form-control"></div>
                    <div class="col-md-4 mb-2"><label>ลงวันที่</label><input type="date" name="contract_date" class="form-control"></div>
                    <div class="col-md-4 mb-2"><label>สิ้นสุดวันที่</label><input type="date" name="contract_end_date" class="form-control"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3"><label>เบอร์โทรศัพท์</label><input type="text" name="phone" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>อีเมล์</label><input type="email" name="email" class="form-control"></div>
            </div>

            <div class="mb-3 text-center">
                <label class="form-label d-block">เซ็นชื่อ (ลายเซ็น)</label>
                <div class="signature-pad-container d-inline-block">
                    <canvas id="signature-pad" class="img-fluid"></canvas>
                </div>
                <br>
                <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="signaturePad.clear()">ล้างลายเซ็น</button>
                <input type="hidden" name="signature_data" id="signature_data">
            </div>

            <button type="submit" class="btn btn-primary w-100">ส่งคำขอ</button>
        </div>
    </form>
    
    <div class="text-center mt-3">
        <a href="track.php">ติดตามสถานะคำขอ</a> | <a href="admin.php">เจ้าหน้าที่ Login</a>
    </div>
</div>

<script>
    // Signature Pad Setup
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas);

    // Auto Calculate Total Income
    const inputs = document.querySelectorAll('.income-cal');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            let total = 0;
            inputs.forEach(item => total += parseFloat(item.value || 0));
            document.getElementById('total_income').value = total.toFixed(2);
        });
    });

    // Toggle Type B Fields
    function toggleForm() {
        const type = document.getElementById('emp_type').value;
        const bFields = document.getElementById('type_b_fields');
        if(type === 'B') {
            bFields.classList.remove('hidden');
        } else {
            bFields.classList.add('hidden');
        }
    }

    // Toggle Other Purpose
    function toggleOtherPurpose() {
        const val = document.getElementById('purpose').value;
        const otherDiv = document.getElementById('div_purpose_other');
        if(val === 'อื่นๆ') {
            otherDiv.classList.remove('hidden');
        } else if(val === 'ทำบัตรเครดิต/เดบิต') {
            otherDiv.classList.remove('hidden');
            //document.getElementById('div_purpose_other').label = "ทำบัตรฯของ?";
        } else if(val === 'กู้เงินจากธนาคารกรุงไทย') {
            otherDiv.classList.remove('hidden');
        } else if(val === 'ค้ำประกันการกู้เงิน') {
            otherDiv.classList.remove('hidden');
        } else {
            otherDiv.classList.add('hidden');
        }
    }

    // On Submit
    document.getElementById('reqForm').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            alert("กรุณาเซ็นชื่อ");
            e.preventDefault();
        } else {
            document.getElementById('signature_data').value = signaturePad.toDataURL();
        }
    });
</script>

<script>
        // *** สำคัญ: เปลี่ยน YOUR_WEB_APP_URL_HERE เป็น URL ที่คุณได้จากการ Deploy ในขั้นตอนที่ 2 *** เพิ่มหน่วยงานในสังกัดจาก google 
        const WEB_APP_URL = 'https://script.googleusercontent.com/macros/echo?user_content_key=AehSKLi6MNE0rnrhRbDiAjlUBI0nQF4AYD7cqWlmwNrmd0YyVTMgc7cj3bKyTXXg9dRxtd5WQaFt4SAUh6NdpDYJUOVrMkyGBeQxpuaJf0bBrKudLW4759alr7sURGLKpkQIqV-E8-ep7WX6JKPrbiHTljHOrG68tG0O8PRM4He0nMdR85Q4ob-lYS3WmlZcRvxowXfnygykECCDM7gACZF5ZcMlBx62y-QHKAq2aUXUUYyTkleBt1_55jVyvbzv2T5pIC0rl5lyhmXjmipsiqxrJuv6JPpWHQ&lib=MxAy61XuoMhvZz6-dtelljFG8jR3uC7Sx'; 

        async function fetchOptions() {
            const selectElement = document.getElementById('sheet-options');
            
            try {
                // 1. เรียก API โดยใช้ Fetch API (สมัยใหม่)
                const response = await fetch(WEB_APP_URL);

                if (!response.ok) {
                    throw new Error(`HTTP Error! สถานะ: ${response.status}`);
                }

                // 2. แปลงผลลัพธ์เป็น JSON
                const data = await response.json();
                
                // 3. ตรวจสอบและดำเนินการ
                if (data.options && data.options.length > 0) {
                    // ล้างตัวเลือกเดิมและเพิ่มตัวเลือกใหม่
                    selectElement.innerHTML = '<option value="">--- กรุณาเลือก ---</option>';

                    data.options.forEach(optionText => {
                        const option = document.createElement('option');
                        option.value = optionText; 
                        option.textContent = optionText;
                        selectElement.appendChild(option);
                    });
                } else if (data.error) {
                    // แสดงข้อผิดพลาดจาก Apps Script
                    selectElement.innerHTML = `<option value="">เกิดข้อผิดพลาด: ${data.error}</option>`;
                    console.error("API Error:", data.error);
                } else {
                    // ไม่พบข้อมูล
                    selectElement.innerHTML = '<option value="">ไม่พบข้อมูลใน Sheet</option>';
                }

            } catch (error) {
                // จัดการข้อผิดพลาดในการเชื่อมต่อ (เช่น Network Error)
                console.error("การโหลดข้อมูลล้มเหลว:", error);
                selectElement.innerHTML = `<option value="">❌ โหลดข้อมูลล้มเหลว</option>`;
            }
        }

        // เรียกใช้งานฟังก์ชันเมื่อหน้าเว็บโหลดเสร็จ
        document.addEventListener('DOMContentLoaded', fetchOptions);

    </script>
    <script>
    
    function resizeCanvas() {
        var canvas = document.querySelector('canvas');
        
        // Get the device pixel ratio, to make the canvas look crisp on mobile devices
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        
        // Match the canvas's internal drawing buffer size to its CSS display size
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        
        // Scale the context to ensure everything is drawn correctly
        canvas.getContext("2d").scale(ratio, ratio);
        
        // If you are using szimek/signature_pad library, call .clear() or .fromDataURL()
        // to handle the canvas being cleared by the browser when resized.
        
        // Example for szimek/signature_pad:
        // signaturePad.clear(); 
        // OR if you want to preserve the signature:
        // var data = signaturePad.toDataURL(); // Save before resize
        // signaturePad.fromDataURL(data);    // Redraw after resize
    }

    // Call resize function on page load
    window.addEventListener('load', resizeCanvas); 

    // Call resize function on window resize event for                  
    window.addEventListener('resize', resizeCanvas);

    </script>
    <footer>
        <center>
	    <h3>Version 1.2</h3>
        <p>Author: สำนักคลัง อบจ.ศรีสะเกษ tel: 045814683<br>
        <a href="mailto:klunk.adm.sskpao@gmail.com">klunk.adm.sskpao@gmail.com</a></p>
        </center>
    </footer>
</body>
</html>