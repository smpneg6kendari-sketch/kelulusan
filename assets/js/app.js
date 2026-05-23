/**
 * App JavaScript
 * Website Pengumuman Kelulusan
 */

// ========================================
// Loading Overlay
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('loadingOverlay');
    if (loader) {
        setTimeout(() => {
            loader.classList.add('hidden');
        }, 600);
    }
});

// ========================================
// Mobile Navigation
// ========================================
function toggleNav() {
    const nav = document.getElementById('mobileNav');
    const overlay = document.getElementById('navOverlay');
    if (nav && overlay) {
        nav.classList.toggle('open');
        overlay.classList.toggle('open');
        document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
    }
}

// ========================================
// Countdown Timer
// ========================================
function initCountdown(targetDateStr) {
    const targetDate = new Date(targetDateStr).getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const diff = targetDate - now;
        
        const daysEl = document.getElementById('countDays');
        const hoursEl = document.getElementById('countHours');
        const minsEl = document.getElementById('countMins');
        const secsEl = document.getElementById('countSecs');
        const countdownSection = document.getElementById('countdownSection');
        const mainContent = document.getElementById('mainContent');
        
        if (diff <= 0) {
            // Pengumuman sudah dibuka
            if (countdownSection) countdownSection.style.display = 'none';
            if (mainContent) {
                mainContent.classList.remove('blur-lock');
                mainContent.style.pointerEvents = 'auto';
            }
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);
        
        if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
        if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
        if (minsEl) minsEl.textContent = String(mins).padStart(2, '0');
        if (secsEl) secsEl.textContent = String(secs).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// ========================================
// Confetti Animation
// ========================================
function launchConfetti() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);
    
    const colors = ['#0A84FF', '#22C55E', '#FFD84D', '#FF5A5A', '#4DA3FF', '#A855F7', '#F97316'];
    const shapes = ['circle', 'square', 'triangle'];
    
    for (let i = 0; i < 80; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        
        const color = colors[Math.floor(Math.random() * colors.length)];
        const shape = shapes[Math.floor(Math.random() * shapes.length)];
        const size = Math.random() * 8 + 6;
        const left = Math.random() * 100;
        const delay = Math.random() * 2;
        const duration = Math.random() * 2 + 2;
        
        piece.style.left = left + '%';
        piece.style.width = size + 'px';
        piece.style.height = size + 'px';
        piece.style.backgroundColor = color;
        piece.style.animationDelay = delay + 's';
        piece.style.animationDuration = duration + 's';
        
        if (shape === 'circle') {
            piece.style.borderRadius = '50%';
        } else if (shape === 'triangle') {
            piece.style.width = '0';
            piece.style.height = '0';
            piece.style.backgroundColor = 'transparent';
            piece.style.borderLeft = (size/2) + 'px solid transparent';
            piece.style.borderRight = (size/2) + 'px solid transparent';
            piece.style.borderBottom = size + 'px solid ' + color;
        }
        
        container.appendChild(piece);
    }
    
    setTimeout(() => container.remove(), 5000);
}

// ========================================
// Print Result
// ========================================
function printResult() {
    window.print();
}

// ========================================
// Download SKL (Generate PDF-like print)
// ========================================
function downloadSKL() {
    // Create a printable version
    const printContent = document.getElementById('hasilContent');
    if (!printContent) return;
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
    const namaSekolah = escapeHtml(document.getElementById('namaSekolahSkl')?.textContent?.trim() || 'SMP Negeri 6 Kendari');
    const alamatSekolah = escapeHtml(document.getElementById('alamatSekolahSkl')?.textContent?.trim() || '-');
    const namaKepsek = escapeHtml(document.getElementById('namaKepsekSkl')?.textContent?.trim() || '-');
    const nipKepsek = escapeHtml(document.getElementById('nipKepsekSkl')?.textContent?.trim() || '-');
    
    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Surat Keterangan Lulus</title>
            <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px; color: #1E293B; }
                .header { text-align: center; border-bottom: 3px double #0A84FF; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { font-size: 18px; color: #0A84FF; margin-bottom: 4px; }
                .header p { font-size: 12px; color: #64748B; }
                .title { text-align: center; margin-bottom: 30px; }
                .title h2 { font-size: 20px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
                .title p { font-size: 12px; color: #64748B; }
                .data-table { width: 100%; margin-bottom: 30px; }
                .data-table td { padding: 8px 12px; font-size: 14px; }
                .data-table td:first-child { font-weight: 600; width: 140px; }
                .status { display: inline-block; padding: 4px 16px; border-radius: 6px; font-weight: 700; font-size: 13px; }
                .status.lulus { background: #DCFCE7; color: #16A34A; }
                .footer { margin-top: 60px; text-align: right; font-size: 13px; }
                .footer .sign { margin-top: 60px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>${namaSekolah.toUpperCase()}</h1>
                <p>${alamatSekolah}</p>
            </div>
            <div class="title">
                <h2>Surat Keterangan Lulus</h2>
                <p>Tahun Ajaran ${document.getElementById('tahunAjaran')?.textContent || '2024/2025'}</p>
            </div>
            <p style="margin-bottom:20px; font-size:14px;">Yang bertanda tangan di bawah ini, Kepala ${namaSekolah}, menerangkan bahwa:</p>
            <table class="data-table">
                <tr><td>Nama</td><td>: ${document.getElementById('namaMurid')?.textContent || '-'}</td></tr>
                <tr><td>NISN</td><td>: ${document.getElementById('nisnMurid')?.textContent || '-'}</td></tr>
                <tr><td>Kelas</td><td>: ${document.getElementById('kelasMurid')?.textContent || '-'}</td></tr>
                <tr><td>Status</td><td>: <span class="status lulus">LULUS</span></td></tr>
            </table>
            <p style="font-size:14px;">Telah dinyatakan <strong>LULUS</strong> dari ${namaSekolah} pada tahun ajaran ${document.getElementById('tahunAjaran')?.textContent || '2024/2025'}.</p>
            <div class="footer">
                <p>Kendari, ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
                <p>Kepala Sekolah,</p>
                <p class="sign">${namaKepsek}</p>
                <p style="font-size:11px; color:#64748B;">NIP. ${nipKepsek}</p>
            </div>
        </body>
        </html>
    `);
    win.document.close();
    setTimeout(() => { win.print(); }, 500);
}

// ========================================
// Form Validation
// ========================================
function validateNISN(input) {
    // Only allow digits
    input.value = input.value.replace(/\D/g, '');
}

// ========================================
// Smooth Page Transition  
// ========================================
function navigateTo(url) {
    const loader = document.getElementById('loadingOverlay');
    if (loader) {
        loader.classList.remove('hidden');
    }
    setTimeout(() => {
        window.location.href = url;
    }, 300);
}

// ========================================
// Sambutan Modal
// ========================================
function openSambutanModal() {
    const modal = document.getElementById('sambutanModal');
    if (!modal) return;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeSambutanModal() {
    const modal = document.getElementById('sambutanModal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

document.addEventListener('click', function(event) {
    const modal = document.getElementById('sambutanModal');
    if (modal && event.target === modal) closeSambutanModal();
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeSambutanModal();
});
