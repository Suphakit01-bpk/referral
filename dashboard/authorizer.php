<?php
session_start();

// ตรวจสอบว่าเป็นผู้อนุมัติหรือไม่
if ($_SESSION['role'] !== 'authorizer') {
    header('Location: ../SignupForm/signin.php');
    exit();
}

// ตรวจสอบว่ามีการ login หรือไม่
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    // ส่ง JavaScript alert และ redirect
    echo "<script>
            alert('กรุณาเข้าสู่ระบบก่อนใช้งาน');
            window.location.href = '../SignupForm/signin.php';
          </script>";
    exit();
}
include '..\Database\db_connect.php';

// สร้างตัวแปร $pdo โดยใช้คลาส Database
use Database\Database;

$db = new Database();
if (!$db->connect()) {
    echo "<div style='color: red; background-color: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid red;'>";
    echo "<strong>Database Error:</strong> " . $db->getMessage();
    echo "<br>กรุณาติดต่อผู้ดูแลระบบ";
    echo "</div>";
    exit();
}

// ดึงข้อมูล user จาก session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// ดึงข้อมูล fullname จากตาราง users
$fullname = '';
if ($username) {
    try {
        $result = $db->query("SELECT fullname FROM users WHERE username = $1", array($username));
        if ($result) {
            $row = pg_fetch_assoc($result);
            if ($row) {
                $fullname = $row['fullname'];
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" type="image/x-icon" href="http://192.168.13.31/seedhelpdesk/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
</head>

<body>
    <div class="navbar">
    
        
        <a href="authorizer"><img src="../Assets/logo_bpk_group.png" alt="" width="160" height="50"></a>
        <div class="user-info">
            <h1>สวัสดีคุณ <?php echo htmlspecialchars($fullname); ?></h1>
            
        </div>
    </div>
    <div class="container">
        <div class="form-container">
            <h3>ค้นหาข้อมูลผู้ป่วย</h3>
            <div class="form-group">
                <!-- ฟิลด์ค้นหาข้อมูล -->
                <div>
                    <label for="national-id">เลขประจำตัวประชาชน</label>
                    <input id="national-id" placeholder="ค้นหาเลขประจำตัวประชาชน" type="text" pattern="\d*"
                        inputmode="numeric" maxlength="13" />
                </div>
                <div>
                    <label for="full-name">ชื่อ-นามสกุล:</label>
                    <input id="full-name" placeholder="ค้นหาชื่อ" type="text" />
                </div>
                <div>
                    <label for="start-date">วันที่:</label>
                    <input id="start-date" type="date" />
                </div>
                <div>
                    <label for="end-date">ถึงวันที่:</label>
                    <input id="end-date" type="date" />
                </div>
                <div>
                    <label for="hospitalTF">ส่งตัวไปที่โรงพยาบาล:</label>
                    <select id="hospitalTF">
                        <option value="" disabled selected>กรุณาเลือกโรงพยาบาล</option>
                        <option value="โรงพยาบาลบางปะกอก 9">โรงพยาบาลบางปะกอก 9</option>
                        <option value="โรงพยาบาลบางปะกอก 3">โรงพยาบาลบางปะกอก 3</option>
                        <option value="โรงพยาบาลบางปะกอก 1">โรงพยาบาลบางปะกอก 1</option>
                    </select>
                </div>
                <div>
                    <label for="status">���ถานะการส่งตัว:</label>
                    <select id="status">
                        <option value="" disabled selected>กรุณาเลือกสถานะ</option>
                        <option value="อนุมัติแล้ว">อนุมัติแล้ว</option>
                        <option value="รอการอนุมัติ">รอการอนุมัติ</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button id="search-button" type="button">Search</button>
                <button id="cancel-button" class="cancel" type="button">ยกเลิก</button>
            </div>
        </div>

        <!-- ตารางแสดงข้อมูลผู้ป่วย -->
        <div class="table-container">
            <div class="header-with-button">
                <h3>ข้อมูลผู้ป่วย</h3>
                <button id="add-form-button" type="button">เพิ่มฟอร์มส่งตัว</button>

                <!-- ฟอร์มป๊อปอัพ -->
                <div id="popup-form" class="popup-form hidden">
                    <div class="popup-content">
                        <span id="close-popup" class="close-popup">&times;</span>
                        <h3>ฟอร์มส่งตัวผู้ป่วย</h3>
                        <form id="transfer-form">
                            <label for="national-id-popup">เลขประจำตัวประชาชน</label>
                            <input id="national-id-popup" placeholder="กรุณาป้อนเลขประจำตัวประชาชน" type="text"
                                maxlength="13" required>

                            <label for="full-name-popup">ชื่อ-นามสกุล</label>
                            <input id="full-name-popup" placeholder="กรุณาป้อนชื่อ-นามสกุล" type="text" required>

                            <label for="hospital-popup">ส่งตัวไปที่โรงพยาบาล</label>
                            <select id="hospital-popup" required>
                                <option value="" disabled selected>กรุณาเลือกโรงพยาบาล</option>
                                <option value="โรงพยาบาลบางปะกอก 9">โรงพยาบาลบางปะกอก 9</option>
                                <option value="โ��งพยาบาลบางปะกอก 3">โรงพยาบาลบางปะกอก 3</option>
                                <option value="โรงพยาบาลบางปะกอก 1">โรงพยาบาลบางปะกอก 1</option>
                            </select>

                            <label for="transfer-date-popup">วันที่ส่งตัว</label>
                            <input id="transfer-date-popup" type="date" required>

                            <!-- New fields to match form.html -->
                            <label for="company-popup">บริษัท/โรงงาน</label>
                            <input id="company-popup" placeholder="กรุณาป้อนชื่อบริษัท/โรงงาน" type="text">

                            <label for="address-popup">ที่อยู่</label>
                            <input id="address-popup" placeholder="กรุณาป้อนที่อยู่" type="text">

                            <label for="phone-popup">โทรศัพท์</label>
                            <input id="phone-popup" placeholder="กรุณาป้อนเบอร์โทรศัพท์" type="text">

                            <label for="age-popup">อายุ</label>
                            <input id="age-popup" placeholder="กรุณาป้อนอายุ" type="number">

                            <label for="diagnosis-popup">การวินิจฉัยโรค</label>
                            <input id="diagnosis-popup" placeholder="กรุณาป้อนการวินิจฉัยโรค" type="text">

                            <label for="reason-popup">เหตุผลในการส่งตัว</label>
                            <input id="reason-popup" placeholder="กรุณาป้อนเหตุผลในการส่งตัว" type="text">

                            <button type="submit">บันทึก</button>
                        </form>
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>เลขประจำตัวประชาชน</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>ส่งตัวไปที่โรงพยาบาล</th>
                        <th>วันที่ส่งตัว</th>
                        <th>สถานะ</th>
                        <th>ดาวน์โหลด</th>
                        <th>การกระทำ</th> <!-- New column for actions -->
                        <th>ดูใบส่งตัว</th>
                    </tr>
                </thead>
                <?php 
                    try {
                        $result = $db->query("SELECT * FROM transfer_form ORDER BY id ASC");
                        if ($result) {
                            while ($row = pg_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['national_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['full_name_tf']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['hospital_tf']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['transfer_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td><a href='#'>ดาวน์โหลด</a></td>";
                                echo "<td>
                                        <button class='edit-button' data-id='".$row['id']."' data-national-id='".$row['national_id']."' data-full-name='".$row['full_name_tf']."' data-hospital-tf='".$row['hospital_tf']."' data-transfer-date='".$row['transfer_date']."' data-status='".$row['status']."'><i class='fas fa-edit'></i> แก้ไข</button>
                                        <button class='delete-button'><i class='fas fa-trash-alt'></i> ยกเลิก</button>
                                      </td>";
                                echo "<td><a href='../form.php?id=" . htmlspecialchars($row['id']) . "'><i class='fas fa-eye view-icon'></i></a></td>";
                                echo "</tr>";
                            }
                        }
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage();
                    }
                ?>
            </table>
            <div class="pagination">
                <button id="prev-page">« Prev</button>
                <span id="page-info"></span>
                <button id="next-page">Next »</button>
            </div>
        </div>
    </div>
    <div id="toast" class="toast">
        <div class="toast-title">ส่งข้อมูลสำเร็จ</div>
        <div class="toast-description">ข้อมูลการส่งตัวผู้ป่วยถูกส่งไปยังผู้อนุมัติแล้ว</div>
    </div>

           
        </form>
    </div>
</div>

<script src="authorizer.js"></script>
    <script>// srcipt ปุ่ม next , prev
        document.addEventListener('DOMContentLoaded', function () {
            const rowsPerPage = 10; // กำหนดจำนวนข้อมูลต่อหน้า
            let currentPage = 1;

            const tableBody = document.getElementById('table-body');
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            const prevPageButton = document.getElementById('prev-page');
            const nextPageButton = document.getElementById('next-page');
            const pageInfo = document.getElementById('page-info');

            // Function for showing rows based on current page
            function displayRows() {
                const startIndex = (currentPage - 1) * rowsPerPage;
                const endIndex = startIndex + rowsPerPage;

                rows.forEach((row, index) => {
                    row.style.display = (index >= startIndex && index < endIndex) ? 'table-row' : 'none';
                });

                // Update page info text
                pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

                // Disable/enable buttons based on the current page
                prevPageButton.disabled = currentPage === 1;
                nextPageButton.disabled = currentPage === totalPages;
            }

            // Add event listeners to the pagination buttons
            prevPageButton.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    displayRows();
                }
            });

            nextPageButton.addEventListener('click', function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayRows();
                }
            });

            // Initial display
            displayRows();
        });
    </script>

    <script> //script ปุ่ม search
        document.addEventListener('DOMContentLoaded', function () {
            const cancelButton = document.getElementById('cancel-button');
            const searchButton = document.getElementById('search-button');
            const nationalIdInput = document.getElementById('national-id');
            const fullNameInput = document.getElementById('full-name');
            const hospitalSelect = document.getElementById('hospitalTF');
            const startDateInput = document.getElementById('start-date');
            const endDateInput = document.getElementById('end-date');
            const tableBody = document.getElementById('table-body');
            const status = document.getElementById('status');

            // ระบบยกเลิก
            cancelButton.addEventListener('click', function () {
                nationalIdInput.value = '';
                fullNameInput.value = '';
                hospitalSelect.selectedIndex = 0; // Reset to the default option
                startDateInput.value = '';
                endDateInput.value = '';
                status.selectedIndex = 0;
                const rows = tableBody.querySelectorAll('tr');

                rows.forEach(row => row.style.display = 'table-row'); // แสดงผลทั้งหมด
            });

            // ระบบค้นหา
            // ระบบค้นหา
            searchButton.addEventListener('click', function () {
                const nationalId = nationalIdInput.value.trim();
                const fulltName = fullNameInput.value.trim().toLowerCase();
                const hospitalTF = hospitalSelect.value; // Get the selected hospital
                const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
                const endDate = endDateInput.value ? new Date(endDateInput.value) : null;
                const selectedStatus = status.value; // <-- ใช้ status ที่ถูกต้อง

                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    const columns = row.querySelectorAll('td');
                    const nationalIdRow = columns[0].textContent;
                    const fullName = columns[1].textContent.toLowerCase();
                    const hospital = columns[2].textContent.trim(); // ใช้ trim() เพื่อลบช่องว่าง
                    const transferDate = new Date(columns[3].textContent); // คอลัมน์ 3 เป็นวันที่ส่งตัว
                    const statusRow = columns[4].textContent; // <-- ตรวจสอบสถานะจากคอลัมน์นี้

                    let match = true;

                    // ตรวจสอบเลขประจำตัวประชาชน
                    if (nationalId && !nationalIdRow.includes(nationalId)) match = false;

                    // ตรวจสอบชื่อ
                    if (fulltName && !fullName.includes(fulltName)) match = false;

                    // ตรวจสอบโรงพยาบาล
                    if (hospitalTF && hospital !== hospitalTF) match = false; // ใช้ตรง ๆ แทน includes

                    // ตรวจสอบวันที่เริ่มต้นและสิ้นสุด
                    if (startDate && transferDate < startDate) match = false;
                    if (endDate && transferDate > endDate) match = false;

                    // ตรวจสอบสถานะการส่งตัว
                    if (selectedStatus && selectedStatus !== statusRow) match = false; // <-- ตรวจสอบสถานะที่เลือก

                    row.style.display = match ? 'table-row' : 'none'; // แสดงหรือซ่อนแถว
                });
            });

        });
    </script>

    <script> // เช็คเลขบัตรประชาชน
        document.addEventListener('DOMContentLoaded', function () {
            const nationalIdInput = document.getElementById('national-id');

            nationalIdInput.addEventListener('input', function (e) {
                // ลบทุกตัวอักษรที่ไม่ใช่ตัวเลข
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            nationalIdInput.addEventListener('keypress', function (e) {
                // ป้องกันการป้อนตัวอักษรที่ไม่ใช่ตัวเลข
                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                }
            });

            // ป้องกันการวาง (paste) ข้อมูลที่ไม่ใช่ตัวเลข
            nationalIdInput.addEventListener('paste', function (e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numericText = pastedText.replace(/[^0-9]/g, '');
                this.value = numericText.slice(0, 13); // จำกัดความยาวเป็น 13 ตัว
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addButton = document.getElementById('add-form-button');
            const popupForm = document.getElementById('popup-form');
            const closeButton = document.getElementById('close-popup');
            const transferForm = document.getElementById('transfer-form');
            let editingRow = null;

            // แสดงฟอร์มพร้อม animation
            addButton.addEventListener('click', function () {
                popupForm.classList.remove('hidden');

                // รอให้ DOM อัพเดทก่อนเพิ่ม class show
                requestAnimationFrame(() => {
                    popupForm.classList.add('show');
                });
            });

            // ซ่อนฟอร์มพร้อม animation
            function hidePopupForm() {
                popupForm.classList.remove('show');
                // รอให้ animation เสร็จก่อนซ่อนฟอร์ม
                setTimeout(() => {
                    popupForm.classList.add('hidden');
                }, 300); // ต้องตรงกับ transition duration ใน CSS
            }

            // เมื่อกดปุ่มปิดฟอร์ม
            closeButton.addEventListener('click', hidePopupForm);

            // ปิดฟอร์มเมื่อคลิกพื้นหลัง
            popupForm.addEventListener('click', function (event) {
                if (event.target === popupForm) {
                    hidePopupForm();
                }
            });

            // เมื่อ submit ฟอร์ม
            transferForm.addEventListener('submit', function (event) {
                event.preventDefault();

                // ดึงค่าจากฟอร์ม
                const nationalId = document.getElementById('national-id-popup').value.trim();
                const fullName = document.getElementById('full-name-popup').value.trim();
                const hospital = document.getElementById('hospital-popup').value;
                const transferDate = document.getElementById('transfer-date-popup').value;
                const company = document.getElementById('company-popup').value.trim();
                const address = document.getElementById('address-popup').value.trim();
                const phone = document.getElementById('phone-popup').value.trim();
                const age = document.getElementById('age-popup').value.trim();
                const diagnosis = document.getElementById('diagnosis-popup').value.trim();
                const reason = document.getElementById('reason-popup').value.trim();
                const status = "รอการอนุมัติ"; // Set status to "รอการอนุมัติ"

                // Validate inputs
                if (!nationalId || !fullName || !hospital || !transferDate || !company || !address || !phone || !age || !diagnosis || !reason) {
                    alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');
                    return;
                }

                // Validate numeric inputs
                if (isNaN(nationalId) || isNaN(phone) || isNaN(age)) {
                    alert('กรุณากรอกเฉพาะตัวเลขในช่อง เลขประจำตัวประชาชน, โทรศัพท์ และ อายุ');
                    return;
                }

                if (editingRow) {
                    // Update existing row
                    editingRow.innerHTML = `
                    <td>${nationalId}</td>
                    <td>${fullName}</td>
                    <td>${hospital}</td>
                    <td>${transferDate}</td>
                    <td>${status}</td>
                    <td><a href="#">ดาวน์โหลด</a></td>
                    <td>
                        <button class="edit-button"><i class="fas fa-edit"></i> แก้ไข</button>
                        <button class="delete-button"><i class="fas fa-trash-alt"></i> ยกเลิก</button>
                    </td>
                `;
                    editingRow = null;
                } else {
                    // เพิ่มข้อมูลใหม่ไปยังตาราง
                    const tableBody = document.getElementById('table-body');
                    const newRow = document.createElement('tr');

                    newRow.innerHTML = `
                    <td>${nationalId}</td>
                    <td>${fullName}</td>
                    <td>${hospital}</td>
                    <td>${transferDate}</td>
                    <td>${status}</td>
                    <td><a href="#">ดาวน์โหลด</a></td>
                    <td>
                        <button class="edit-button"><i class="fas fa-edit"></i> แก้ไข</button>
                        <button class="delete-button"><i class="fas fa-trash-alt"></i> ยกเลิก</button>
                    </td>
                `;

                    tableBody.appendChild(newRow);
                }

                // ซ่อนฟอร์มพร้อม animation
                hidePopupForm();

                // เคลียร์ข้อมูลในฟอร์ม
                transferForm.reset();

                const toast = document.getElementById('toast');
                toast.style.display = 'block';

                // ซ่อน toast หลังจาก 3 วินาที
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 3000);
            });

            // Add input validation for numeric fields
            const numericFields = ['national-id-popup', 'phone-popup', 'age-popup'];
            numericFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                field.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });

            // Edit and Delete button functionality
            document.getElementById('table-body').addEventListener('click', function (event) {
                if (event.target.classList.contains('edit-button')) {
                    const row = event.target.closest('tr');
                    const columns = row.querySelectorAll('td');

                    // Fill the form with existing data
                    document.getElementById('national-id-popup').value = columns[0].textContent;
                    document.getElementById('full-name-popup').value = columns[1].textContent;
                    document.getElementById('hospital-popup').value = columns[2].textContent;
                    document.getElementById('transfer-date-popup').value = columns[3].textContent;
                    document.getElementById('company-popup').value = ''; // Add logic to fill this field if needed
                    document.getElementById('address-popup').value = ''; // Add logic to fill this field if needed
                    document.getElementById('phone-popup').value = ''; // Add logic to fill this field if needed
                    document.getElementById('age-popup').value = ''; // Add logic to fill this field if needed
                    document.getElementById('diagnosis-popup').value = ''; // Add logic to fill this field if needed
                    document.getElementById('reason-popup').value = ''; // Add logic to fill this field if needed

                    editingRow = row;

                    // Show the form
                    popupForm.classList.remove('hidden');
                    requestAnimationFrame(() => {
                        popupForm.classList.add('show');
                    });
                } else if (event.target.classList.contains('delete-button')) {
                    const row = event.target.closest('tr');
                    row.remove();
                }
            });
        });
    </script>
    <script src="your-javascript-file.js"></script>
</body>

</html>