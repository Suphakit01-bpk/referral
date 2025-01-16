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

        .refresh {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .refresh:hover {
            background-color: #45a049;
        }

        .fa-sync-alt {
            transition: transform 0.3s ease;
        }

        .refresh:hover .fa-sync-alt {
            transform: rotate(180deg);
        }



        .logout-button {
            margin-left: auto;
            /* Push to right side */
            background-color: #dc3545;
        }

        .logout-button:hover {
            background-color: #c82333;
        }

        .fa-sign-out-alt {
            margin-right: 5px;
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
    function getRedirectUrl($role)
    {
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
                        <option value="" disabled selected>กรุณาเลือกสถานะ</option>
                        <option value="อนุมัติ">อนุมัติ</option>
                        <option value="ยกเลิก">ยกเลิก</option>

                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button id="search-button" type="button">Search</button>
                <button id="refresh-button" class="refresh" type="button">
                    <i class="fas fa-sync-alt"></i> รีเฟรช
                </button>
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
            const searchButton = document.getElementById('search-button');
            const refreshButton = document.getElementById('refresh-button');
            const searchForm = document.querySelector('.form-container');
            const inputs = {
                nationalId: document.getElementById('national-id'),
                fullName: document.getElementById('full-name'),
                hospital_tf: document.getElementById('hospital_tf'),
                startDate: document.getElementById('start-date'),
                endDate: document.getElementById('end-date'),
                status: document.getElementById('status')
            };

            // ฟังก์ชันรีเฟรชข้อมูล
            function refreshData() {
                // เพิ่มเอฟเฟกต์การหมุนไอคอน
                const icon = refreshButton.querySelector('.fa-sync-alt');
                icon.style.transform = 'rotate(360deg)';

                // รีเซ็ตฟอร์ม
                Object.values(inputs).forEach(input => {
                    if (input.type === 'select-one') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                });

                // รีเซ็ตตัวแปรค้นหา
                window.currentSearchCriteria = null;

                // โหลดข้อมูลใหม่
                fetchData(1);

                // รีเซ็ตการหมุนไอคอนหลังจาก animation เสร็จสิ้น
                setTimeout(() => {
                    icon.style.transform = '';
                }, 300);
            }

            // ฟังก์ชันค้นหา
            function performSearch(event) {
                if (event) {
                    event.preventDefault();
                }

                const searchCriteria = {
                    nationalId: inputs.nationalId.value.trim(),
                    fullName: inputs.fullName.value.trim(),
                    hospital_tf: inputs.hospital_tf.value,
                    status: inputs.status.value,
                    startDate: inputs.startDate.value,
                    endDate: inputs.endDate.value
                };

                // เก็บค่าการค้นหาไว้
                window.currentSearchCriteria = searchCriteria;

                // ส่งคำค้นหาไปยัง API
                fetchData(1);
            }

            // Event Listeners
            refreshButton.addEventListener('click', refreshData);
            searchButton.addEventListener('click', performSearch);

            // เพิ่ม Event Listener สำหรับ Enter key ในช่องค้นหา
            Object.values(inputs).forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch();
                    }
                });
            });

            // Initial load
            fetchData(1);
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.fetchData = function(page = 1) {
                const queryParams = new URLSearchParams();
                queryParams.set('page', page.toString());

                if (window.currentSearchCriteria) {
                    Object.entries(window.currentSearchCriteria).forEach(([key, value]) => {
                        if (value) {
                            queryParams.set(key, value);
                        }
                    });
                }

                fetch(`../action_dashboard/fetch_history.php?${queryParams.toString()}`)
                    .then(response => response.json())
                    .then(result => {
                        if (!result.success) {
                            throw new Error(result.error || 'Failed to fetch data');
                        }

                        const tableBody = document.getElementById('table-body');
                        tableBody.innerHTML = '';

                        if (result.data.length === 0) {
                            tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">ไม่พบข้อมูล</td></tr>';
                        } else {
                            result.data.forEach(row => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
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
                                tableBody.appendChild(tr);
                            });
                        }

                        // Update pagination
                        const pageInfo = document.getElementById('page-info');
                        const prevButton = document.getElementById('prev-page');
                        const nextButton = document.getElementById('next-page');

                        if (result.pagination) {
                            const {
                                currentPage,
                                totalPages,
                                totalRows
                            } = result.pagination;
                            pageInfo.textContent = `หน้า ${currentPage} จาก ${totalPages} (${totalRows} รายการ)`;
                            prevButton.disabled = currentPage <= 1;
                            nextButton.disabled = currentPage >= totalPages;
                            window.currentPage = currentPage;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('table-body').innerHTML =
                            '<tr><td colspan="7" style="text-align: center; color: red;">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
                    });
            };

            // Add pagination event listeners
            document.getElementById('prev-page').addEventListener('click', () => {
                if (window.currentPage > 1) {
                    fetchData(window.currentPage - 1);
                }
            });

            document.getElementById('next-page').addEventListener('click', () => {
                fetchData(window.currentPage + 1);
            });

            // Initial load
            fetchData(1);
        });
    </script>
</body>

</html>