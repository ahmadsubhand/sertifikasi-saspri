<?php

/** @var \common\models\Certification $certification */
/** @var \common\models\SelfTeamMember[] $self_team_members */

use yii\helpers\Html;

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
    <h1>Pengajuan Sertifikasi</h1>

    <div class="card p-3 d-flex flex-column gap-2 w-100">
        <h2>Tim Mandiri</h2>

        <p>
            Pengajuan
            <?= $certification->purpose === 'level_up' ? 'sertifikasi naik ke tingkat ' : 'pengulangan sertifikasi tingkat' ?>
            <?= ucfirst($certification->level) ?>
        </p>

        <div class="d-flex flex-column gap-2">
            <div class="user-search-container">
                <input type="text" id="user-search-input" placeholder="Cari anggota tim mandiri (username) ..."
                    class="form-control" autocomplete="off">
                <div id="search-dropdown" class="search-dropdown shadow"></div>
            </div>

            <div id="selected-users-container" class="user-chips"></div>

            <form id="add-members-form" method="post"
                action="<?= \yii\helpers\Url::to(['saspri-k/tambah-anggota-tim-mandiri']) ?>">
                <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
                <input type="hidden" name="user_ids" id="selected-user-ids">
                <button type="submit" id="submit-add-btn" class="btn btn-success mt-2" style="display: none;">
                    Tambah Terpilih
                </button>
            </form>
        </div>

        <table class="table align-middle mt-3">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nama Anggota</th>
                    <th scope="col">Peran</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($self_team_members as $index => $member): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= Html::encode($member->user->username) ?>
                    </td>
                    <td>
                        <?= Html::beginForm(['ubah-peran-anggota-tim-mandiri', 'id' => $member->id], 'post') ?>
                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach (\common\enums\TeamRole::list() as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $member->role === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?= Html::endForm() ?>
                    </td>
                    <td scope="row"><?= $member->status ?></td>
                    <td>
                        <?= Html::a('Hapus', ['hapus-anggota-tim-mandiri', 'id' => $member->id], [
                'class' => 'btn btn-danger btn-sm',
                'data' => [
                  'confirm' => 'Apakah Anda yakin ingin menghapus anggota ini?',
                  'method' => 'delete',
                ],
              ]) ?>
                        <button class="btn btn-primary btn-sm">Liat</button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>

        <?php if ($certification->status === \common\enums\CertificationStatus::PENDING_SELF_TEAM_FORMATION): ?>
        <div class="d-flex justify-content-end mt-3">
            <?= Html::beginForm(['ajukan-sertifikasi'], 'post') ?>
            <button type="submit" class="btn btn-primary"
                onclick="return confirm('Apakah Anda yakin ingin mengajukan sertifikasi? Pastikan komposisi tim sudah benar.')">
                Ajukan Sertifikasi
            </button>
            <?= Html::endForm() ?>
        </div>
        <?php endif; ?>
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
                fetch('<?= \yii\helpers\Url::to(['saspri-k/cari-anggota-tim-mandiri']) ?>?q=' +
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