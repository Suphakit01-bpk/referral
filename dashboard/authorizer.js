document.addEventListener('DOMContentLoaded', function() {
    // Debug: ตรวจสอบว่าพบปุ่มแก้ไขหรือไม่
    const editButtons = document.querySelectorAll('.edit-button');
    console.log('Found edit buttons:', editButtons.length);

    const modal = document.getElementById('editModal');
    console.log('Found modal:', modal); // Debug: ตรวจสอบว่าพบ modal หรือไม่

    if (!modal) {
        console.error('Modal element not found');
        return;
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // ป้องกันการ submit form
            console.log('Edit button clicked'); // Debug: ตรวจสอบว่ามีการคลิกปุ่มหรือไม่
            
            // แสดง modal
            modal.style.display = 'flex'; // เปลี่ยนจาก add class เป็นกำหนด style โดยตรง
            
            // ดึงข้อมูลจากแถวที่ถูกคลิก
            const row = this.closest('tr');
            if (row) {
                const company = row.querySelector('td:nth-child(1)').textContent;
                const editCompanyInput = document.getElementById('editCompany');
                if (editCompanyInput) {
                    editCompanyInput.value = company;
                }
            }
        });
    });

    // เพิ่ม event listener สำหรับปุ่มปิด modal
    const closeButtons = document.querySelectorAll('.close-popup, [onclick="closeModal()"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });

    // ปิด modal เมื่อคลิกพื้นหลัง
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
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