<?php
session_start();
if ($_SESSION['role'] !== 'register') {
    header('Location: ../SignupForm/signin.php');
    exit();
}

// Get fullname from session
$fullname = $_SESSION['fullname'] ?? 'ผู้ใช้งาน'; 
$hospital = $_SESSION['hospital'] ?? 'โรงพยาบาลทั่วไป';

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer</title>
    <link rel="stylesheet" href="dashboard_F.css">
    <link rel="shortcut icon" type="image/x-icon" href="http://192.168.13.31/seedhelpdesk/favicon.ico">
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&family=Prompt:wght@400;700&family=Noto+Sans+Thai:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Add sliding animation styles */
        .popup-form {
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
            transform: translateY(-100%);
            opacity: 0;
        }

        .popup-form.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="user_register.php"><img src="../assets/logo_bpk_group.png" alt="" width="160" height="40"></a>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; สวัสดีคุณ <?php echo htmlspecialchars($fullname); ?> จาก
         <?php echo htmlspecialchars($hospital); ?>
        <a href="history.html" class="nav-button">ดูประวัติการยกเลิก</a>
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
                    <label for="hospital_tf">ส่งตัวไปที่โรงพยาบาล:</label>
                    <select id="hospital_tf">
                        <option value="" disabled selected>กรุณาเลือกโรงพยาบาล</option>
                        <option value="โรงพยาบาลบางปะกอก 9">โรงพยาบาลบางปะกอก 9</option>
                        <option value="โรงพยาบาลบางปะกอก 3">โรงพยาบาลบางปะกอก 3</option>
                        <option value="โรงพยาบาลบางปะกอก 1">โรงพยาบาลบางปะกอก 1</option>
                    </select>
                </div>
                <div>
                    <label for="status">สถานะการส่งตัว:</label>
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
                            <div class="checkbox-group">
                                <label>ประเภทการเรียกเก็บ:</label>
                                <div class="checkbox-options">
                                    <label>
                                        <input type="checkbox" id="bill-company" name="billing_type[]" value="company">
                                        เรียกเก็บบริษัท
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="bill-employee" name="billing_type[]"
                                            value="employee">
                                        เรียกเก็บพนักงาน
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="bill-fund" name="billing_type[]" value="fund">
                                        เรียกเก็บกองทุนเงินทดแทน
                                    </label><br>
                                    <label class="insurance-container">
                                        <input type="checkbox" id="bill-insurance" name="billing_type[]"
                                            value="insurance">
                                        เรียกเก็บบริษัทประกัน
                                        <input type="text" id="insurance-name" class="insurance-input"
                                            placeholder="ระบุชื่อบริษัทประกัน" style="display: none;">
                                    </label>
                                </div>
                            </div>

                            <!-- เพิ่มหลังจาก billing types checkbox group -->

                            <label for="national-id-popup">เลขประจำตัวประชาชน <span class="required">*</span></label>
                            <input id="national-id-popup" placeholder="กรุณาป้อนเลขประจำตัวประชาชน" type="text"
                                maxlength="13" pattern="\d{13}" title="กรุณากรอกเลขประจำตัวประชาชน 13 หลัก" required>

                            <label for="full-name-popup">ชื่อ-นามสกุล</label>
                            <input id="full-name-popup" placeholder="กรุณาป้อนชื่อ-นามสกุล" type="text" required>

                            <label for="hospital_tf-popup">ส่งตัวไปที่โรงพยาบาล</label>
                            <select id="hospital_tf-popup" required>
                                <option value="" disabled selected>กรุณาเลือกโรงพยาบาล</option>
                                <option value="โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล">โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล</option>
                                <option value="โรงพยาบาลบางปะกอก 1">โรงพยาบาลบางปะกอก 1</option>
                                <option value="โรงพยาบาลบางปะกอก 3">โรงพยาบาลบางปะกอก 3</option>
                                <option value="โรงพยาบาลบางปะกอก 8">โรงพยาบาลบางปะกอก 8</option>
                                <option value="โรงพยาบาลบางปะกอก 2 รังสิต">โรงพยาบาลบางปะกอก 2 รังสิต</option>
                                <option value="โรงพยาบาลบางปะกอกสมุทรปราการ">โรงพยาบาลบางปะกอกสมุทรปราการ</option>
                                <option value="โรงพยาบาลปิยะเวท">โรงพยาบาลปิยะเวท</option>
                                <option value="โรงพยาบาลบางปะกอกอายุรเวช">โรงพยาบาลบางปะกอกอายุรเวช</option>
                            </select>

                            <label for="transfer-date-popup">วันที่ส่งตัว</label>
                            <input id="transfer-date-popup" type="date" required>

                            <!-- New fields to match form.html -->
                            <label for="company-popup">บริษัท/โรงงาน</label>
                            <input id="company-popup" placeholder="กรุณาป้อนชื่อบริษัท/โรงงาน" type="text">

                            <label for="address-popup">ที่อยู่</label>
                            <input id="address-popup" placeholder="กรุณาป้อนที่อยู่" type="text">

                            <label for="phone-popup">โทรศัพท์</label>
                            <input id="phone-popup" placeholder="กรุณาป้อนเบอร์โทรศัพท์" type="text" maxlength="10"
                                pattern="\d{9,10}" title="กรุณากรอกเบอร์โทรศัพท์ 9-10 หลัก">

                            <label for="age-popup">อายุ</label>
                            <input id="age-popup" placeholder="กรุณาป้อนอายุ" type="number" min="0" max="150"
                                title="กรุณากรอกอายุระหว่าง 0-150 ปี">
                            <div class="checkbox-group">
                                <label>เพื่อ:</label>
                                <div class="checkbox-options">
                                    <label>
                                        <input type="checkbox" id="purpose-checkup" name="purpose[]" value="checkup">
                                        ตรวจรักษา
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" id="purpose-annual" name="purpose[]" value="annual">
                                        ตรวจร่างกายประจำปี
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="purpose-new" name="purpose[]" value="new">
                                        ตรวจร่างกายพนักงานใหม่
                                    </label><br>
                                    <label>
                                        <input type="checkbox" id="purpose-continuous" name="purpose[]"
                                            value="continuous">
                                        รักษาต่อเนื่องจนหายที่ โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล
                                    </label>
                                </div>
                            </div>
                            <label for="approved-hospital">โรงพยาบาลที่อนุมัติ</label>
                            <select id="approved-hospital" required>
                                <option value="โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล">โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล</option>
                                <option value="โรงพยาบาลบางปะกอก 1">โรงพยาบาลบางปะกอก 1</option>
                                <option value="โรงพยาบาลบางปะกอก 3">โรงพยาบาลบางปะกอก 3</option>
                                <option value="โรงพยาบาลบางปะกอก 8">โรงพยาบาลบางปะกอก 8</option>
                                <option value="โรงพยาบาลบางปะกอก 2 รังสิต">โรงพยาบาลบางปะกอก 2 รังสิต</option>
                                <option value="โรงพยาบาลบางปะกอกสมุทรปราการ">โรงพยาบาลบางปะกอกสมุทรปราการ</option>
                                <option value="โรงพยาบาลปิยะเวท">โรงพยาบาลปิยะเวท</option>
                                <option value="โรงพยาบาลบางปะกอกอายุรเวช">โรงพยาบาลบางปะกอกอายุรเวช</option>
                            </select>

                            <label for="diagnosis-popup">การวินิจฉัยโรค</label>
                            <input id="diagnosis-popup" placeholder="กรุณาป้อนการวินิจฉัยโรค" type="text">

                            <label for="reason-popup">เหตุผลในการส่งตัว</label>
                            <input id="reason-popup" placeholder="กรุณาป้อนเหตุผลในการส่งตัว" type="text">

                            <button type="submit">บันทึก</button>
                        </form>>
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
                        <th>ตัวจัดการ !! </th> <!-- เพิ่มคอลัมน์ใหม่ -->
                        <th>ดูใบส่งตัว</th>
                        <th>ดาวน์โหลด</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                </tbody>
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
    <script>
        // srcipt ปุ่ม next , prev
        document.addEventListener('DOMContentLoaded', function() {
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
            prevPageButton.addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    displayRows();
                }
            });

            nextPageButton.addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayRows();
                }
            });

            // Initial display
            displayRows();
        });
    </script>

    <script>
        //script ปุ่ม search
        document.addEventListener('DOMContentLoaded', function() {
            const cancelButton = document.getElementById('cancel-button');
            const searchButton = document.getElementById('search-button');
            const searchForm = document.querySelector('.form-container');
            const nationalIdInput = document.getElementById('national-id');
            const fullNameInput = document.getElementById('full-name');
            const hospital_tfSelect = document.getElementById('hospital_tf');
            const startDateInput = document.getElementById('start-date');
            const endDateInput = document.getElementById('end-date');
            const tableBody = document.getElementById('table-body');
            const status = document.getElementById('status');

            // ฟังก์ชันสำหรับรีเซ็ตการค้นหา
            function resetSearch() {
                nationalIdInput.value = '';
                fullNameInput.value = '';
                hospital_tfSelect.selectedIndex = 0;
                startDateInput.value = '';
                endDateInput.value = '';
                status.selectedIndex = 0;
                fetchData(); // รีโหลดข้อมูลทั้งหมด
            }

            // ฟังก์ชันสำหรับการค้นหา
            function performSearch(event) {
                // ป้องกันการ submit form
                if (event) {
                    event.preventDefault();
                }

                const searchCriteria = {
                    nationalId: nationalIdInput.value.trim(),
                    fullName: fullNameInput.value.trim().toLowerCase(),
                    hospital_tf: hospital_tfSelect.value,
                    status: status.value,
                    startDate: startDateInput.value ? new Date(startDateInput.value) : null,
                    endDate: endDateInput.value ? new Date(endDateInput.value) : null
                };

                if (searchCriteria.startDate) {
                    searchCriteria.startDate.setHours(0, 0, 0, 0);
                }
                if (searchCriteria.endDate) {
                    searchCriteria.endDate.setHours(23, 59, 59, 999);
                }

                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 0) return;

                    try {
                        const rowData = {
                            nationalId: cells[0].textContent.trim(),
                            fullName: cells[1].textContent.trim().toLowerCase(),
                            hospital_tf: cells[2].textContent.trim(),
                            date: parseDate(cells[3].textContent.trim()),
                            status: cells[4].textContent.trim()
                        };

                        const matches = isMatch(rowData, searchCriteria);
                        row.style.display = matches ? 'table-row' : 'none';
                    } catch (error) {
                        console.error('Error processing row:', error);
                    }
                });
            }

            // Event Listeners
            cancelButton.addEventListener('click', resetSearch);
            searchButton.addEventListener('click', performSearch);

            // เพิ่ม Form Submit Event
            searchForm.addEventListener('submit', performSearch);

            // เพิ่ม Enter key event สำหรับช่อง input
            [nationalIdInput, fullNameInput].forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch();
                    }
                });
            });

            // Helper functions
            function parseDate(dateStr) {
                try {
                    const [month, day, year] = dateStr.split('/').map(num => parseInt(num, 10));
                    const date = new Date(year, month - 1, day);
                    if (isNaN(date.getTime())) {
                        throw new Error('Invalid date');
                    }
                    return date;
                } catch (error) {
                    console.error('Error parsing date:', dateStr, error);
                    return new Date(0); // Return epoch date as fallback
                }
            }

            function isMatch(rowData, criteria) {
                if (!rowData || !criteria) return true;

                if (criteria.nationalId && !rowData.nationalId.includes(criteria.nationalId)) return false;
                if (criteria.fullName && !rowData.fullName.includes(criteria.fullName)) return false;
                if (criteria.hospital_tf && rowData.hospital_tf !== criteria.hospital_tf) return false;
                if (criteria.status && rowData.status !== criteria.status) return false;

                if (criteria.startDate && criteria.endDate) {
                    return rowData.date >= criteria.startDate && rowData.date <= criteria.endDate;
                } else if (criteria.startDate) {
                    return rowData.date >= criteria.startDate;
                } else if (criteria.endDate) {
                    return rowData.date <= criteria.endDate;
                }

                return true;
            }
        });
        
    </script>

    <script>
        // เช็คเลขบัตรประชาชน
        document.addEventListener('DOMContentLoaded', function() {
            const nationalIdInput = document.getElementById('national-id');

            nationalIdInput.addEventListener('input', function(e) {
                // ลบทุกตัวอักษรที่ไม่ใช่ตัวเลข
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            nationalIdInput.addEventListener('keypress', function(e) {
                // ป้องกันการป้อนตัวอักษรที่ไม่ใช่ตัวเลข
                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                }
            });

            // ป้องกันการวาง (paste) ข้อมูลที่ไม่ใช่ตัวเลข
            nationalIdInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numericText = pastedText.replace(/[^0-9]/g, '');
                this.value = numericText.slice(0, 13); // จำกัดความยาวเป็น 13 ตัว
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addButton = document.getElementById('add-form-button');
            const popupForm = document.getElementById('popup-form');
            const closeButton = document.getElementById('close-popup');
            const transferForm = document.getElementById('transfer-form');
            const tableBody = document.getElementById('table-body');

            // Make popupForm globally accessible
            window.popupForm = popupForm;

            // Define fetchData function in the global scope
            window.fetchData = function() {
                fetch('../action_dashboard/fetch_transfers.php')
                    .then(response => response.json())
                    .then(result => {
                        if (!result.success) {
                            throw new Error(result.error || 'Failed to fetch data');
                        }

                        tableBody.innerHTML = '';
                        result.data.forEach(row => {
                            const newRow = document.createElement('tr');
                            newRow.innerHTML = `
                            <td>${row.national_id || ''}</td>
                            <td>${row.full_name_tf || ''}</td>
                            <td>${row.hospital_tf || ''}</td>
                            <td>${row.transfer_date || ''}</td>
                            <td>${row.status || ''}</td>
                            
                            <td>
                                <button class="edit-button" onclick="editTransfer('${row.national_id}')">
                                    แก้ไข
                                </button>
                                <button class="cancel-button" onclick="cancelTransfer('${row.national_id}')">
                                    ยกเลิก
                                </button>
                            </td>
                            <td>
                                <a href="../form.php?id=${row.national_id}">
                                    <i class="fas fa-eye view-icon"></i>
                                </a>
                            </td>
                            <td><a href="#">ดาวน์โหลด</a></td>
                        `;
                            tableBody.appendChild(newRow);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: red;">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
                    });
            };

            // Initial data fetch
            fetchData();

            // เพิ่มฟังก์ชัน editTransfer ให้อยู่ในขอบเขตที่ถูกต้อง
            window.editTransfer = function(nationalId) {
                // Reset form
                transferForm.reset();

                // Fetch record details
                fetch(`../action_dashboard/get_transfer.php?national_id=${nationalId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const record = data.data;

                            // Populate form fields
                            document.getElementById('national-id-popup').value = record.national_id;
                            document.getElementById('national-id-popup').readOnly = true;
                            document.getElementById('full-name-popup').value = record.full_name_tf;
                            document.getElementById('hospital_tf-popup').value = record.hospital_tf;
                            document.getElementById('approved-hospital').value = record.approved_hospital || ''; // Add this line
                            // Format date for display
                            const date = new Date(record.transfer_date);
                            const formattedDate = date.toISOString().split('T')[0]; // เก็บรูปแบบ YYYY-MM-DD สำหรับ input type="date"
                            document.getElementById('transfer-date-popup').value = formattedDate;
                            document.getElementById('company-popup').value = record.company || '';
                            document.getElementById('address-popup').value = record.address || '';
                            document.getElementById('phone-popup').value = record.phone || '';
                            document.getElementById('age-popup').value = record.age || '';
                            document.getElementById('diagnosis-popup').value = record.diagnosis || '';
                            document.getElementById('reason-popup').value = record.reason || '';

                            // Show popup
                            popupForm.classList.remove('hidden');
                            requestAnimationFrame(() => {
                                popupForm.classList.add('show');
                            });

                            // Set form mode to edit
                            transferForm.setAttribute('data-mode', 'edit');
                        } else {
                            console.error('Failed to fetch record:', data.error);
                            alert('ไม่สามารถดึงข้อมูลได้');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
                    });
            };

            // ฟังก์ชันสำหรับซ่อน popup form
            function hidePopupForm() {
                popupForm.classList.remove('show');
                setTimeout(() => {
                    popupForm.classList.add('hidden');
                    transferForm.reset();
                    document.getElementById('national-id-popup').readOnly = false;
                    transferForm.removeAttribute('data-mode');
                }, 300);
            }

            // Event Listeners
            addButton.addEventListener('click', function() {
                transferForm.reset();
                transferForm.removeAttribute('data-mode');
                document.getElementById('national-id-popup').readOnly = false;
                popupForm.classList.remove('hidden');
                requestAnimationFrame(() => {
                    popupForm.classList.add('show');
                });
            });

            closeButton.addEventListener('click', hidePopupForm);

            popupForm.addEventListener('click', function(event) {
                if (event.target === popupForm) {
                    hidePopupForm();
                }
            });

            // ...existing fetchData function...

            // Modify form submit handler
            transferForm.addEventListener('submit', function(event) {
                event.preventDefault();

                // รวบรวมข้อมูลการเรียกเก็บ
                const billingTypes = [];
                document.querySelectorAll('input[name="billing_type[]"]:checked').forEach(checkbox => {
                    billingTypes.push(checkbox.value);
                });

                // รวบรวมข้อมูลวัตถุประสงค์
                const purposes = [];
                document.querySelectorAll('input[name="purpose[]"]:checked').forEach(checkbox => {
                    purposes.push(checkbox.value);
                });

                const formData = {
                    nationalId: document.getElementById('national-id-popup').value.trim(),
                    fullName: document.getElementById('full-name-popup').value.trim(),
                    hospital_tf: document.getElementById('hospital_tf-popup').value,
                    transferDate: document.getElementById('transfer-date-popup').value,
                    company: document.getElementById('company-popup').value.trim(),
                    address: document.getElementById('address-popup').value.trim(),
                    phone: document.getElementById('phone-popup').value.trim(),
                    age: document.getElementById('age-popup').value.trim(),
                    diagnosis: document.getElementById('diagnosis-popup').value.trim(),
                    reason: document.getElementById('reason-popup').value.trim(),
                    billing_type: billingTypes,
                    insurance_company: document.getElementById('insurance-name').value.trim(),
                    purpose: purposes,
                    approved_hospital: document.getElementById('approved-hospital').value // Add this line
                };

                if (!formData.nationalId || !formData.fullName || !formData.hospital_tf || !formData.transferDate) {
                    alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                    return;
                }

                const isEdit = this.getAttribute('data-mode') === 'edit';
                const endpoint = isEdit ? '../action_dashboard/update_transfer.php' : '../action_dashboard/save_transfer.php';

                fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            fetchData();
                            hidePopupForm();

                            const toast = document.getElementById('toast');
                            toast.style.display = 'block';
                            setTimeout(() => {
                                toast.style.display = 'none';
                            }, 3000);
                        } else {
                            throw new Error(data.error || 'Failed to save data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + error.message);
                    });
            });

            function hidePopupForm() {
                popupForm.classList.remove('show');
                setTimeout(() => {
                    popupForm.classList.add('hidden');
                    transferForm.reset();
                    transferForm.removeAttribute('data-mode');
                }, 300);
            }

            // เพิ่มฟังก์ชัน cancelTransfer
            window.cancelTransfer = function(nationalId) {
                if (confirm('คุณต้องการยกเลิกการส่งตัวนี้ใช่หรือไม่?')) {
                    fetch('../action_dashboard/cancel_transfer.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                nationalId: nationalId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fetchData(); // รีเฟรชตาราง
                                const toast = document.getElementById('toast');
                                toast.querySelector('.toast-title').textContent = 'ยกเลิกสำเร็จ';
                                toast.querySelector('.toast-description').textContent = 'ยกเลิกการส่งตัวเรียบร้อยแล้ว';
                                toast.style.display = 'block';
                                setTimeout(() => {
                                    toast.style.display = 'none';
                                }, 3000);
                            } else {
                                throw new Error(data.error || 'Failed to cancel transfer');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('เกิดข้อผิดพลาดในการยกเลิก: ' + error.message);
                        });
                }
            };
        });
    </script>

    <!-- เพิ่มต่อจาก script ที่มีอยู่ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // จัดการกับช่องกรอกชื่อบริษัทประกัน
            const insuranceCheckbox = document.getElementById('bill-insurance');
            const insuranceInput = document.getElementById('insurance-name');

            // เพิ่ม CSS inline สำหรับช่องกรอกชื่อบริษัทประกัน
            insuranceInput.style.marginTop = '5px';
            insuranceInput.style.width = '100%';
            insuranceInput.style.padding = '8px';
            insuranceInput.style.boxSizing = 'border-box';

            insuranceCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    insuranceInput.style.display = 'block';
                    insuranceInput.required = true;
                } else {
                    insuranceInput.style.display = 'none';
                    insuranceInput.required = false;
                    insuranceInput.value = ''; // ล้างค่าเมื่อยกเลิกการติ๊ก
                }
            });

            // เพิ่มการตรวจสอบในฟอร์มก่อนส่ง
            const transferForm = document.getElementById('transfer-form');
            transferForm.addEventListener('submit', function(event) {
                if (insuranceCheckbox.checked && !insuranceInput.value.trim()) {
                    event.preventDefault();
                    alert('กรุณาระบุชื่อบริษัทประกัน');
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ...existing code...

            // Get the close button reference
            const closeButton = document.getElementById('close-popup');

            // Add auto refresh functionality
            let refreshInterval;

            function startAutoRefresh() {
                refreshInterval = setInterval(fetchData, 5000);
            }

            function stopAutoRefresh() {
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                }
            }

            // Start auto refresh when page loads
            startAutoRefresh();

            // Stop refresh when user interacts with form
            if (popupForm) {
                popupForm.addEventListener('click', stopAutoRefresh);
            }

            // Resume refresh when form is closed
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    hidePopupForm();
                    startAutoRefresh();
                });
            }

            // Modify form submit handler
            if (transferForm) {
                transferForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    // ...existing form submission code...

                    fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fetchData(); // Immediate refresh after submit
                                hidePopupForm();
                                startAutoRefresh(); // Resume auto refresh

                                const toast = document.getElementById('toast');
                                toast.style.display = 'block';
                                setTimeout(() => {
                                    toast.style.display = 'none';
                                }, 3000);
                            } else {
                                throw new Error(data.error || 'Failed to save data');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + error.message);
                        });
                });
            }

            // Modify cancelTransfer function
            window.cancelTransfer = function(nationalId) {
                if (confirm('คุณต้องการยกเลิกการส่งตัวนี้ใช่หรือไม่?')) {
                    stopAutoRefresh(); // Stop refresh during cancellation
                    fetch('../action_dashboard/cancel_transfer.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                nationalId: nationalId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fetchData(); // Immediate refresh after cancellation
                                startAutoRefresh(); // Resume auto refresh
                                const toast = document.getElementById('toast');
                                toast.querySelector('.toast-title').textContent = 'ยกเลิกสำเร็จ';
                                toast.querySelector('.toast-description').textContent = 'ยกเลิกการส่งตัวเรียบร้อยแล้ว';
                                toast.style.display = 'block';
                                setTimeout(() => {
                                    toast.style.display = 'none';
                                }, 3000);
                            } else {
                                throw new Error(data.error || 'Failed to cancel transfer');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('เกิดข้อผิดพลาดในการยกเลิก: ' + error.message);
                            startAutoRefresh(); // Resume auto refresh even if error occurs
                        });
                }
            };

            // Add visibility change handler
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopAutoRefresh();
                } else {
                    fetchData(); // Immediate refresh when page becomes visible
                    startAutoRefresh();
                }
            });
        });
    </script>

</body>

</html>