<?php
$mobile_menu = $setting['mobile_menu'] ?? [];
?>
<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="card-form">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Menu bawah mobile</h5>
                    <small class="text-muted">Urutan 1-4 tampil langsung di bawah. Sisanya masuk ke menu More.</small>
                </div>
                <button type="button" class="btn-save py-2 px-3" id="simpanSettingBtn">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle setting-table mb-0">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-center">Can Edit</th>
                            <th class="text-center">Tampil</th>
                            <th class="text-center setting-col-order">Posisi</th>
                            <th class="text-center setting-col-drag">Geser</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mobile_menu as $index => $menu): ?>
                        <tr class="setting-menu-row <?= !empty($menu['can_edit']) ? 'is-draggable' : 'is-locked' ?>" data-key="<?= htmlspecialchars($menu['key']) ?>" draggable="<?= !empty($menu['can_edit']) ? 'true' : 'false' ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="<?= htmlspecialchars($menu['icon']) ?> text-primary"></i>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($menu['label']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($menu['url']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= !empty($menu['can_edit']) ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= !empty($menu['can_edit']) ? 'Ya' : 'Tidak' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input menu-visible" <?= !empty($menu['visible']) ? 'checked' : '' ?> <?= empty($menu['can_edit']) ? 'disabled' : '' ?>>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark menu-order-label"><?= (int) ($menu['sort_order'] ?? ($index + 1)) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="drag-handle <?= !empty($menu['can_edit']) ? '' : 'drag-handle-disabled' ?>" title="<?= !empty($menu['can_edit']) ? 'Geser untuk ubah urutan' : 'Posisi dikunci' ?>">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card-form mb-3">
            <h5 class="mb-3">Background aplikasi</h5>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Background</label>
                    <input type="color" class="form-control-modern" id="background_color" value="<?= htmlspecialchars($setting['background_color'] ?? '#f5f7fb') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">Warna teks</label>
                    <input type="color" class="form-control-modern" id="text_color" value="<?= htmlspecialchars($setting['text_color'] ?? '#1a2a3a') ?>">
                </div>
            </div>
        </div>

        <div class="card-form">
            <h6 class="mb-2">Preview</h6>
            <div class="setting-preview" id="settingPreview">
                <div class="setting-preview-title">AZurnal</div>
                <div class="setting-preview-body">Warna ini akan dipakai sebagai theme utama halaman.</div>
            </div>
        </div>
    </div>
</div>

<style>
.setting-table th,
.setting-table td {
    vertical-align: middle;
}

.setting-col-order,
.setting-col-drag {
    width: 90px;
}

.setting-menu-row.is-draggable {
    cursor: move;
}

.setting-menu-row.dragging {
    opacity: 0.45;
}

.setting-menu-row.drag-target {
    outline: 2px dashed rgba(45, 62, 80, 0.35);
    outline-offset: -2px;
}

.drag-handle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #475569;
}

.drag-handle-disabled {
    opacity: 0.45;
}

.setting-preview {
    border-radius: 12px;
    padding: 18px;
    background: var(--preview-bg);
    color: var(--preview-txt);
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.setting-preview-title {
    font-weight: 700;
    margin-bottom: 6px;
}

@media (max-width: 768px) {
    .setting-col-order,
    .setting-col-drag {
        width: 64px;
    }

    .setting-table small.text-muted {
        display: none;
    }
}
</style>

<script>
$(document).ready(function() {
    let draggingRow = null;

    function renderPreview() {
        var bg = $('#background_color').val();
        var txt = $('#text_color').val();
        $('#settingPreview').css({
            '--preview-bg': bg,
            '--preview-txt': txt,
            background: bg,
            color: txt
        });
    }

    function collectMenuItems() {
        var items = [];
        $('.setting-menu-row').each(function(index) {
            items.push({
                key: $(this).data('key'),
                visible: $(this).find('.menu-visible').is(':checked') ? 1 : 0,
                sort_order: index + 1
            });
        });
        return items;
    }

    function refreshOrderLabels() {
        $('.setting-menu-row').each(function(index) {
            $(this).find('.menu-order-label').text(index + 1);
        });
    }

    function initDragDrop() {
        $('.setting-menu-row.is-draggable').on('dragstart', function(e) {
            draggingRow = this;
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        });

        $('.setting-menu-row').on('dragover', function(e) {
            if (!draggingRow || draggingRow === this || !$(this).hasClass('is-draggable')) {
                return;
            }

            e.preventDefault();
            var rect = this.getBoundingClientRect();
            var midpoint = rect.top + (rect.height / 2);
            $(this).addClass('drag-target');

            if (e.originalEvent.clientY < midpoint) {
                this.parentNode.insertBefore(draggingRow, this);
            } else {
                this.parentNode.insertBefore(draggingRow, this.nextSibling);
            }

            refreshOrderLabels();
        });

        $('.setting-menu-row').on('dragleave', function() {
            $(this).removeClass('drag-target');
        });

        $('.setting-menu-row.is-draggable').on('dragend', function() {
            $('.setting-menu-row').removeClass('dragging drag-target');
            draggingRow = null;
            refreshOrderLabels();
        });
    }

    renderPreview();
    refreshOrderLabels();
    initDragDrop();
    $('#background_color, #text_color').on('input change', renderPreview);

    $('#simpanSettingBtn').click(function() {
        $.ajax({
            url: base_url + 'setting/simpan',
            type: 'POST',
            dataType: 'json',
            data: {
                background_color: $('#background_color').val(),
                text_color: $('#text_color').val(),
                menu_items: JSON.stringify(collectMenuItems())
            },
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success');
                    refreshCurrentMainContent(window.location.href, { pushState: false });
                    return;
                }

                Swal.fire('Gagal', res.message, 'error');
            }
        });
    });
});
</script>
