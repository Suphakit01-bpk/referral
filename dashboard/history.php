<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการยกเลิก</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="navbar">
        <h1>ประวัติการยกเลิกการส่งตัว</h1>
        <a href="user_register.php" class="nav-button">กลับหน้าหลัก</a>
    </div>

    <div class="container">
        <div class="form-container">
            <h3>ค้นหาประวัติการยกเลิก</h3>
            <div class="form-group">
                <div>
                    <label for="national-id">เลขประจำตัวประชาชน</label>
                    <input id="national-id" placeholder="ค้นหาเลขประจำตัวประชาชน" type="text" maxlength="13">
                </div>
                <div>
                    <label for="full-name">ชื่อ-นามสกุล</label>
                    <input id="full-name" placeholder="ค้นหาชื่อ" type="text">
                </div>
                <div>
                    <label for="start-date">วันที่ยกเลิก:</label>
                    <input id="start-date" type="date">
                </div>
                <div>
                    <label for="end-date">ถึงวันที่:</label>
                    <input id="end-date" type="date">
                </div>
            </div>
            <div class="form-actions">
                <button id="search-button">ค้นหา</button>
                <button id="cancel-button" class="cancel">ล้างการค้นหา</button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>เลขประจำตัวประชาชน</th>
                        <th>ชื่อ - นามสกุล</th>
                        <th>โรงพยาบาลที่ส่งตัว</th>
                        <th>วันที่ส่งตัว</th>
                        <th>วันที่ยกเลิก</th>
                        <th>สถานะ</th>
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
        document.addEventListener('DOMContentLoaded', function() {
            const tableBody = document.getElementById('table-body');
            
            // Fetch history data
            function fetchHistory() {
                fetch('fetch_history.php')
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
                                <td>${row.full_name_tf_tf || ''}</td>
                                <td>${row.hospital_tf || ''}</td>
                                <td>${row.transfer_date || ''}</td>
                                <td>${row.cancelled_date || ''}</td>
                                <td>${row.status || ''}</td>
                            `;
                            tableBody.appendChild(newRow);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
                    });
            }

            // Initial fetch
            fetchHistory();

            // Search functionality
            document.getElementById('search-button').addEventListener('click', function() {
                const nationalId = document.getElementById('national-id').value.trim();
                const fullName = document.getElementById('full-name').value.trim().toLowerCase();
                
                // แก้ไขการจัดก��รวันที่
                let searchStartDate = null;
                let searchEndDate = null;
                
                if (document.getElementById('start-date').value) {
                    searchStartDate = new Date(document.getElementById('start-date').value);
                    searchStartDate.setHours(0, 0, 0, 0);
                }
                if (document.getElementById('end-date').value) {
                    searchEndDate = new Date(document.getElementById('end-date').value);
                    searchEndDate.setHours(23, 59, 59, 999);
                }

                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    let match = true;

                    // Check national ID and name
                    if (nationalId && !cols[0].textContent.includes(nationalId)) match = false;
                    if (fullName && !cols[1].textContent.toLowerCase().includes(fullName)) match = false;

                    // Check date range
                    const dateStr = cols[4].textContent; // MM/DD/YYYY format
                    if (dateStr) {
                        const [month, day, year] = dateStr.split('/');
                        const rowDate = new Date(year, month - 1, day);
                        rowDate.setHours(0, 0, 0, 0);

                        if (searchStartDate && searchEndDate) {
                            if (rowDate < searchStartDate || rowDate > searchEndDate) {
                                match = false;
                            }
                        } else if (searchStartDate) {
                            if (rowDate < searchStartDate) match = false;
                        } else if (searchEndDate) {
                            if (rowDate > searchEndDate) match = false;
                        }
                    }

                    row.style.display = match ? '' : 'none';
                });
            });

            // Reset search
            document.getElementById('cancel-button').addEventListener('click', function() {
                document.getElementById('national-id').value = '';
                document.getElementById('full-name').value = '';
                document.getElementById('start-date').value = '';
                document.getElementById('end-date').value = '';
                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => row.style.display = '');
            });
        });
    </script>
</body>
</html>