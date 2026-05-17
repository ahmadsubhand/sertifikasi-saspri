<?php

/** @var \common\models\Certification $certification */
/** @var \common\models\Certification $valid_certificate */
/** @var \common\models\SaspriK $saspri_k */
/** @var \common\models\District $district */
/** @var \common\models\PeerTeamMember[] $peer_team_members */

use common\enums\ApprovalStatus;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\UserRole;
use yii\helpers\Html;

$this->title = 'Pembentukan Tim Sebaya';

$statToProc = [
  'weania' => 'Weania ke Natalia',
  'natalia' => 'Natalia ke Prematura',
  'prematura' => 'Prematura ke Matura',
  'matura' => 'Matura'
];
$label = [
  'SASPRI-K',
  'SASPRI-KK',
  'SASPRI-P',
  'Alamat Sekretariat',
  'Nama unit usaha (koperasi)',
  'Nama wali SASPRI',
  'Jumlah kelompok yang dibina',
  'Jumlah anggota aktif dalam kelompok yang dibina',
  'Ternak yang diusahakan',
  'Jumlah total ternak milik anggota aktif',
  'Jumlah ternak indukan (pernah beranak)',
  'Jumlah ternak dara produktif (siap dikawinkan)',
];
$index = [
  'district_id',
  'district_id',
  'district_id',
  'address',
  'cooperative_name',
  'coordinator_id',
  'number_of_groups',
  'number_of_active_members',
  'livestock_type',
  'total_livestock_count',
  'breeding_livestock_count',
  'productive_heifer_count',

];
$certLabel = [
  'Level Sertifikat',
  'No. Sertifikat',
  'Tanggal Pengajuan',
  'Tanggal Penerbitan',
  'Predikat',
];
$certIndex = [
  'level',
  'code',
  'submitted_at',
  'issued_at',
  'grade',
];
$shingles = [
  'number_of_active_members' => 'Orang',
  'total_livestock_count' => 'Ekor',
  'breeding_livestock_count' => 'Ekor',
  'productive_heifer_count' => 'Ekor',
];
?>

</style>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3 w-100">
  <h1><?= Html::encode($this->title) ?></h1>

  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="px-4">
      <p class="h6">Informasi SASPRI-K</p>
      <?php foreach ($index as $key => $dat) : ?>
        <?php echo $this->render('/component/_idline', [
          'label' => $label[$key],
          'data' => $saspri_k[$dat],
          'shingles' => $shingles[$dat] ?? ''
        ]); ?>
      <?php endforeach ?>
      <br>
      <p class="h6">Tingkat Sertifikasi Terakhir</p>
      <?php foreach ($certIndex as $key => $dat) : ?>
        <?php echo $this->render('/component/_idline', [
          'label' => $certLabel[$key],
          'data' => $valid_certificate[$dat] ?? '-',
          'shingles' => ''
        ]); ?>
      <?php endforeach ?>
    </div>
  </div>
  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="px-4">
      <h2 class="h4">Kelola Anggota Tim Sebaya</h2>
      <p class="text-muted small">
        Syarat: Minimal 3 orang, 1 Facilitator (Admin), 1 Leader (Luar SASPRI-K), Anggota (Luar SASPRI-K).<br>
        Semua anggota luar SASPRI-K harus berasal dari SASPRI-K yang berbeda satu sama lain.
      </p>
      <div class="d-flex flex-column gap-2">
        <p class="mb-0">Tambah Anggota:</p>
        <div class="user-search-container">
          <input type="text" id="user-search-input" placeholder="Cari admin atau anggota/wali SASPRI-K lain ..."
            class="form-control dropdown-toggle border border-1 shadow-sm" autocomplete="off">
          <div id="search-dropdown" class="search-dropdown dropdown-men shadow bg-white"></div>
        </div>

        <div id="selected-users-container" class="user-chips my-3 d-flex flex-wrap "></div>

        <form id="add-members-form" method="post"
          action="<?= \yii\helpers\Url::to(['penentuan-tim-sebaya/tambah-anggota-tim-sebaya', 'certification_id' => $certification->id]) ?>">
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
              <td scope="row"><?= $index + 1 ?></th>
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
                  <?= ApprovalStatus::list()[$member->status] ?? ucfirst($member->status) ?>
                </span>
              </td>
              <td>
                <?= Html::a(
                  '<i class="fa-solid fa-xmark"></i>',
                  [
                    'hapus-anggota-tim-sebaya',
                    'user_id' => $member->user_id,
                    'certification_id' => $certification->id,
                  ],
                  [
                    'class' => 'btn s-btn-red btn-sm',
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

      <div class="mt-3">
        <?= Html::beginForm(['ajukan-peer-review', 'certification_id' => $certification->id], 'post') ?>
        <button type="submit" class="btn s-btn-green me-2 w-100 mb-3"
          onclick="return confirm('Apakah Anda yakin ingin memproses ke tahap Peer Review? Pastikan komposisi tim sudah benar.')">
          Selesaikan Pembentukan Tim
        </button>
        <?= Html::endForm() ?>
      </div>
    </div>
  </div>
  <div class="p-2">
    <div class="dropdown-divider border border-1 border-black"></div>
  </div>
  <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
    <div class="px-4">
      <h2 class="h4">Penolakan Sertifikasi</h2>
      <p class="text-muted small">
        Tolak sertifikasi jika ada kekurangan dari SASPRI-K
      </p>
      <?= Html::beginForm() ?>
      <label for="deny-reason" class="mb-0">Alasan:</label>
      <input type="text" id="deny-reason" placeholder="Tulisa alasan penolakan sertifikasi"
        class="form-control border border-1 shadow-sm" autocomplete="off">
      <button type="submit" class="btn s-btn-red me-2 w-100 mb-3 my-5"
        onclick="return confirm('Apakah Anda yakin ingin menolak sertifikasi SASPRI-K?')">
        Tolak Sertifikasi
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
        const url = '<?= \yii\helpers\Url::to(['penentuan-tim-sebaya/cari-anggota-tim-sebaya', 'certification_id' => $certification->id]) ?>' + '&q=' + encodeURIComponent(q);
        fetch(url)
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