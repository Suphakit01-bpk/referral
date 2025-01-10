document.addEventListener('DOMContentLoaded', function() {
    // ดึงข้อมูลฟอร์มและ elements ที่จำเป็น
    const popupForm = document.getElementById('popup-form');
    const transferForm = document.getElementById('transfer-form');
    const editButtons = document.querySelectorAll('.edit-button');

    // เพิ่ม event listener สำหรับปุ่มแก้ไขทุกปุ่ม
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // ดึงข้อมูลจาก data attributes
            const id = this.dataset.id;
            const nationalId = this.dataset.nationalId;
            const fullName = this.dataset.fullName;
            const hospitalTf = this.dataset.hospitalTf;
            const transferDate = this.dataset.transferDate;
            const status = this.dataset.status;

            // กรอกข้อมูลลงในฟอร์ม
            document.getElementById('national-id-popup').value = nationalId;
            document.getElementById('full-name-popup').value = fullName;
            document.getElementById('hospital-popup').value = hospitalTf;
            document.getElementById('transfer-date-popup').value = formatDate(transferDate);

            // เปิดฟอร์ม
            popupForm.classList.remove('hidden');
            requestAnimationFrame(() => {
                popupForm.classList.add('show');
            });

            // เพิ่ม flag สำหรับระบุว่ากำลังแก้ไข
            transferForm.setAttribute('data-mode', 'edit');
            transferForm.setAttribute('data-id', id);
        });
    });

    // ฟังก์ชันแปลงรูปแบบวันที่
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toISOString().split('T')[0];
    }
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