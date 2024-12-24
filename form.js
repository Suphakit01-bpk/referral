document.addEventListener('DOMContentLoaded', function() {
    const insuranceCheckbox = document.getElementById('bill-insurance');
    const insuranceInput = document.getElementById('insurance-name');

    // แสดง/ซ่อน input สำหรับชื่อบริษัทประกัน
    insuranceCheckbox.addEventListener('change', function() {
        insuranceInput.style.display = this.checked ? 'inline-block' : 'none';
    });

    // ตรวจสอบสถานะเริ่มต้นของ checkbox
    if (insuranceCheckbox && insuranceCheckbox.checked) {
        insuranceInput.style.display = 'inline-block';
    }

    // ทำให้ทุก input เป็น readonly เมื่อดูข้อมูล
    document.querySelectorAll('input[type="text"], input[type="checkbox"]').forEach(input => {
        input.setAttribute('readonly', 'readonly');
        if (input.type === 'checkbox') {
            input.style.pointerEvents = 'none';
        }
    });
});

// ฟังก์ชันสำหรับการพิมพ์
function printForm() {
    window.print();
}