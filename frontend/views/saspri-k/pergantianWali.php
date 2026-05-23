<?php

use common\enums\ApprovalStatus;
use common\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\SaspriK $saspri_k */

$this->title = 'Pergantian Wali SASPRI-K';

$is_pending = $saspri_k->change_status === ApprovalStatus::PENDING;
$is_rejected = $saspri_k->change_status === ApprovalStatus::REJECTED;

$new_coordinator = null;
if ($saspri_k->new_coordinator_id) {
    $new_coordinator = User::findOne($saspri_k->new_coordinator_id);
}

?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
    <div class="d-flex align-items-center">
        <a href="<?= Url::to(['/saspri-k']) ?>" class="text-decoration-none text-black fs-5 me-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0"><?= Html::encode($this->title) ?></h3>
    </div>

    <?php if ($is_pending): ?>
        <div class="alert alert-info shadow-sm border-0">
            <i class="fa-solid fa-circle-info me-2"></i>
            Pergantian wali sedang dalam proses tinjauan oleh SASPRI-Nasional.
            <?php if ($new_coordinator): ?>
                Wali pengganti yang diajukan: <strong><?= Html::encode($new_coordinator->username) ?></strong>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($is_rejected): ?>
        <div class="alert alert-danger shadow-sm border-0">
            <i class="fa-solid fa-circle-xmark me-2"></i>
            <strong>Pengajuan pergantian wali ditolak.</strong><br>
            Alasan Penolakan: <?= Html::encode($saspri_k->change_rejection_reason) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
        <div class="px-4">
            <form id="form-pergantian-wali" method="post" action="<?= Url::to(['saspri-k/ajukan-pergantian-wali']) ?>">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pencarian Wali Pengganti</label>
                    <div class="user-search-container">
                        <input type="text" id="user-search-input" placeholder="Cari anggota (username) ..."
                            class="form-control dropdown-toggle border-black" autocomplete="off">
                        <div id="search-dropdown" class="search-dropdown dropdown-menu shadow"></div>
                    </div>

                    <div id="selected-user-container" class="mt-3">
                        <?php if ($new_coordinator): ?>
                            Calon koordinator:
                            <div class="d-flex bg-white shadow border-1 border align-items-center p-2 btn rounded-4" style="width: fit-content;">
                                <span> <?= Html::encode($new_coordinator->username) ?></span>
                                <span class="remove-btn ms-2" onclick="window.removeSelectedUser()">&times;</span>
                                <input type="hidden" name="new_coordinator_id" value="<?= $new_coordinator->id ?>">
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="new_coordinator_id" id="new-coordinator-id" required>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan Pergantian Wali</label>
                    <textarea name="change_request_reason" class="form-control border-black" rows="4" placeholder="Masukkan alasan pergantian wali..." required><?= Html::encode($saspri_k->change_request_reason) ?></textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input border-black" type="checkbox" id="check-consent" required>
                        <label class="form-check-label" for="check-consent">
                            Saya secara sadar mengajukan pergantian posisi Wali SASPRI-K kepada anggota yang saya pilih
                        </label>
                    </div>
                </div>

                <div class="w-100">
                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                        Ajukan pergantian wali
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('user-search-input');
        const dropdown = document.getElementById('search-dropdown');
        const container = document.getElementById('selected-user-container');

        let timeout = null;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const q = this.value;
            if (q.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            timeout = setTimeout(() => {
                fetch('<?= Url::to(['saspri-k/cari-anggota-saspri-k']) ?>?q=' + encodeURIComponent(q))
                    .then(response => response.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(user => {
                                const item = document.createElement('div');
                                item.className = 'search-item p-2 rounded-2 btn w-100 text-start';
                                item.textContent = user.username;
                                item.onclick = () => selectUser(user);
                                dropdown.appendChild(item);
                            });
                            dropdown.style.display = 'block';
                        } else {
                            dropdown.style.display = 'none';
                        }
                    });
            }, 300);
        });

        function selectUser(user) {
            container.innerHTML = `
            Calon koordinator:
            <div class="d-flex bg-white shadow border-1 border align-items-center justify-content-between p-2 btn rounded-4" style="width: fit-content;">
                <span>Calon koordinator: ${user.username}</span>
                <span class="remove-btn ms-2" onclick="window.removeSelectedUser()">&times;</span>
                <input type="hidden" name="new_coordinator_id" value="${user.id}">
            </div>
        `;
            input.value = '';
            dropdown.style.display = 'none';
        }

        window.removeSelectedUser = function() {
            container.innerHTML = '<input type="hidden" name="new_coordinator_id" id="new-coordinator-id" required>';
        };

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    });
</script>