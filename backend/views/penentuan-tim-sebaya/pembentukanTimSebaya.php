<?php

/** @var \common\models\Certification $certification */
/** @var \common\models\SaspriK $saspri_k */
/** @var \common\models\District $district */
/** @var \common\models\PeerTeamMember[] $peer_team_members */

use common\enums\UserRole;
use yii\helpers\Html;

$this->title = 'Pembentukan Tim Sebaya';
?>
<style>
    .user-search-container {
        position: relative;
        width: 100%;
    }

    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .search-item {
        padding: 8px 12px;
        cursor: pointer;
    }

    .search-item:hover {
        background-color: #f8f9fa;
    }

    .user-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 10px;
    }

    .chip {
        background-color: #e9ecef;
        padding: 4px 12px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chip .remove-btn {
        cursor: pointer;
        font-weight: bold;
        color: #dc3545;
    }
</style>

<div class="d-flex flex-column align-items-start gap-3">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <h2>Informasi Sertifikasi</h2>
        <p>
            SASPRI-K: <strong><?= Html::encode($saspri_k->cooperative_name) ?></strong> (<?= Html::encode($saspri_k->region_name) ?>)<br>
            Tingkat: <strong><?= Html::encode(ucfirst($certification->level)) ?></strong><br>
            Tujuan: <strong><?= Html::encode(ucfirst($certification->purpose)) ?></strong>
        </p>

        <hr>

        <h2>Kelola Anggota Tim Sebaya</h2>
        <p class="text-muted small">
            Syarat: Minimal 3 orang, 1 Facilitator (Admin), 1 Leader (Luar SASPRI-K), Anggota (Luar SASPRI-K).<br>
            Semua anggota luar SASPRI-K harus berasal dari SASPRI-K yang berbeda satu sama lain.
        </p>

        <div class="d-flex flex-column gap-2">
            <div class="user-search-container">
                <input type="text" id="user-search-input" placeholder="Cari admin atau anggota/wali SASPRI-K lain ..."
                    class="form-control" autocomplete="off">
                <div id="search-dropdown" class="search-dropdown shadow"></div>
            </div>

            <div id="selected-users-container" class="user-chips"></div>

            <form id="add-members-form" method="post"
                action="<?= \yii\helpers\Url::to(['sertifikasi/tambah-anggota-tim-sebaya', 'certification_id' => $certification->id]) ?>">
                <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
                <input type="hidden" name="user_ids" id="selected-user-ids">
                <button type="submit" id="submit-add-btn" class="btn btn-success mt-2" style="display: none;">
                    Tambah Anggota
                </button>
            </form>
        </div>

        <table class="table align-middle mt-3">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nama</th>
                    <th scope="col">SASPRI-K</th>
                    <th scope="col">Peran</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($peer_team_members as $index => $member): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td>
                        <?= Html::encode($member->user->username) ?>
                        <?php if (\Yii::$app->authManager->getAssignment(UserRole::ADMIN, $member->user_id)): ?>
                            <span class="badge bg-secondary ms-1">Admin</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $member->user->saspriK ? Html::encode($member->user->saspriK->cooperative_name) : '-' ?></td>
                    <td>
                        <?= Html::beginForm(
                            [
                                'ubah-peran-anggota-tim-sebaya', 
                                'user_id' => $member->user_id, 
                                'certification_id' => $certification->id
                            ], 
                            'post'
                        ) ?>
                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach (\common\enums\TeamRole::list() as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $member->role === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?= Html::endForm() ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $member->status === 'approved' ? 'success' : ($member->status === 'pending' ? 'warning' : 'danger') ?>">
                            <?= ucfirst($member->status) ?>
                        </span>
                    </td>
                    <td>
                        <?= Html::a('Hapus', 
                            [
                                'hapus-anggota-tim-sebaya', 
                                'user_id' => $member->user_id, 
                                'certification_id' => $certification->id,
                            ], 
                            [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'confirm' => 'Apakah Anda yakin ingin menghapus anggota ini?',
                                    'method' => 'delete',
                                ],
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($peer_team_members)): ?>
                <tr>
                    <td colspan="6" class="text-center">Belum ada anggota tim sebaya.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-end mt-3">
            <?= Html::beginForm(['ajukan-peer-review', 'certification_id' => $certification->id], 'post') ?>
            <button type="submit" class="btn btn-primary"
                onclick="return confirm('Apakah Anda yakin ingin memproses ke tahap Peer Review? Pastikan komposisi tim sudah benar.')">
                Selesaikan Pembentukan Tim
            </button>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('user-search-input');
        const dropdown = document.getElementById('search-dropdown');
        const chipsContainer = document.getElementById('selected-users-container');
        const hiddenInput = document.getElementById('selected-user-ids');
        const submitBtn = document.getElementById('submit-add-btn');

        let selectedUsers = [];
        let timeout = null;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const q = this.value;
            if (q.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            timeout = setTimeout(() => {
                const url = '<?= \yii\helpers\Url::to(['sertifikasi/cari-anggota-tim-sebaya', 'certification_id' => $certification->id]) ?>' + '&q=' + encodeURIComponent(q);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(user => {
                                if (selectedUsers.some(u => u.id === user.id))
                                    return;

                                const item = document.createElement('div');
                                item.className = 'search-item';
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
            selectedUsers.push(user);
            renderChips();
            input.value = '';
            dropdown.style.display = 'none';
            updateHiddenInput();
        }

        function removeUser(userId) {
            selectedUsers = selectedUsers.filter(u => u.id !== userId);
            renderChips();
            updateHiddenInput();
        }

        function renderChips() {
            chipsContainer.innerHTML = '';
            selectedUsers.forEach(user => {
                const chip = document.createElement('div');
                chip.className = 'chip';
                chip.innerHTML = `
                    <span>${user.username}</span>
                    <span class="remove-btn" onclick="window.removeUserFromList(${user.id})">&times;</span>
                `;
                chipsContainer.appendChild(chip);
            });
            submitBtn.style.display = selectedUsers.length > 0 ? 'block' : 'none';
        }

        function updateHiddenInput() {
            hiddenInput.value = selectedUsers.map(u => u.id).join(',');
        }

        window.removeUserFromList = removeUser;

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    });
</script>
