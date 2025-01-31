<?php 
session_start();
// ตรวจสอบว่ามีการ login หรือไม่
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    // ถ้าไม่มี session จะ redirect ไปยังหน้า signin.html
    header('Location: ../SignupForm/signin.html');
    exit();
}

include 'Database\db_connect.php';
use Database\Database;

$db = new Database();
$pdo = $db->connect();

// รับค่า ID จาก URL parameter
$id = isset($_GET['id']) ? $_GET['id'] : null;

// ตัวแปรสำหรับเก็บข้อมูล
$formData = array();

// ดึงข้อมูลจากฐานข้อมูล
if ($id) {
    try {
        $query = "SELECT *, billing_type::text[], purpose::text[] FROM transfer_form WHERE id = $1";
        $result = pg_query_params($pdo, $query, array($id));

        if ($result) {
            $formData = pg_fetch_assoc($result);
            
            // แปลงข้อมูล arrays จาก PostgreSQL
            $billingTypes = str_replace(['{','}','"'], '', $formData['billing_type']);
            $billingTypes = explode(',', $billingTypes);
            
            $purposes = str_replace(['{','}','"'], '', $formData['purpose']);
            $purposes = explode(',', $purposes);
            
            // เพิ่มข้อมูลกลับเข้าไปใน formData
            $formData['billing_types_array'] = $billingTypes;
            $formData['purposes_array'] = $purposes;
        }
    } catch (Exception $e) {
        error_log("Error fetching form data: " . $e->getMessage());
    }
}

// แปลงข้อมูล billing_type เป็น array
$billingTypes = isset($formData['billing_type']) ? explode(',', $formData['billing_type']) : [];

// แปลงข้อมูลให้เป็น array ที่ใช้งานได้
$billingTypes = str_replace(['{', '}', '"'], '', $formData['billing_type']); // ลบ {, }, และ " ออก
$billingTypes = explode(',', $billingTypes); // แปลงเป็น array

?>


<!DOCTYPE html>
<html lang="th">

<head>
    <title>ใบส่งตัวผู้ป่วย</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="date=no">
    <meta name="format-detection" content="address=no">
    <title>โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล - ใบส่งตัวผู้ป่วย</title>
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    
</head>
<body>
    <div class="form-container">
        <div class="header">
            <div class="logo">BPK</div>
            <h3>โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล<br>BANGPAKOK 9 INTERNATIONAL HOSPITAL</h3>
        </div>

        <div class="checkbox-group">
    <label>ประเภทการเรียกเก็บ:</label>
    <div class="checkbox-options">
        <label>
            <input type="checkbox" 
                   id="bill-company" 
                   name="billing_type[]" 
                   value="company"
                   <?php echo (in_array('company', $billingTypes) || in_array('"company"', $billingTypes)) ? 'checked' : ''; ?>
                   disabled>
            เรียกเก็บบริษัท
        </label>
        <label>
            <input type="checkbox" 
                   id="bill-employee" 
                   name="billing_type[]" 
                   value="employee"
                   <?php echo (in_array('employee', $billingTypes) || in_array('"employee"', $billingTypes)) ? 'checked' : ''; ?>
                   disabled>
            เรียกเก็บพนักงาน
        </label>
        <label>
            <input type="checkbox" 
                   id="bill-fund" 
                   name="billing_type[]" 
                   value="fund"
                   <?php echo (in_array('fund', $billingTypes) || in_array('"fund"', $billingTypes)) ? 'checked' : ''; ?>
                   disabled>
            เรียกเก็บกองทุนเงินทดแทน
        </label>



        <label>
            <input type="checkbox" 
                   id="bill-fund" 
                   name="billing_type[]" 
                   value="social_security"
                   <?php echo (in_array('social_security', $billingTypes) || in_array('"social_security"', $billingTypes)) ? 'checked' : ''; ?>
                   disabled>
                   เรียกเก็บประกันสังคม(SW)
        </label>
           
          
                <label class="insurance-container" for="insurance-company">
                    <input type="checkbox" id="bill-insurance" name="billing_type[]"value="insurance"
                    <?php echo (in_array('insurance', $billingTypes) || in_array('"insurance"', $billingTypes)) ? 'checked' : '';  ?> disabled>
                        ชื่อบริษัทประกัน:
                </label>
                <input type="text" id="insurance-company" name="billing_type[]" 
                    value="<?php echo htmlspecialchars($formData['insurance_company'] ?? ''); ?>" disabled>
            
            </div>
        </div>


        <div>
            <h2 style="font-size: 18px; text-align: center;">ใบส่งตัวผู้ป่วย</h2>
        </div>
        <div class="form-group date-right">
            <label for="date">วันที่</label>
            <input type="text" value="<?php echo htmlspecialchars($formData['transfer_date'] ?? ''); ?>">
       
        </div>

        <div>
            <label>เรียน ผู้อำนวยการโรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล</label>
        </div>
        <div class="form-group">
            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ข้าพเจ้า (บริษัท/โรงงาน) </label>
            <input type="text" value="<?php echo htmlspecialchars($formData['company'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>ที่อยู่ </label>
            <input type="text" value="<?php echo htmlspecialchars($formData['address'] ?? ''); ?>">
            <label>โทรศัพท์ </label>
            <input type="text" class="phone-input" value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
        </div>

        <div class="form-group">
            
        </div>
        <div class="form-group">
            <label>ขอส่งตัวพนักงานชื่อ</label>
            
            <select id="title">
                <option value="นาย">นาย</option>
                <option value="นาง">นาง</option>
                <option value="นางสาว">นางสาว</option>
            </select>
            <input type="text" value="<?php echo htmlspecialchars($formData['full_name_tf'] ?? ''); ?>">
            <label>อายุ </label>
            <input type="text" value="<?php echo htmlspecialchars($formData['age'] ?? ''); ?>">
        </div>

        <div class="form-group">
       
        </div>

        <div class="checkbox-group">
            <label>เพื่อ:</label>
            <div class="checkbox-options">
                <label>
                    <input type="checkbox" id="purpose-checkup" name="purpose[]" value="checkup"
                    <?php echo (in_array('checkup', $formData['purposes_array'])) ? 'checked' : ''; ?> 
                    disabled>
                    ตรวจรักษา
                </label>
                <label>
                    <input type="checkbox" id="purpose-annual" name="purpose[]" value="annual"
                    <?php echo (in_array('annual', $formData['purposes_array'])) ? 'checked' : ''; ?> 
                    disabled>
                    ตรวจร่างกายประจำปี
                </label>
                <label>
                    <input type="checkbox" id="purpose-new" name="purpose[]" value="new"
                    <?php echo (in_array('new', $formData['purposes_array'])) ? 'checked' : ''; ?> 
                    disabled>
                    ตรวจร่างกายพนักงานใหม่
                </label>
                <label>
                    <input type="checkbox" id="purpose-continuous" name="purpose[]"
                        value="continuous"
                        <?php echo (in_array('continuous', $formData['purposes_array'])) ? 'checked' : ''; ?> 
                        disabled>
                    รักษาต่อเนื่องจนหายที่ โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล
                </label>
                <label>
                    <input type="checkbox" id="purpose-other" name="purpose[]"
                        value="other" <?php echo (in_array('continuous', $formData['purposes_array'])) ? 'checked' : ''; ?> 
                        disabled>
                        อื่นๆโปรดระบุ
                    <input type="text" id="diagnosis-popup" name="diagnosis" 
                    value="<?php echo htmlspecialchars($formData['diagnosis'] ?? ''); ?>" disabled>
                </label>
        </div>
        </div>
        <br>


        <div class="signature-row">
    <div class="signature-group" style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
        <div class="signature-item" style="margin-right: 10px;">
            <label style="margin-right: 10px;">ลงชื่อผู้ป่วย</label>
            <input type="text" class="dotted-line" style="width: 200px;" value="">
        </div>
        <div class="signature-item" style="display: flex; flex-direction: column;">
            <div style="margin-bottom: 10px;">
                <label style="margin-right: 10px;">ลงชื่อผู้อนุมัติ</label>
                <input type="text" class="dotted-line" style="width: 200px;" value="<?php echo htmlspecialchars($formData['approved_by'] ?? ''); ?>">
            </div>
            <div style="margin-left: 10px;">
                <label style="margin-right: 10px;">ตำแหน่ง</label>
                <input type="text" class="dotted-line" style="width: 200px;">
            </div>
        </div>
    </div>
</div>

        <div class="doctor-section">
            <h3>ความเห็นแพทย์</h3>
            <div class="form-group">
                <label>ข้าพเจ้านายแพทย์/แพทย์หญิง </label>
                <input type="text"> ใบอนุญาตที่ <input type="text">
            </div>
            <div class="form-group">
                <label>ได้ทำการตรวจรักษา (นาย/นาง/น.ส.) </label>
                <input type="text"> เลขประจำตัวคนไข้ <input type="text">
            </div>
            <div class="form-group">
                <label>เมื่อวันที่ </label>
                <input type="text"> มีอาการ <input type="text">
            </div>
            <div class="form-group">
                <label>การวินิจฉัยโรค </label>
                <input type="text">
            </div>
            <div class="form-group">
                <label>ความเห็น </label>
                <input type="text">
            </div>
            <br>
            <label style="display: inline-block;">แพทย์ผู้ตรวจ</label><input type="text" class="dotted-line" style="display: inline-block; width: 200px;">
        </div>

        <p>หมายเหตุ โปรดส่งใบส่งตัวให้โรงพยาบาลทั้งชุด รวม 3 ฉบับ<br>
            (1.ต้นฉบับ 2.สำเนาสีชมพู-สำหรับโรงพยาบาล 3.สำเนาสีเขียว-ผู้ป่วยนำกลับบริษัท)</p>

            <div class="button-group">
    <button class="save-button" onclick="saveForm()">บันทึก</button>
    <?php if ($_SESSION['role'] !== 'register' &&  $_SESSION ['role'] !== 'nurse' ) : ?>
        <button class="approve-button" onclick="approveForm(<?php echo $id; ?>)">อนุมัติ</button>
    <?php endif; ?>
</div>
    </div>
    <script src="form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
            function approveForm(id) {
                Swal.fire({
                    title: 'ยืนยันการอนุมัติ',
                    text: 'คุณต้องการอนุมัติฟอร์มนี้ใช่หรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, อนุมัติ',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('approve_form.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                id: id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'สำเร็จ!',
                                    text: 'อนุมัติฟอร์มเรียบร้อยแล้ว',
                                    icon: 'success',
                                    confirmButtonColor: '#28a745'
                                }).then(() => {
                                    // window.location.href('../signin.html');
                                    window.location.href = '/referral-1/dashboard/authorizer.php';

                            });
                            } else {
                                Swal.fire({
                                    title: 'เกิดข้อผิดพลาด!',
                                    text: 'เกิดข้อผิดพลาด: ' + data.message,
                                    icon: 'error',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด!',
                                text: 'เกิดข้อผิดพลาดในการอนุมัติฟอร์ม',
                                icon: 'error',
                                confirmButtonColor: '#d33'
                            });
                        });
                    }
                });
            }
</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const insuranceCheckbox = document.getElementById('bill-insurance');
            const insuranceCompanyField = document.getElementById('insurance-company-field');
            
            // ตรวจสอบสถานะเริ่มต้น
            insuranceCompanyField.style.display = insuranceCheckbox.checked ? 'block' : 'none';
            
            // เพิ่ม event listener สำหรับการเปลี่ยนแปลง
            insuranceCheckbox.addEventListener('change', function() {
                insuranceCompanyField.style.display = this.checked ? 'block' : 'none';
                if (!this.checked) {
                    document.getElementById('insurance-company').value = '';
                }
            });
        });
</script>
    <script>
        function saveForm() {
            document.querySelectorAll('input[type="text"], input[type="date"], input[type="number"]').forEach(input => {
                input.setAttribute('readonly', 'readonly');
            });
            window.print();
        }
    </script>

<script>
    function selectTitle(title) {
        // ลบคลาส 'selected' จากทุก label ก่อน
        document.getElementById('label-nai').classList.remove('selected');
        document.getElementById('label-nang').classList.remove('selected');
        document.getElementById('label-nangsao').classList.remove('selected');

        // เพิ่มคลาส 'selected' ให้กับ label ที่ถูกคลิก
        document.getElementById('label-' + title).classList.add('selected');
    }
</script>




</body>

</html>