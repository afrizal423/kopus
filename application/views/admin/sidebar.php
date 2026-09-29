<?php
$current_page = $current_page ?? $this->uri->segment(2) ?? 'dashboard';
if (empty($current_page) || $current_page === 'index') {
    $current_page = 'dashboard';
}
?>
<!-- Sidebar Navigation -->
<nav id="adminSidebar" class="col-md-3 col-lg-2 p-3 admin-sidebar d-flex flex-column">
    <div class="d-flex align-items-center justify-content-between mb-4 px-2">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <i class="fas fa-landmark text-success fs-4 flex-shrink-0"></i>
            <div class="text-truncate">
                <div class="fw-bold text-white small text-truncate"><?= htmlspecialchars($settings['cooperative_name'] ?? 'Koperasi'); ?></div>
                <span class="badge bg-secondary" style="font-size: 0.65rem;">Panel Pengawas</span>
            </div>
        </div>
        <!-- Tombol Tutup / Sembunyikan Sidebar di dalam sidebar -->
        <button type="button" class="btn btn-sm btn-outline-secondary text-white-50 p-1 btn-sidebar-toggle d-flex align-items-center justify-content-center flex-shrink-0" id="btnSidebarClose" title="Sembunyikan Sidebar" aria-label="Sembunyikan Sidebar" style="width: 28px; height: 28px;">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="d-flex flex-column gap-1 flex-grow-1">
        <a href="<?= base_url('admin'); ?>" class="<?= ($current_page === 'dashboard' || $current_page === 'admin') ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard &amp; Hasil</span>
        </a>
        <a href="<?= base_url('admin/candidates'); ?>" class="<?= $current_page === 'candidates' ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i>
            <span>Manajemen Calon</span>
        </a>
        <a href="<?= base_url('admin/voters'); ?>" class="<?= $current_page === 'voters' ? 'active' : ''; ?>">
            <i class="fas fa-id-card"></i>
            <span>DPT &amp; Kartu RFID</span>
        </a>
        <a href="<?= base_url('admin/report_turnout'); ?>" class="<?= $current_page === 'report_turnout' ? 'active' : ''; ?>">
            <i class="fas fa-user-check"></i>
            <span>Laporan Partisipasi</span>
        </a>
        <a href="<?= base_url('admin/audit_votes'); ?>" class="<?= $current_page === 'audit_votes' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>Audit Jejak Suara</span>
        </a>
        <a href="<?= base_url('admin/doorprize'); ?>" class="<?= $current_page === 'doorprize' ? 'active' : ''; ?>">
            <i class="fas fa-gift"></i>
            <span>Undian Doorprize</span>
        </a>
        <a href="<?= base_url('admin/export_results'); ?>" target="_blank">
            <i class="fas fa-file-signature"></i>
            <span>Cetak Berita Acara</span>
        </a>
        <a href="<?= base_url('voting'); ?>" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>Bilik Suara (Kiosk)</span>
        </a>
    </div>

    <div class="border-top border-secondary pt-3 mt-auto px-2">
        <div class="text-white small fw-semibold mb-1"><?= htmlspecialchars($this->session->userdata('admin_name')); ?></div>
        <div class="text-muted small mb-2 text-capitalize">Peran: <?= htmlspecialchars($this->session->userdata('admin_role')); ?></div>
        <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalChangePassword">
                <i class="fas fa-key me-1"></i> Ganti Password
            </button>
            <a href="<?= base_url('admin/logout'); ?>" class="text-danger p-0 d-inline-flex align-items-center gap-1 small">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<!-- Backdrop overlay for mobile screen drawer -->
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

<script>
$(document).ready(function() {
    function isMobile() {
        return window.innerWidth < 768;
    }

    function syncSidebarUI() {
        const isCollapsed = $('html').hasClass('sidebar-collapsed');
        const isMobileOpen = $('body').hasClass('sidebar-mobile-open');

        const $toggleBtns = $('.btn-sidebar-toggle');
        const $mainToggle = $('#sidebarToggle');

        if (isMobile()) {
            $toggleBtns.attr('title', isMobileOpen ? 'Tutup Menu' : 'Buka Menu');
            if (isMobileOpen) {
                $mainToggle.addClass('active');
            } else {
                $mainToggle.removeClass('active');
            }
        } else {
            $toggleBtns.attr('title', isCollapsed ? 'Tampilkan Sidebar' : 'Sembunyikan Sidebar');
            if (isCollapsed) {
                $mainToggle.addClass('active');
            } else {
                $mainToggle.removeClass('active');
            }
        }

        // Trigger window resize event to let charts, 3D canvases, and grids adapt
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 120);
    }

    // Toggle click handler (both from main header and inside sidebar)
    $(document).on('click', '.btn-sidebar-toggle', function(e) {
        e.preventDefault();
        if (isMobile()) {
            $('body').toggleClass('sidebar-mobile-open');
        } else {
            $('html, body').toggleClass('sidebar-collapsed');
            const nowCollapsed = $('html').hasClass('sidebar-collapsed');
            try {
                localStorage.setItem('kopus_admin_sidebar_collapsed', nowCollapsed ? '1' : '0');
            } catch(err) {}
        }
        syncSidebarUI();
    });

    // Close on backdrop click (mobile)
    $(document).on('click', '#adminSidebarBackdrop', function() {
        $('body').removeClass('sidebar-mobile-open');
        syncSidebarUI();
    });

    // Close on Escape key (mobile)
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('body').hasClass('sidebar-mobile-open')) {
            $('body').removeClass('sidebar-mobile-open');
            syncSidebarUI();
        }
    });

    // Sync on window resize (between desktop and mobile layout)
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (!isMobile()) {
                $('body').removeClass('sidebar-mobile-open');
            }
            syncSidebarUI();
        }, 100);
    });

    syncSidebarUI();
});
</script>
