// 訂單詳情頁倒數計時：純前端，由後端傳入剩餘秒數（DB NOW() 算的，避免時區誤差）。
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('countdown');
    if (!el) {
        return;
    }

    var remaining = parseInt(el.dataset.seconds, 10) || 0;
    var payBtn = document.getElementById('pay-btn');
    var note = document.getElementById('countdown-note');
    var timer = null;

    function render() {
        if (remaining <= 0) {
            el.textContent = '00:00';
            el.classList.add('text-danger');
            if (note) {
                note.textContent = '訂單已過期，座位已釋出，請重新訂票。';
            }
            if (payBtn) {
                payBtn.disabled = true;
            }
            if (timer) {
                clearInterval(timer);
            }
            return;
        }
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        remaining--;
    }

    render();
    timer = setInterval(render, 1000);
});
