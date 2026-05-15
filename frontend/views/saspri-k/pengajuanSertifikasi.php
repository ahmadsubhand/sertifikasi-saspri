<?php

use common\enums\ApprovalStatus;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use common\enums\TeamRole;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Certification $certification */
/** @var \common\models\SaspriK $saspri_k */
/** @var \common\models\District $district */
/** @var \common\models\SelfTeamMember[] $self_team_members */

?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <div class="d-flex align-items-center text-center">
    <a href="/saspri-k" class=" text-decoration-none text-black fs-5 me-3">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h3 class="fw-bold mb-0">Pengajuan Sertifikasi</h3>
  </div>
  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="px-4 d-flex align-items-center mx-4 justify-content-between">
      <div>
        <p class="mb-0">SASPRI-K</p>
        <p class="mb-0 fs-4">
          <strong><?= Html::encode($saspri_k->region_name) ?></strong> (<?= Html::encode($district->name) ?>)
        </p>
        <p class="mb-2 font-sm">
          Kabupaten: <?= Html::encode($district->regency->name) ?> |
          Provinsi: <?= Html::encode($district->regency->province->name) ?>
        </p>
      </div>
      <div>
        <p class="mb-0"><?= Html::encode(CertificationPurpose::list()[$certification->purpose] ?? '-') ?></p>
        <p class="mb-1 fs-4">
          <strong><?= Html::encode(CertificateLevel::list()[$certification->level] ?? '-') ?></strong>
          <i class="fa-solid fa-chevron-right"></i>
          <strong><?= Html::encode(CertificateLevel::next()[$certification->level] ?? '-') ?></strong>
        </p>
      </div>
    </div>
  </div>
  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="px-4">
      <p class=" fw-bold">Kelola Anggota Tim Mandiri</p>
      <div class="mb-4">
        <div class="user-search-container">
          <input type="text" id="user-search-input" placeholder="Cari anggota baru (username) ..."
            class="form-control dropdown-toggle border border-1 shadow-sm" autocomplete="off">
          <div id="search-dropdown" class="search-dropdown dropdown-menu shadow"></div>
        </div>

        <div id="selected-users-container" class="user-chips my-3 d-flex flex-wrap"></div>

        <form id="add-members-form" method="post"
          action="<?= Url::to(['saspri-k/tambah-anggota-tim-mandiri']) ?>">
          <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
          <input type="hidden" name="user_ids" id="selected-user-ids">
          <button type="submit" id="submit-add-btn" class="btn btn-success mt-2" style="display: none;">
            Tambah Anggota
          </button>
        </form>
      </div>
      <table class="table self-request text-center">
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
              <tD scope="row"><?= $index + 1 ?></th>
              <td><?= Html::encode($member->user->username) ?></td>
              <td>
                <?= Html::beginForm(['ubah-peran-anggota-tim-mandiri', 'user_id' => $member->user->id], 'post') ?>
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                  <?php foreach (TeamRole::list() as $value => $label): ?>
                    <?php if ($value == TeamRole::FACILITATOR) break; ?>
                    <option value="<?= $value ?>" <?= $member->role === $value ? 'selected' : '' ?>>
                      <?= $label ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?= Html::endForm() ?>
              </td>
              <td>
                <span class="badge bg-<?= $member->status === 'approved' ? 'success' : ($member->status === 'pending' ? 'warning' : 'danger') ?>">
                  <?= ApprovalStatus::list()[$member->status] ?>
                </span>
              </td>
              <td>
                <?= Html::a('<i class="fa-solid fa-xmark"></i>', ['hapus-anggota-tim-mandiri', 'user_id' => $member->user->id], [
                  'class' => 'btn s-btn-red btn-sm',
                  'data' => [
                    'confirm' => 'Apakah Anda yakin ingin menghapus anggota ini?',
                    'method' => 'delete',
                  ],
                ]) ?>
                <?= Html::a('<i class="fa-solid fa-magnifying-glass"></i>', ['#', 'user_id' => $member->id], [
                  'class' => 's-btn-main btn btn-sm',
                ]) ?>
              </td>
            </tr>
          <?php endforeach ?>
          <?php if (empty($self_team_members)): ?>
            <tr>
              <td colspan="5" class="text-center">Belum ada anggota tim mandiri.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div>
    <?= Html::beginForm(['ajukan-sertifikasi'], 'post') ?>
    <button type="submit" class="btn s-btn-green me-2 w-100 mb-3" <?php echo CertificationStatus::PENDING_SELF_TEAM_FORMATION == $certification->status ? '' : 'disabled' ?>
      onclick="return confirm('Apakah Anda yakin ingin mengajukan sertifikasi? Pastikan komposisi tim sudah benar.')">
      Ajukan Sertifikasi 
    </button>
    <?= Html::endForm() ?>
  </div>
</div>

<script>
  // code from be
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
        fetch('<?= Url::to(['saspri-k/cari-anggota-tim-mandiri']) ?>?q=' +
            encodeURIComponent(q))
          .then(response => response.json())
          .then(data => {
            dropdown.innerHTML = '';
            if (data.length > 0) {
              data.forEach(user => {
                if (selectedUsers.some(u => u.id === user.id))
                  return;

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
        <div class="d-flex bg-white shadow border-1 border m-2 align-items-center p-2 btn rounded-4" style="width: fit-content;">
          <span>${user.username}</span>
          <span class="remove-btn ms-1" onclick="window.removeUserFromList(${user.id})">&times;</span>
        </div>
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