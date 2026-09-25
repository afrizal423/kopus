<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Web Accessibility Widget (Fitur Aksesibilitas Web)
 * Khusus Bilik E-Voting: Halaman Scan RFID & Surat Suara (Ballot)
 * Dilengkapi Text Resizer, High Contrast (Dark, Yellow-Black WCAG AAA, Monochrome), 
 * Teks Tebal, Spasi Renggang, Garis Pandu Baca, dan sinkronisasi otomatis via localStorage.
 */
?>

<!-- Reading Guide Line Element -->
<div id="a11yReadingGuide" class="a11y-reading-guide-line" aria-hidden="true"></div>

<!-- Floating Action Button (FAB) Aksesibilitas -->
<div id="a11yWidgetWrap" class="a11y-fab-container">
    <button type="button" 
            id="a11yFabBtn" 
            class="a11y-fab-btn" 
            data-bs-toggle="modal" 
            data-bs-target="#a11yModal" 
            aria-label="Buka Menu Aksesibilitas Web" 
            title="Menu Aksesibilitas (Ukuran Huruf & Kontras Layar)">
        <i class="fas fa-universal-access a11y-fab-icon" aria-hidden="true"></i>
        <span class="a11y-fab-label">Aksesibilitas</span>
    </button>
</div>

<!-- Modal Dialog Aksesibilitas -->
<div class="modal fade" id="a11yModal" tabindex="-1" aria-labelledby="a11yModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <!-- Header -->
            <div class="modal-header border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle" style="width: 38px; height: 38px; flex-shrink: 0;">
                        <i class="fas fa-universal-access fs-5" aria-hidden="true"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="a11yModalLabel">Aksesibilitas &amp; Tampilan</h5>
                        <small class="text-muted" style="font-size: 0.8rem;">Bantuan penglihatan &amp; kenyamanan membaca di layar</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">
                <!-- 1. Text Resizer / Ukuran Huruf -->
                <div class="mb-4">
                    <div class="a11y-section-title">
                        <i class="fas fa-text-height text-success"></i>
                        <span>Ukuran Huruf / Tulisan</span>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <button type="button" id="a11yFontDec" class="btn btn-outline-secondary btn-sm px-2.5 py-1.5" title="Perkecil Ukuran Font" aria-label="Perkecil Ukuran Font">
                            <i class="fas fa-minus small"></i> A-
                        </button>
                        
                        <div class="btn-group a11y-font-group w-100" role="group" aria-label="Pilihan Ukuran Font">
                            <button type="button" class="btn btn-outline-secondary a11y-font-btn" data-size="normal">
                                Standar
                            </button>
                            <button type="button" class="btn btn-outline-secondary a11y-font-btn" data-size="large">
                                Besar
                            </button>
                            <button type="button" class="btn btn-outline-secondary a11y-font-btn" data-size="xlarge">
                                Ekstra
                            </button>
                            <button type="button" class="btn btn-outline-secondary a11y-font-btn" data-size="xxlarge">
                                Maksimal
                            </button>
                        </div>

                        <button type="button" id="a11yFontInc" class="btn btn-outline-secondary btn-sm px-2.5 py-1.5" title="Perbesar Ukuran Font" aria-label="Perbesar Ukuran Font">
                            A+ <i class="fas fa-plus small"></i>
                        </button>
                    </div>

                    <!-- Live Sample Preview -->
                    <div class="a11y-preview-box p-2.5 bg-light border rounded-3 text-center">
                        <small class="text-muted d-block mb-1" style="font-size: 0.725rem;">Pratinjau Langsung Ukuran Tulisan:</small>
                        <span class="fw-semibold">Surat Suara Pemilihan Pengurus Koperasi</span>
                    </div>
                </div>

                <!-- 2. Kontras Layar & Ketajaman (Contrast & Sharpness) -->
                <div class="mb-4">
                    <div class="a11y-section-title">
                        <i class="fas fa-circle-half-stroke text-success"></i>
                        <span>Ketajaman &amp; Mode Kontras Warna</span>
                    </div>

                    <div class="a11y-contrast-grid">
                        <!-- Option 1: Standar -->
                        <button type="button" class="a11y-contrast-card" data-contrast="default">
                            <span class="a11y-swatch a11y-swatch-default">
                                <i class="fas fa-sun text-warning"></i>
                            </span>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 0.85rem;">Standar</strong>
                                <small class="text-muted" style="font-size: 0.725rem;">Warna Asli</small>
                            </div>
                        </button>

                        <!-- Option 2: Kontras Gelap (Dark Mode) -->
                        <button type="button" class="a11y-contrast-card" data-contrast="dark">
                            <span class="a11y-swatch a11y-swatch-dark">
                                <i class="fas fa-moon"></i>
                            </span>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 0.85rem;">Kontras Gelap</strong>
                                <small class="text-muted" style="font-size: 0.725rem;">Latar Gelap Tajam</small>
                            </div>
                        </button>

                        <!-- Option 3: Kuning di atas Hitam (WCAG AAA Lansia) -->
                        <button type="button" class="a11y-contrast-card" data-contrast="yellow">
                            <span class="a11y-swatch a11y-swatch-yellow">
                                <strong>AAA</strong>
                            </span>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 0.85rem;">Kuning - Hitam</strong>
                                <small class="text-muted" style="font-size: 0.725rem;">Ramah Lansia / WCAG</small>
                            </div>
                        </button>

                        <!-- Option 4: Monokrom (Buta Warna) -->
                        <button type="button" class="a11y-contrast-card" data-contrast="mono">
                            <span class="a11y-swatch a11y-swatch-mono">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 0.85rem;">Monokrom</strong>
                                <small class="text-muted" style="font-size: 0.725rem;">Skala Abu-abu</small>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- 3. Peningkatan Keterbacaan Tambahan -->
                <div class="mb-3">
                    <div class="a11y-section-title">
                        <i class="fas fa-sliders text-success"></i>
                        <span>Kenyamanan Membaca Tambahan</span>
                    </div>

                    <!-- Teks Lebih Tebal -->
                    <div class="a11y-switch-row">
                        <div>
                            <strong class="d-block text-dark" style="font-size: 0.875rem;">Teks Ekstra Tebal (Bold)</strong>
                            <small class="text-muted" style="font-size: 0.75rem;">Mempertebal guratan huruf agar lebih tajam dan tegas dibaca</small>
                        </div>
                        <div class="form-check form-switch m-0 ms-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="a11yBoldSwitch">
                        </div>
                    </div>

                    <!-- Spasi Teks & Baris Lebih Renggang -->
                    <div class="a11y-switch-row">
                        <div>
                            <strong class="d-block text-dark" style="font-size: 0.875rem;">Spasi Baris Lebih Renggang</strong>
                            <small class="text-muted" style="font-size: 0.75rem;">Memberi jarak antar baris kalimat agar mata tidak cepat lelah</small>
                        </div>
                        <div class="form-check form-switch m-0 ms-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="a11ySpacingSwitch">
                        </div>
                    </div>

                    <!-- Garis Pemandu Baca -->
                    <div class="a11y-switch-row">
                        <div>
                            <strong class="d-block text-dark" style="font-size: 0.875rem;">Garis Pandu Baca (Reading Guide)</strong>
                            <small class="text-muted" style="font-size: 0.75rem;">Menampilkan garis horizontal penanda baris mengikuti sentuhan layar</small>
                        </div>
                        <div class="form-check form-switch m-0 ms-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="a11yGuideSwitch">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between">
                <button type="button" id="a11yResetBtn" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1.5">
                    <i class="fas fa-rotate-left"></i>
                    <span>Kembalikan Semula</span>
                </button>
                <button type="button" class="btn btn-success btn-sm px-4 fw-semibold" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> Terapkan &amp; Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Core Accessibility JavaScript Engine -->
<script>
(function() {
    'use strict';

    const STORAGE_KEY = 'kopus_a11y_prefs';
    const fontSteps = ['normal', 'large', 'xlarge', 'xxlarge'];

    // Default configuration
    let prefs = {
        fontSize: 'normal',
        contrast: 'default',
        bold: false,
        spacing: false,
        readingGuide: false
    };

    // Load saved preferences
    function loadPreferences() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                prefs = Object.assign({}, prefs, parsed);
            }
        } catch (e) {
            console.error('Failed reading a11y preferences', e);
        }
    }

    // Save preferences to localStorage
    function savePreferences() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
        } catch (e) {
            console.error('Failed saving a11y preferences', e);
        }
    }

    // Apply classes to document root and sync UI controls
    function applyPreferences() {
        const root = document.documentElement;

        // 1. Font Size
        fontSteps.forEach(function(s) {
            root.classList.remove('a11y-font-' + s);
        });
        if (prefs.fontSize && fontSteps.indexOf(prefs.fontSize) !== -1) {
            root.classList.add('a11y-font-' + prefs.fontSize);
        }

        // 2. Contrast
        ['dark', 'yellow', 'mono'].forEach(function(c) {
            root.classList.remove('a11y-contrast-' + c);
        });
        if (prefs.contrast && prefs.contrast !== 'default') {
            root.classList.add('a11y-contrast-' + prefs.contrast);
        }

        // 3. Bold Text
        if (prefs.bold) {
            root.classList.add('a11y-bold');
        } else {
            root.classList.remove('a11y-bold');
        }

        // 4. Extra Spacing
        if (prefs.spacing) {
            root.classList.add('a11y-spacing');
        } else {
            root.classList.remove('a11y-spacing');
        }

        // 5. Reading Guide
        if (prefs.readingGuide) {
            root.classList.add('a11y-guide-active');
        } else {
            root.classList.remove('a11y-guide-active');
        }

        // Sync Modal Controls (if rendered)
        syncControlsUI();
    }

    function syncControlsUI() {
        // Font buttons
        $('.a11y-font-btn').removeClass('active');
        $(`.a11y-font-btn[data-size="${prefs.fontSize}"]`).addClass('active');

        // Font +/- button states
        const currentIndex = fontSteps.indexOf(prefs.fontSize);
        $('#a11yFontDec').prop('disabled', currentIndex <= 0);
        $('#a11yFontInc').prop('disabled', currentIndex >= fontSteps.length - 1);

        // Contrast cards
        $('.a11y-contrast-card').removeClass('active');
        $(`.a11y-contrast-card[data-contrast="${prefs.contrast}"]`).addClass('active');

        // Switches
        $('#a11yBoldSwitch').prop('checked', !!prefs.bold);
        $('#a11ySpacingSwitch').prop('checked', !!prefs.spacing);
        $('#a11yGuideSwitch').prop('checked', !!prefs.readingGuide);
    }

    // Initialize on document ready
    $(document).ready(function() {
        loadPreferences();
        applyPreferences();

        // 1. Font Size Button Click
        $(document).on('click', '.a11y-font-btn', function() {
            const size = $(this).data('size');
            if (size && fontSteps.indexOf(size) !== -1) {
                prefs.fontSize = size;
                savePreferences();
                applyPreferences();
            }
        });

        // 2. Font Increment / Decrement
        $(document).on('click', '#a11yFontDec', function() {
            let idx = fontSteps.indexOf(prefs.fontSize);
            if (idx > 0) {
                prefs.fontSize = fontSteps[idx - 1];
                savePreferences();
                applyPreferences();
            }
        });

        $(document).on('click', '#a11yFontInc', function() {
            let idx = fontSteps.indexOf(prefs.fontSize);
            if (idx < fontSteps.length - 1) {
                prefs.fontSize = fontSteps[idx + 1];
                savePreferences();
                applyPreferences();
            }
        });

        // 3. Contrast Card Click
        $(document).on('click', '.a11y-contrast-card', function() {
            const contrast = $(this).data('contrast') || 'default';
            prefs.contrast = contrast;
            savePreferences();
            applyPreferences();
        });

        // 4. Switches
        $(document).on('change', '#a11yBoldSwitch', function() {
            prefs.bold = $(this).is(':checked');
            savePreferences();
            applyPreferences();
        });

        $(document).on('change', '#a11ySpacingSwitch', function() {
            prefs.spacing = $(this).is(':checked');
            savePreferences();
            applyPreferences();
        });

        $(document).on('change', '#a11yGuideSwitch', function() {
            prefs.readingGuide = $(this).is(':checked');
            savePreferences();
            applyPreferences();
        });

        // 5. Reset Button
        $(document).on('click', '#a11yResetBtn', function() {
            prefs = {
                fontSize: 'normal',
                contrast: 'default',
                bold: false,
                spacing: false,
                readingGuide: false
            };
            savePreferences();
            applyPreferences();
        });

        // 6. Reading Guide Pointer Movement
        const $guide = $('#a11yReadingGuide');
        $(window).on('pointermove touchmove', function(e) {
            if (!prefs.readingGuide) return;
            let clientY = null;
            if (e.originalEvent && e.originalEvent.touches && e.originalEvent.touches.length) {
                clientY = e.originalEvent.touches[0].clientY;
            } else if (e.clientY) {
                clientY = e.clientY;
            }
            if (clientY !== null) {
                $guide.css('top', clientY + 'px');
            }
        });
    });

    // Make engine methods accessible globally if needed
    window.KopusA11y = {
        apply: applyPreferences,
        getPrefs: function() { return Object.assign({}, prefs); }
    };
})();
</script>
