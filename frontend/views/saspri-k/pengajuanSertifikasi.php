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
/** @var \common\models\PeerTeamMember[] $peer_team_members */

$percentages = [
  CertificationStatus::PENDING_SELF_TEAM_FORMATION => '2%',
  CertificationStatus::SELF_REVIEW => '27%',
  CertificationStatus::PENDING_PEER_TEAM_FORMATION => '50%',
  CertificationStatus::PEER_REVIEW => '50%',
  CertificationStatus::EXTERNAL_REVIEW => '73%',
  CertificationStatus::COMPLETED => '100%',
];

$this->title = (string) 'Pengajuan Sertifikasi';
?>


<div class="page-cont w-100 h-100 p-md-3 d-flex flex-column gap-3">
  <div class="d-flex align-items-center text-center">
    <a href="/saspri-k" class=" text-decoration-none text-black fs-5 me-3">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h3 class="fw-bold mb-0">Pengajuan Sertifikasi</h3>
  </div>
  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="px-md-4 mx-md-4 mx-1 row align-items-center justify-content-between">
      <div class="col-md-6 my-2">
        <p class="mb-0 text-secondary small fw-semibold">SASPRI-K</p>
        <p class="mb-0 fs-4">
          <strong><?= Html::encode($saspri_k->region_name) ?></strong> (<?= Html::encode($district->name) ?>)
        </p>
        <p class="mb-0 text-muted small mt-1">
          Kabupaten: <?= Html::encode($district->regency->name) ?> |
          Provinsi: <?= Html::encode($district->regency->province->name) ?>
        </p>
      </div>

      <div class="col-md-6 my-2 d-flex flex-column align-items-start align-items-md-end">
        <p class="mb-0 text-secondary small fw-semibold"><?= Html::encode(CertificationPurpose::list()[$certification->purpose] ?? '-') ?></p>
        <p class="mb-0 fs-4 d-flex align-items-center gap-2">
          <strong><?= Html::encode(CertificateLevel::prev()[$certification->level] ?? '-') ?></strong>
          <i class="fa-solid fa-chevron-right text-muted fs-5 mx-1"></i>
          <strong><?= Html::encode(CertificateLevel::list()[$certification->level] ?? '-') ?></strong>
        </p>
      </div>

    </div>
  </div>
  <?php if ($certification->is_rejected) : ?>
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden w-100 mx-auto my-2">
      <div class="bg-danger" style="height: 5px;"></div>

      <div class="card-body p-2 p-md-4">
        <div class="d-flex align-items-center gap-3 justify-content-center mb-4">
          <div class=" text-danger rounded-circle d-flex align-items-center justify-content-center">
            <i class="fa-solid fa-circle-xmark fs-4"></i>
          </div>
          <h2 class="h4 fw-bold text-danger mb-0">Sertifikasi Ditolak</h2>
        </div>

        <hr class="text-dark opacity-10 my-4">
        <span class="text-muted small text-uppercase tracking-wider fw-bold d-block mb-2">
          Alasan Penolakan:
        </span>
        <p class="text-dark fs-5 mb-0 lh-base fw-medium">
          <?= Html::encode($certification->rejection_reason) ?>
        </p>
      </div>
    </div>
  <?php endif ?>
  <div class="bg-white px-md-5 px-2 py-4 rounded-2 shadow border-1 border text-center">
    <p class="fs-4 fw-bold">Status Sertifikasi</p>
    <div class="progress">
      <div class="progress-bar <?= $certification->is_rejected ? 'bg-danger' : ($certification->status === CertificationStatus::COMPLETED ? 'bg-success' : 's-bg-main') ?>" role="progressbar" style="width: <?= $percentages[$certification->status] ?>;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="w-100 d-flex justify-content-between text-center">
      <p class="text-start" style="width: 5rem;">Mulai</p>
      <p class="" style="width: 5rem;">Self Review</p>
      <p class="" style="width: 5rem;">Peer Review</p>
      <p class="" style="width: 5rem;">External Review</p>
      <p class="text-end" style="width: 5rem;">Selesai</p>
    </div>
  </div>

  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="px-md-4">
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
          <div id="selected-user-inputs"></div>
          <button type="submit" id="submit-add-btn" class="btn btn-success mt-2" style="display: none;">
            Tambah Anggota
          </button>
        </form>
      </div>
      <div class="mobile-scroll">
        <table class="table self-request text-center">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Nama Anggota</th>
              <th scope="col" style="min-width: 8rem;">Peran</th>
              <th scope="col">Status</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($self_team_members as $index => $member): ?>
              <tr>
                <tD scope="row"><?= $index + 1 ?></th>
                <td><?= Html::encode($member->user->full_name) ?></td>
                <td>
                  <?= Html::beginForm(['ubah-peran-anggota-tim-mandiri', 'user_id' => $member->user->id], 'post') ?>
                  <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach (TeamRole::list() as $value => $label): ?>
                      <?php if ($value == TeamRole::FACILITATOR) {
                        break;
                      } ?>
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
                    'class' => "btn s-btn-red btn-sm ". (($certification->status != CertificationStatus::PENDING_SELF_TEAM_FORMATION && $certification->is_rejected == 0) ? 'disabled' : ''),
                    'data' => [
                      'confirm' => 'Apakah Anda yakin ingin menghapus anggota ini?',
                      'method' => 'delete',
                    ],
                  ]) ?>
                  <!-- <?= Html::a('<i class="fa-solid fa-magnifying-glass"></i>', ['#', 'user_id' => $member->id], [
                    'class' => 's-btn-main btn btn-sm',
                  ]) ?> -->
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
  </div>
  <?php if ($peer_team_members != null) : ?>
    <div>
      <?= $this->render('/component/_team_table', [
        "model" => $peer_team_members,
        'is_self' => 0
      ]) ?>
    </div>
  <?php endif ?>
  <div>
    <?= Html::beginForm(['ajukan-sertifikasi'], 'post') ?>
    <button type="submit" class="btn btn-lg s-btn-green me-2 w-100 mb-3" <?php echo CertificationStatus::PENDING_SELF_TEAM_FORMATION == $certification->status ? '' : 'disabled' ?>
      onclick="return confirm('Apakah Anda yakin ingin mengajukan sertifikasi? Pastikan komposisi tim sudah benar.')">
      Ajukan Sertifikasi
    </button>
    <?= Html::endForm() ?>
  </div>

  <?php if (CertificationStatus::PENDING_SELF_TEAM_FORMATION != $certification->status): ?>
    <hr class="text-dark opacity-10 my-3">

    <div>
      <?= Html::a(
        'Batalkan Sertifikasi',
        ['batalkan-pengajuan-sertifikasi'],
        [
          'class' => 'btn btn-danger me-2 w-100 mb-3',
          'disabled' => $certification->status == CertificationStatus::PENDING_SELF_TEAM_FORMATION,
          'data' => [
            'method' => 'delete',
            'confirm' => 'Apakah Anda yakin ingin membatalkan sertifikasi?',
          ],
        ]
      ) ?>
    </div>
  <?php endif ?>
</div>

<script>
  // code from be
  document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('user-search-input');
    const dropdown = document.getElementById('search-dropdown');
    const chipsContainer = document.getElementById('selected-users-container');
    const hiddenInputsContainer = document.getElementById('selected-user-inputs');
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
                item.textContent = `${user.username} - ${user.full_name}`;
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
          <span>${user.username} - ${user.full_name}</span>
          <span class="remove-btn ms-1" onclick="window.removeUserFromList(${user.id})">&times;</span>
        </div>
      `;
        chipsContainer.appendChild(chip);
      });
      submitBtn.style.display = selectedUsers.length > 0 ? 'block' : 'none';
    }

    function updateHiddenInput() {
      hiddenInputsContainer.innerHTML = '';
      selectedUsers.forEach(user => {
        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = 'user_ids[]';
        input.value = user.id;

        hiddenInputsContainer.appendChild(input);
      });
    }

    window.removeUserFromList = removeUser;

    document.addEventListener('click', function(e) {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });
  });
</script>