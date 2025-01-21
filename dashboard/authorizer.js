document.addEventListener('DOMContentLoaded', function() {
    const popupForm = document.getElementById('popup-form');
    const transferForm = document.getElementById('transfer-form');

    // Function to fill form with data
    function fillFormWithData(data) {
        document.getElementById('national-id-popup').value = data.nationalId;
        document.getElementById('full-name-popup').value = data.fullName;
        document.getElementById('hospital-popup').value = data.hospital;
        document.getElementById('transfer-date-popup').value = data.transferDate;
        document.getElementById('company-popup').value = data.company || '';
        document.getElementById('address-popup').value = data.address || '';
        document.getElementById('phone-popup').value = data.phone || '';
        document.getElementById('age-popup').value = data.age || '';
        document.getElementById('diagnosis-popup').value = data.diagnosis || '';
        document.getElementById('reason-popup').value = data.reason || '';
        document.getElementById('approved-hospital-popup').value = data.approvedHospital || '';

        // Handle billing type checkboxes
        if (data.billingType) {
            const billingTypes = JSON.parse(data.billingType);
            document.querySelectorAll('input[name="billing_type[]"]').forEach(checkbox => {
                checkbox.checked = billingTypes.includes(checkbox.value);
            });
        }

        // Handle insurance company
        const insuranceCheckbox = document.getElementById('bill-insurance');
        const insuranceInput = document.getElementById('insurance-name');
        if (data.insuranceCompany) {
            insuranceCheckbox.checked = true;
            insuranceInput.style.display = 'block';
            insuranceInput.value = data.insuranceCompany;
        }

        // Handle purpose checkboxes
        if (data.purpose) {
            const purposes = JSON.parse(data.purpose);
            document.querySelectorAll('input[name="purpose[]"]').forEach(checkbox => {
                checkbox.checked = purposes.includes(checkbox.value);
            });
        }
    }

    // Handle edit button clicks
    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.edit-button')) {
            const button = event.target.closest('.edit-button');
            
            // Get data from button attributes
            const data = {
                id: button.dataset.id,
                nationalId: button.dataset.nationalId,
                fullName: button.dataset.fullName,
                hospital: button.dataset.hospital,
                transferDate: button.dataset.transferDate,
                company: button.dataset.company,
                address: button.dataset.address,
                phone: button.dataset.phone,
                age: button.dataset.age,
                diagnosis: button.dataset.diagnosis,
                reason: button.dataset.reason,
                billingType: button.dataset.billingType,
                insuranceCompany: button.dataset.insuranceCompany,
                purpose: button.dataset.purpose,
                approvedHospital: button.dataset.approvedHospital
            };

            // Fill form with data
            fillFormWithData(data);

            // Store the editing row ID
            transferForm.dataset.editId = data.id;

            // Show popup form
            popupForm.classList.remove('hidden');
            requestAnimationFrame(() => {
                popupForm.classList.add('show');
            });
        }
    });

    // Handle delete button clicks
    document.body.addEventListener('click', async function(event) {
        if (event.target.closest('.delete-button')) {
            const button = event.target.closest('.delete-button');
            const id = button.dataset.id;
            const nationalId = button.dataset.nationalId;
            
            // Show confirmation dialog
            const result = await Swal.fire({
                title: 'ยืนยันการยกเลิก',
                text: 'คุณต้องการยกเลิกการส่งตัวนี้ใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../action_dashboard/cancel_transfer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id,
                            nationalId: nationalId,
                            status: 'ยกเลิก' // เพิ่มสถานะยกเลิก
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        // ลบแถวออกจากตาราง
                        button.closest('tr').remove();
                        
                        Swal.fire({
                            title: 'ยกเลิกแล้ว',
                            text: 'การส่งตัวถูกยกเลิกเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            // รีโหลดหน้าเพื่อแสดงข้อมูลล่าสุด
                            location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'ไม่สามารถยกเลิกการส่งตัวได้');
                    }
                } catch (error) {
                    Swal.fire(
                        'เกิดข้อผิดพลาด',
                        error.message,
                        'error'
                    );
                }
            }
        }
    });

    // Handle form submission
    transferForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const formData = {
            nationalId: document.getElementById('national-id-popup').value,
            fullName: document.getElementById('full-name-popup').value,
            hospital_tf: document.getElementById('hospital-popup').value,
            transferDate: document.getElementById('transfer-date-popup').value,
            company: document.getElementById('company-popup').value,
            address: document.getElementById('address-popup').value,
            phone: document.getElementById('phone-popup').value,
            age: document.getElementById('age-popup').value,
            diagnosis: document.getElementById('diagnosis-popup').value,
            reason: document.getElementById('reason-popup').value,
            billing_type: Array.from(document.querySelectorAll('input[name="billing_type[]"]:checked')).map(cb => cb.value),
            insurance_company: document.getElementById('insurance-name').value,
            purpose: Array.from(document.querySelectorAll('input[name="purpose[]"]:checked')).map(cb => cb.value),
            approved_hospital: document.getElementById('approved-hospital-popup').value
        };

        // เพิ่ม ID เฉพาะเมื่อเป็นการแก้ไข
        if (this.dataset.editId) {
            formData.id = this.dataset.editId;
        }

        try {
            const endpoint = this.dataset.editId ? 
                '../action_dashboard/update_transfer.php' : 
                '../action_dashboard/save_transfer.php';

            console.log('Sending data:', formData); // Debug log

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            console.log('Response:', result); // Debug log

            if (result.success) {
                Swal.fire({
                    title: 'สำเร็จ',
                    text: this.dataset.editId ? 'อัพเดทข้อมูลเรียบร้อยแล้ว' : 'บันทึกข้อมูลเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(result.error || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        } catch (error) {
            console.error('Error:', error); // Debug log
            Swal.fire({
                title: 'ผิดพลาด',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        }
    });

    // Fill form when editing
    function fillFormWithData(data) {
        // Set the edit mode flag
        transferForm.dataset.editId = data.id;
        
        // ...existing fillFormWithData code...
    }

    // เพิ่ม event listener สำหรับปุ่มค้นหา
    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }

    // เพิ่ม event listener สำหรับการกด Enter ในช่องค้นหา
    const searchInputs = [
        'national-id',
        'full-name',
        'hospitalTF',
        'status',
        'start-date',
        'end-date'
    ].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });
        }
    });

    // เพิ่มการจัดการปุ่ม reset
    const cancelButton = document.getElementById('cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            // ล้างค่าในฟอร์มค้นหา
            document.getElementById('national-id').value = '';
            document.getElementById('full-name').value = '';
            document.getElementById('hospitalTF').selectedIndex = 0;
            document.getElementById('status').selectedIndex = 0;
            document.getElementById('start-date').value = '';
            document.getElementById('end-date').value = '';
            
            // redirect ไปยังหน้าเดิมโดยไม่มีพารามิเตอร์การค้นหา
            window.location.href = window.location.pathname;
        });
    }

    // Add pagination handling
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = parseInt(urlParams.get('page')) || 1;
            if (currentPage > 1) {
                urlParams.set('page', currentPage - 1);
                window.location.search = urlParams.toString();
            }
        });

        nextBtn.addEventListener('click', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = parseInt(urlParams.get('page')) || 1;
            const totalPages = parseInt(this.dataset.totalPages);
            if (currentPage < totalPages) {
                urlParams.set('page', currentPage + 1);
                window.location.search = urlParams.toString();
            }
        });
    }
    
    // Update performSearch function to maintain pagination
    window.performSearch = function() {
        const searchCriteria = {
            nationalId: document.getElementById('national-id').value.trim(),
            fullName: document.getElementById('full-name').value.trim(),
            hospitalTF: document.getElementById('hospitalTF').value,
            status: document.getElementById('status').value,
            startDate: document.getElementById('start-date').value,
            endDate: document.getElementById('end-date').value,
            approvedDateLimit: 7 // เพิ่มค่าจำนวนวันสำหรับการกรอง
        };

        const queryString = encodeURIComponent(JSON.stringify(searchCriteria));
        
        // Reset to page 1 when searching
        const urlParams = new URLSearchParams();
        urlParams.set('search', queryString);
        urlParams.set('page', '1');
        
        window.location.search = urlParams.toString();
    };
});

// เพิ่มฟังก์ชัน search ใหม่
function performSearch() {
    const searchCriteria = {
        nationalId: document.getElementById('national-id').value.trim(),
        fullName: document.getElementById('full-name').value.trim(),
        hospitalTF: document.getElementById('hospitalTF').value,
        status: document.getElementById('status').value,
        startDate: document.getElementById('start-date').value,
        endDate: document.getElementById('end-date').value
    };

    // สร้าง URL สำหรับการค้นหา
    const queryString = encodeURIComponent(JSON.stringify(searchCriteria));
    const url = window.location.pathname + '?search=' + queryString;

    // ทำการ reload หน้าพร้อมพารามิเตอร์การค้นหา
    window.location.href = url;
}

// ฟังก์ชันสำหรับปิด modal
function closeModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ฟังก์ชันสำหรับบันทึกการเปลี่ยนแปลง
function saveChanges() {
    const company = document.getElementById('editCompany').value;
    if (!company.trim()) {
        alert('กรุณากรอกชื่อบริษัท');
        return;
    }
    
    // TODO: เพิ่มโค้ดสำหรับบันทึกข้อมูลลงฐานข้อมูล
    
    // ปิด modal
    closeModal();
}

// แก้ไขฟังก์ชัน isMatch เพื่อตรวจสอบเงื่อนไขเพิ่มเติม
function isMatch(rowData, criteria) {
    if (!rowData || !criteria) return true;

    // เพิ่มการตรวจสอบสถานะและวันที่อนุมัติ
    if (rowData.status === 'อนุมัติ') {
        const approvedDate = new Date(rowData.approvedDate);
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        
        if (approvedDate < sevenDaysAgo) {
            return false;
        }
    }

    // ...existing matching conditions...

    return true;
}
