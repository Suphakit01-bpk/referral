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

    // Handle form submission
    transferForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const formData = {
            id: this.dataset.editId,
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

        try {
            const response = await fetch('../action_dashboard/update_transfer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            if (result.success) {
                // Show success message
                Swal.fire({
                    title: 'สำเร็จ',
                    text: 'อัพเดทข้อมูลเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload(); // Reload page to show updated data
                });
            } else {
                throw new Error(result.error || 'เกิดข้อผิดพลาดในการอัพเดทข้อมูล');
            }
        } catch (error) {
            Swal.fire({
                title: 'ผิดพลาด',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        }
    });
});

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