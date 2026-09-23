<!-- Modal Ganti Password Admin -->
<div class="modal fade" id="modalChangePassword" tabindex="-1" aria-labelledby="modalChangePasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <form action="<?= base_url('admin/change_password'); ?>" method="POST" id="formChangePassword">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($current_page ?? 'admin'); ?>">

                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-key"></i>
                        </div>
                        <h5 class="modal-title fw-bold" id="modalChangePasswordLabel">Ganti Password Akun</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Password Saat Ini (Lama)</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="inputCurrentPass" class="form-control" placeholder="Masukkan password lama" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="#inputCurrentPass" title="Tampilkan/Sembunyikan">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="inputNewPass" class="form-control" placeholder="Minimal 8 karakter (huruf besar, kecil, angka, simbol)" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="#inputNewPass" title="Tampilkan/Sembunyikan">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Checklist Aturan Password Real-Time -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="small fw-semibold text-secondary mb-2">Persyaratan Password Baru:</div>
                        <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-1" id="passRulesList">
                            <li id="ruleLength" class="d-flex align-items-center gap-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <span>Minimal 8 karakter</span>
                            </li>
                            <li id="ruleUpper" class="d-flex align-items-center gap-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <span>Mengandung minimal 1 huruf besar (A-Z)</span>
                            </li>
                            <li id="ruleLower" class="d-flex align-items-center gap-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <span>Mengandung minimal 1 huruf kecil (a-z)</span>
                            </li>
                            <li id="ruleNumber" class="d-flex align-items-center gap-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <span>Mengandung minimal 1 angka (0-9)</span>
                            </li>
                            <li id="ruleSymbol" class="d-flex align-items-center gap-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <span>Mengandung minimal 1 simbol / karakter khusus (!@#$%^&* dll)</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="inputConfirmPass" class="form-control" placeholder="Ketik ulang password baru" required autocomplete="new-password">
                            <button class="btn btn-outline-secondary btn-toggle-pass" type="button" data-target="#inputConfirmPass" title="Tampilkan/Sembunyikan">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="confirmFeedback" class="small mt-1" style="display: none;"></div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-kop-primary btn-sm" id="btnSubmitChangePass">
                        <i class="fas fa-save me-1"></i> Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.btn-toggle-pass').on('click', function() {
        const targetSelector = $(this).data('target');
        const $input = $(targetSelector);
        const $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    const $newPass = $('#inputNewPass');
    const $confirmPass = $('#inputConfirmPass');
    const $confirmFeedback = $('#confirmFeedback');

    function updateRule(elementId, isValid) {
        const $el = $('#' + elementId);
        const $icon = $el.find('i');
        if (isValid) {
            $el.removeClass('text-muted text-danger').addClass('text-success fw-semibold');
            $icon.removeClass('fa-circle text-secondary fa-times-circle text-danger')
                 .addClass('fas fa-check-circle text-success')
                 .css('font-size', '0.85rem');
        } else {
            $el.removeClass('text-success fw-semibold').addClass('text-muted');
            $icon.removeClass('fa-check-circle text-success fa-times-circle text-danger')
                 .addClass('fas fa-circle text-secondary')
                 .css('font-size', '0.5rem');
        }
    }

    function checkPasswordComplexity() {
        const val = $newPass.val() || '';
        const confirmVal = $confirmPass.val() || '';

        const hasLen = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNum = /[0-9]/.test(val);
        const hasSym = /[^a-zA-Z0-9]/.test(val);

        updateRule('ruleLength', hasLen);
        updateRule('ruleUpper', hasUpper);
        updateRule('ruleLower', hasLower);
        updateRule('ruleNumber', hasNum);
        updateRule('ruleSymbol', hasSym);

        if (confirmVal.length > 0) {
            $confirmFeedback.show();
            if (val === confirmVal) {
                $confirmFeedback.html('<span class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>Konfirmasi cocok</span>');
            } else {
                $confirmFeedback.html('<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Password konfirmasi belum cocok</span>');
            }
        } else {
            $confirmFeedback.hide();
        }
    }

    $newPass.on('input', checkPasswordComplexity);
    $confirmPass.on('input', checkPasswordComplexity);
});
</script>
