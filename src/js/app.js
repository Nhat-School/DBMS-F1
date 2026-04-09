// ============================================================
// F1 Championship Management — Client-Side JavaScript
// Handles form validation, dynamic loading, interactions
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // --- Auto-hide alerts after 5 seconds ---
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // --- Checkbox limit enforcement (max 2 drivers) ---
    const driverCheckboxes = document.querySelectorAll('.driver-checkbox');
    if (driverCheckboxes.length > 0) {
        driverCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const checked = document.querySelectorAll('.driver-checkbox:checked');
                if (checked.length > 2) {
                    this.checked = false;
                    showToast('Chỉ được chọn tối đa 2 tay đua!', 'error');
                }
            });
        });
    }

    // --- Time format validation (HH:mm:ss.SSS or HH:mm:ss) ---
    const timeInputs = document.querySelectorAll('.time-input');
    timeInputs.forEach(input => {
        input.addEventListener('blur', function () {
            const val = this.value.trim();
            if (val === '') return;
            const pattern = /^\d{2}:\d{2}:\d{2}(\.\d{1,3})?$/;
            if (!pattern.test(val)) {
                this.style.borderColor = '#E10600';
                showToast('Định dạng thời gian không hợp lệ. Vui lòng dùng HH:mm:ss', 'error');
            } else {
                this.style.borderColor = '';
            }
        });
    });

    // --- Dynamic team loading for registration page ---
    const stageSelect = document.getElementById('stage_id');
    const teamSelect = document.getElementById('team_id');

    if (stageSelect && teamSelect) {
        // When stage changes, could reload teams (currently all teams shown)
        stageSelect.addEventListener('change', function () {
            // Auto-submit or reload drivers section
        });
    }

    // --- Confirmation before saving results ---
    const resultForm = document.getElementById('result-form');
    if (resultForm) {
        resultForm.addEventListener('submit', function (e) {
            const confirmed = confirm('Bạn có chắc chắn muốn lưu kết quả chặng đua này?');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }

    // --- Clickable table rows ---
    const clickableRows = document.querySelectorAll('tr.clickable');
    clickableRows.forEach(row => {
        row.addEventListener('click', function () {
            const url = this.dataset.href;
            if (url) {
                window.location.href = url;
            }
        });
    });
});

// --- Toast notification helper ---
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.position = 'fixed';
    toast.style.top = '80px';
    toast.style.right = '24px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity 0.5s, transform 0.5s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}
