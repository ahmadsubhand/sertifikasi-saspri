<?php

use common\enums\CertificateLevel;

/** @var \common\models\SaspriK $saspri_k */
/** @var \common\models\Certification $valid_certificate */
/** @var \common\models\Certification[] $completed_certifications */
/** @var \common\models\User[] $saspri_k_members */

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
    <h1>SASPRI-Kawasan</h1>

    <a class="btn btn-primary"
        href="<?= \yii\helpers\Url::to(['saspri-k/pengajuan-sertifikasi']) ?>">Pengajuan
        Sertifikasi</a>

    <div class="d-flex flex-row w-100 justify-content-between align-items-start">
        <div class="card p-3 w-50 d-flex flex-column gap-2">
            <h2>Identitas</h2>

            <div class="d-flex flex-column">
                <div class="d-flex flex-row gap-2">
                    <div>SASPRI-K</div>
                    <div>:</div>
                    <div><?= $saspri_k->region_name ?></div>
                </div>
                <div class="d-flex flex-row gap-2">
                    <div>Nama unit usaha (koperasi)</div>
                    <div>:</div>
                    <div><?= $saspri_k->cooperative_name ?></div>
                </div>
                <div class="d-flex flex-row gap-2">
                    <div>Jumlah anggota aktif dalam kelompok yang dibina</div>
                    <div>:</div>
                    <div><?= $saspri_k->number_of_active_members ?>
                        orang</div>
                </div>
            </div>

            <div class="w-100 d-flex flex-row justify-content-between">
                <button class="btn btn-danger">Pergantian Wali</button>
                <button class="btn btn-primary">Edit Data</button>
            </div>
        </div>

        <div class="card p-3 w-25 d-flex flex-column gap-2">
            <h2>Sertifikat</h2>

            <div>
                <div class="d-flex flex-row gap-2">
                    <div>Level Sertifikat</div>
                    <div>:</div>
                    <div><?=  CertificateLevel::list()[$valid_certificate->level] ?? '-' ?></div>
                </div>
                <div class="d-flex flex-row gap-2">
                    <div>Nomor Sertifikat</div>
                    <div>:</div>
                    <div><?=  $valid_certificate->code ?></div>
                </div>
            </div>

            <button class="btn btn-primary">Download Sertifikat</button>
        </div>
    </div>

    <div class="card p-3 d-flex flex-column gap-2">
        <h2>Riwayat Sertifikasi</h2>
        <table class="table align-middle">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nomor Pengajuan</th>
                    <th scope="col">Tingkatan</th>
                    <th scope="col">Tanggal Pengajuan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($completed_certifications as $index => $certification): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= $certification->code ?: '-' ?>
                    </td>
                    <td><?= CertificateLevel::list()[$certification->level] ?? '-' ?></td>
                    <td><?= $certification->issued_at ? date('Y-m-d', strtotime($certification->issued_at)) : '-' ?>
                    </td>
                    <td>
                        <button class="btn btn-primary">Unduh</button>
                        <button class="btn btn-primary">Lihat</button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <h2>Anggota Kawasan</h2>

        <div class="d-flex flex-column gap-2">
            <div class="user-search-container">
                <input type="text" id="user-search-input" placeholder="Cari anggota baru (username) ..."
                    class="form-control" autocomplete="off">
                <div id="search-dropdown" class="search-dropdown shadow"></div>
            </div>

            <div id="selected-users-container" class="user-chips"></div>

            <form id="add-members-form" method="post"
                action="<?= \yii\helpers\Url::to(['saspri-k/tambah-anggota']) ?>">
                <?= \yii\helpers\Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
                <input type="hidden" name="user_ids" id="selected-user-ids">
                <button type="submit" id="submit-add-btn" class="btn btn-success mt-2" style="display: none;">Tambah
                    Anggota</button>
            </form>
        </div>

        <table class="table align-middle mt-3">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nama Anggota</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($saspri_k_members as $index => $user): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= \yii\helpers\Html::encode($user->username) ?>
                    </td>
                    <td>
                        <?= \yii\helpers\Html::a('Hapus', ['hapus-anggota', 'user_id' => $user->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                            'confirm' => 'Apakah Anda yakin ingin menghapus anggota ini?',
                            'method' => 'delete',
                            ],
                        ]) ?>
                        <button class="btn btn-primary btn-sm">Lihat</button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
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
                fetch('<?= \yii\helpers\Url::to(['saspri-k/cari-user']) ?>?q=' +
                        encodeURIComponent(q))
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