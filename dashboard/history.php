<?php
session_start();
if ($_SESSION['role'] === 'register' || $_SESSION['role'] === 'authorizer' || $_SESSION['role'] === 'nurse') {
    // โค้ดที่ต้องการให้ทำงานเมื่อ role เป็น 'register', 'authorizer', หรือ 'nurse'
} else {
    header('Location: ../SignupForm/signin.php');
    exit();
}


// Get fullname from session
$fullname = $_SESSION['fullname'] ?? 'ผู้ใช้งาน'; 
$hospital = $_SESSION['hospital'] ?? 'โรงพยาบาลทั่วไป';

// Get hospital from session
$userHospital = $_SESSION['hospital'] ?? '';

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
    <a href="<?php echo getRedirectUrl($_SESSION['role']); ?>" class="nav-button">กลับหน้าหลัก</a>
</div>

<?php
function getRedirectUrl($role) {
    switch ($role) {
        case 'register':
            return 'user_register.php'; // เปลี่ยนเส้นทางสำหรับ role 'register'
        case 'authorizer':
            return 'authorizer.php'; // เปลี่ยนเส้นทางสำหรับ role 'authorizer'
        case 'nurse':
            return 'user_nurse.php'; // เปลี่ยนเส้นทางสำหรับ role 'nurse'
        default:
            return '../SignupForm/signin.php'; // หากไม่มี role ที่กำหนด
    }
}
?>

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
                        <option value="โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล">โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล</option>
                                <option value="โรงพยาบาลบางปะกอก 1">โรงพยาบาลบางปะกอก 1</option>
                                <option value="โรงพยาบาลบางปะกอก 3">โรงพยาบาลบางปะกอก 3</option>
                                <option value="โรงพยาบาลบางปะกอก 8">โรงพยาบาลบางปะกอก 8</option>
                                <option value="โรงพยาบาลบางปะกอก 2 รังสิต">โรงพยาบาลบางปะกอก 2 รังสิต</option>
                                <option value="โรงพยาบาลบางปะกอกสมุทรปราการ">โรงพยาบาลบางปะกอกสมุทรปราการ</option>
                                <option value="โรงพยาบาลปิยะเวท">โรงพยาบาลปิยะเวท</option>
                                <option value="โรงพยาบาลบางปะกอกอายุรเวช">โรงพยาบาลบางปะกอกอายุรเวช</option>
                    </select>
                </div>
                <div>
                    <label for="status">สถานะการส่งตัว:</label>
                    <select id="status">
                        <option value="อนุมัติ">อนุมัติ</option>
                        <option value="ยกเลิก">ยกเลิก</option>
                        <option value="" >ไม่ระบุ</option>
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
                <h3>History</h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>เลขประจำตัวประชาชน</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>ส่งตัวไปที่โรงพยาบาล</th>
                        <th>วันที่ส่งตัว</th>
                        <th>สถานะ</th>
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

            // s
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
                if (criteria.hospital_tf && !rowData.hospital_tf.includes(criteria.hospital_tf)) return false;
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
        document.addEventListener('DOMContentLoaded', function() {
            const popupForm = document.getElementById('popup-form');
            const transferForm = document.getElementById('transfer-form');
            const tableBody = document.getElementById('table-body');

            // Make popupForm globally accessible
            window.popupForm = popupForm;

            // Define fetchData function in the global scope
            window.fetchData = function() {
                fetch('../action_dashboard/fetch_history.php')
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
                                <a href="../form2.php?id=${row.id}" target="_blank">
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
        });
    </script>
</body>
</html>