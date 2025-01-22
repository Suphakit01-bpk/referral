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

// ดึงข้อมูล fullname และ hospital จากตาราง users
$fullname = '';
$hospital = '';
if ($username) {
    try {
        $result = $db->query("SELECT fullname, hospital FROM users WHERE username = $1", array($username));
        if ($result) {
            $row = pg_fetch_assoc($result);
            if ($row) {
                $fullname = $row['fullname'];
                $hospital = $row['hospital'];
                $_SESSION['hospital'] = $hospital; // เก็บค่าโรงพยาบาลไว้ใน session
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
    <link rel="stylesheet" href="dashboard_F.css">

    <link rel="shortcut icon" type="image/x-icon" href="http://192.168.13.31/seedhelpdesk/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    .logout-button {
            margin-left: auto; /* Push to right side */
            background-color: #dc3545;
        }
        
        .logout-button:hover {
            background-color: #c82333;
        }
        
        .fa-sign-out-alt {
        }
        .h1{

        }
</style>
<script>
    // ส่งค่า hospital จาก PHP session ไปให้ JavaScript
    const userHospital = "<?php echo $_SESSION['hospital']; ?>";
</script>
</head>

<body>
    <div class="navbar">
    
        
        <a href="authorizer"><img src="../Assets/logo_bpk_group.png" alt="" width="160" height="50"></a>
        <div class="user-info">
            <h1>สวัสดีคุณ 
                <span style="color: #2196F3; font-weight: bold;">
                    <?php echo htmlspecialchars($fullname); ?>
                </span> 
                จาก 
                <span style="color: #4CAF50; font-weight: bold;">
                    <?php echo htmlspecialchars($hospital); ?>
                </span>
            </h1>
            <a href="history.php" class="nav-button">ดูประวัติ</a>
            <a href="../action_dashboard/logout.php" class="nav-button logout-button">
            <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
        </a>
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
                    <label for="status">สถานะการส่งตัว:</label>
                    <select id="status">
                        <option value="" disabled selected>กรุณาเลือกสถานะ</option>
                        <option value="อนุมัติ">อนุมัติ</option>
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
                                <label for="national-id-popup">เลขประจำตัวประชาชน <span class="required">*</span></label>
                            <input id="national-id-popup" placeholder="กรุณาป้อนเลขประจำตัวประชาชน" type="text"
                                maxlength="13" pattern="\d{13}" title="กรุณากรอกเลขประจำตัวประชาชน 13 หลัก" >

                             <label for="full-name-popup">ชื่อ-นามสกุล</label>
                            <input id="full-name-popup" placeholder="กรุณาป้อนชื่อ-นามสกุล" type="text" required>

                            <label for="hospital-popup">ส่งตัวไปที่โรงพยาบาล</label>
                            <select id="hospital-popup" name="approved_hospital" required>
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

                         

                            <label for="approved-hospital-popup">โรงพยาบาลที่อนุมัติ</label>
                            <select id="approved-hospital-popup" required>
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

                            <label for="diagnosis-popup"name="diagnosis" >การวินิจฉัยโรค</label>
                            <input id="diagnosis-popup"name="diagnosis" placeholder="กรุณาป้อนการวินิจฉัยโรค" type="text">

                            <label for="reason-popup" name="reason">เหตุผลในการส่งตัว</label>
                            <input id="reason-popup"name="reason" placeholder="กรุณาป้อนเหตุผลในการส่งตัว" type="text">
                          
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
                <tbody id="table-body">
                <?php 
                    try {
                        // Calculate pagination
                        $rows_per_page = 5;
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($current_page - 1) * $rows_per_page;

                        // First, get total count for pagination
                        $count_sql = "SELECT COUNT(*) FROM transfer_form tf 
                                      WHERE tf.approved_hospital = $1 
                                      AND tf.status != 'ยกเลิก'
                                      AND (
                                        tf.status != 'อนุมัติ' 
                                        OR (
                                            tf.status = 'อนุมัติ' 
                                            AND CASE 
                                                WHEN tf.approved_date IS NOT NULL 
                                                THEN CAST(tf.approved_date AS DATE) >= CURRENT_DATE - INTERVAL '7 days'
                                                ELSE true
                                            END
                                        )
                                      )";
                        $count_params = array($hospital);
                        
                        // Add search conditions to count query if search parameters exist
                        if (isset($_GET['search'])) {
                            $searchCriteria = json_decode($_GET['search'], true);
                            
                            if (!empty($searchCriteria['nationalId'])) {
                                $count_sql .= " AND tf.national_id LIKE $" . (count($count_params) + 1);
                                $count_params[] = '%' . $searchCriteria['nationalId'] . '%';
                            }
                            
                            if (!empty($searchCriteria['fullName'])) {
                                $count_sql .= " AND LOWER(tf.full_name_tf) LIKE $" . (count($count_params) + 1);
                                $count_params[] = '%' . strtolower($searchCriteria['fullName']) . '%';
                            }
                            
                            if (!empty($searchCriteria['hospitalTF'])) {
                                $count_sql .= " AND tf.hospital_tf = $" . (count($count_params) + 1);
                                $count_params[] = $searchCriteria['hospitalTF'];
                            }

                            if (!empty($searchCriteria['status'])) {
                                $count_sql .= " AND tf.status = $" . (count($count_params) + 1);
                                $count_params[] = $searchCriteria['status'];
                            }
                            
                            if (!empty($searchCriteria['startDate']) && !empty($searchCriteria['endDate'])) {
                                $count_sql .= " AND tf.transfer_date BETWEEN $" . (count($count_params) + 1) . " AND $" . (count($count_params) + 2);
                                $count_params[] = $searchCriteria['startDate'];
                                $count_params[] = $searchCriteria['endDate'];
                            }
                        }
                        
                        $count_result = $db->query($count_sql, $count_params);
                        $total_rows = pg_fetch_result($count_result, 0, 0);
                        $total_pages = ceil($total_rows / $rows_per_page);

                        // Then, get data with pagination
                        $sql = "SELECT DISTINCT ON (tf.id) tf.*, u.fullname as user_fullname 
                                FROM transfer_form tf 
                                LEFT JOIN users u ON u.hospital = tf.approved_hospital 
                                WHERE tf.approved_hospital = $1 
                                AND tf.status != 'ยกเลิก'
                                AND (
                                    tf.status != 'อนุมัติ' 
                                    OR (
                                        tf.status = 'อนุมัติ' 
                                        AND CASE 
                                            WHEN tf.approved_date IS NOT NULL 
                                            THEN CAST(tf.approved_date AS DATE) >= CURRENT_DATE - INTERVAL '7 days'
                                            ELSE true
                                        END
                                    )
                                )";
                        $params = array($hospital);

                        // Add search conditions if they exist
                        if (isset($_GET['search'])) {
                            if (!empty($searchCriteria['nationalId'])) {
                                $sql .= " AND tf.national_id LIKE $" . (count($params) + 1);
                                $params[] = '%' . $searchCriteria['nationalId'] . '%';
                            }
                            
                            if (!empty($searchCriteria['fullName'])) {
                                $sql .= " AND LOWER(tf.full_name_tf) LIKE $" . (count($params) + 1);
                                $params[] = '%' . strtolower($searchCriteria['fullName']) . '%';
                            }
                            
                            if (!empty($searchCriteria['hospitalTF'])) {
                                $sql .= " AND tf.hospital_tf = $" . (count($params) + 1);
                                $params[] = $searchCriteria['hospitalTF'];
                            }

                            if (!empty($searchCriteria['status'])) {
                                $sql .= " AND tf.status = $" . (count($params) + 1);
                                $params[] = $searchCriteria['status'];
                            }
                            
                            if (!empty($searchCriteria['startDate']) && !empty($searchCriteria['endDate'])) {
                                $sql .= " AND tf.transfer_date BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
                                $params[] = $searchCriteria['startDate'];
                                $params[] = $searchCriteria['endDate'];
                            }
                        }

                        // Add pagination
                        $sql .= " ORDER BY tf.id, tf.transfer_date DESC
                                  LIMIT $rows_per_page OFFSET $offset";

                        $result = $db->query($sql, $params);
                        
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
                                        <button class='edit-button' 
                                            data-id='" . htmlspecialchars($row['id']) . "' 
                                            data-national-id='" . htmlspecialchars($row['national_id']) . "' 
                                            data-full-name='" . htmlspecialchars($row['full_name_tf']) . "' 
                                            data-hospital='" . htmlspecialchars($row['hospital_tf']) . "' 
                                            data-transfer-date='" . htmlspecialchars($row['transfer_date']) . "' 
                                            data-company='" . htmlspecialchars($row['company']) . "'
                                            data-address='" . htmlspecialchars($row['address']) . "'
                                            data-phone='" . htmlspecialchars($row['phone']) . "'
                                            data-age='" . htmlspecialchars($row['age']) . "'
                                            data-diagnosis='" . htmlspecialchars($row['diagnosis']) . "'
                                            data-reason='" . htmlspecialchars($row['reason']) . "'
                                            data-billing-type='" . htmlspecialchars(json_encode($row['billing_type'])) . "'
                                            data-insurance-company='" . htmlspecialchars($row['insurance_company']) . "'
                                            data-purpose='" . htmlspecialchars(json_encode($row['purpose'])) . "'
                                            data-approved-hospital='" . htmlspecialchars($row['approved_hospital']) . "'
                                        >
                                            <i class='fas fa-edit'></i> แก้ไข
                                        </button>
                                        <button class='delete-button' 
                                            data-id='" . htmlspecialchars($row['id']) . "'
                                            data-national-id='" . htmlspecialchars($row['national_id']) . "'
                                        >
                                            <i class='fas fa-trash-alt'></i> ยกเลิก
                                        </button>
                                      </td>";
                                echo "<td><a href='../form2.php?id=" . htmlspecialchars($row['id']) . "'><i class='fas fa-eye view-icon'></i></a></td>";
                                echo "</tr>";
                            }
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='8'>Error: " . $e->getMessage() . "</td></tr>";
                    }
                ?>
                </tbody>
            </table>

            <!-- Pagination controls -->
            <div class="pagination">
                <button id="prev-page" class="page-btn" onclick="changePage(<?php echo $current_page - 1; ?>)" 
                        <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>>
                    « Previous
                </button>
                <span class="page-info">
                    Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                </span>
                <button id="next-page" class="page-btn" onclick="changePage(<?php echo $current_page + 1; ?>)"
                        <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>>
                    Next »
                </button>
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

    <script> //script ปุ่ม search
        document.addEventListener('DOMContentLoaded', function () {
            const cancelButton = document.getElementById('cancel-button');
            const searchButton = document.getElementById('search-button');
            const searchForm = document.querySelector('.form-container');
            const nationalIdInput = document.getElementById('national-id');
            const fullNameInput = document.getElementById('full-name');
            const hospitalSelect = document.getElementById('hospitalTF');
            const startDateInput = document.getElementById('start-date');
            const endDateInput = document.getElementById('end-date');
            const tableBody = document.getElementById('table-body');
            const status = document.getElementById('status');

            // ฟังก์ชันสำหรับรีเซ็ตการค้นหา
            function resetSearch() {
                nationalIdInput.value = '';
                fullNameInput.value = '';
                hospitalSelect.selectedIndex = 0;
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
                    hospitalTF: hospitalSelect.value,
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
                const ap_hospital = document.getElementById('approved-hospital-popup').value.trim();
               
                const diagnosis = document.getElementById('diagnosis-popup').value.trim();
                const reason = document.getElementById('reason-popup').value.trim();
                const status = "รอการอนุมัติ"; // Set status to "รอการอนุมัติ"

                // Validate required inputs
                if (!nationalId || !fullName || !hospital || !transferDate || !company || !address || !phone || !age || !ap_hospital) {
                    Swal.fire({
                        title: 'กรุณากรอกข้อมูลให้ครบ',
                        text: 'ตวจสอบข้อมูลให้ครบถ้วนและถูกต้อง',
                        icon: 'warning',
                        confirmButtonText: 'ตกลง'
                    });
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
                    document.getElementById('approved-hospital-popup').value = columns[20].textContent; // Add logic to fill this field if needed

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ...existing code...

            // แก้ไขการ handle click event สำหรับปุ่ม edit
            document.body.addEventListener('click', function(event) {
                if (event.target.closest('.edit-button')) {
                    const button = event.target.closest('.edit-button');
                    const row = button.closest('tr');
                    
                    // ดึงข้อมูลจาก data attributes
                    document.getElementById('national-id-popup').value = button.dataset.nationalId;
                    document.getElementById('full-name-popup').value = button.dataset.fullName;
                    document.getElementById('hospital-popup').value = button.dataset.hospital;
                    document.getElementById('transfer-date-popup').value = button.dataset.transferDate;
                    document.getElementById('company-popup').value = button.dataset.company;
                    document.getElementById('address-popup').value = button.dataset.address;
                    document.getElementById('phone-popup').value = button.dataset.phone;
                    document.getElementById('age-popup').value = button.dataset.age;
                    document.getElementById('approved-hospital-popup').value = button.dataset.approvedHospital;
                    document.getElementById('diagnosis-popup').value = button.dataset.diagnosis;
                    document.getElementById('reason-popup').value = button.dataset.reason;

                    editingRow = row;

                    // แสดงฟอร์ม
                    popupForm.classList.remove('hidden');
                    requestAnimationFrame(() => {
                        popupForm.classList.add('show');
                    });
                }
            });
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
    <script src="your-javascript-file.js"></script>
    <script>
        function changePage(page) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', page);
            window.location.search = urlParams.toString();
        }
    </script>
</body>

</html>