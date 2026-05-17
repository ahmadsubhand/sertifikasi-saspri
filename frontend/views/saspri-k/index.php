<?php

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationStatus;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\SaspriK $saspri_k 
 * @var \common\models\Certification $valid_certificate
 * @var \common\models\Certification[] $completed_certifications
 * @var \common\models\User[] $saspri_k_members */



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
  'created_at',
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

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <div class="">
    <h3 class="fw-bold">SASPRI Kawasan</h3>
  </div>
  <div class="row">
    <div class="col-sm-8">
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
        <div class=" px-4">
          <p class=" fw-bold">Identitas </p>
          <?php foreach ($index as $key => $dat) : ?>
            <?php echo $this->render('/component/_idline', [
              'label' => $label[$key],
              'data' => $saspri_k[$dat],
              'shingles' => $shingles[$dat] ?? ''
            ]); ?>
          <?php endforeach ?>
          <div class="w-100 d-flex justify-content-between mt-3">
            <a href="<?= Url::to(['#', 'saspri_id' => $saspri_k['id']]) ?>" class=" btn s-btn-red me-2">Ajukan pergantian Wali</a>
            <!-- possible pjax change only for top-->
            <a href="<?= Url::to(['#']) ?>" class=" btn s-btn-main me-2">Edit data</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <a href="/saspri-k/pengajuan-sertifikasi" class=" btn s-btn-main me-2 w-100 mb-3">+ Pengajuan Sertifikasi</a>
      <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
        <div class="px-3">
          <p class=" fw-bold">Sertifikat </p>

          <?php foreach ($certIndex as $key => $dat) : ?>
            <?php echo $this->render('/component/_idline', [
              'label' => $certLabel[$key],
              'data' => $valid_certificate[$dat],
              'shingles' => ''
            ]); ?>
          <?php endforeach ?>
        </div>
        <a href="<?= Url::to(['#', 'certificate_id' => $valid_certificate['id']]) ?>" class=" btn s-btn-main me-2 w-100 mt-3">Unduh Sertifikat <i class="fa-solid fa-download"></i></a>
      </div>
    </div>
  </div>
  <div>
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
      <div class="px-4">
        <p class=" fw-bold">Riwayat Sertifikasi </p>

        <table class="table text-center">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Tingkatan</th>
              <th scope="col">Tanggal Pengajuan</th>
              <th scope="col">Tanggal Penerbitan</th>
              <th scope="col">Status</th>
              <th scope="col">Predikat</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($completed_certifications as $key => $value) : ?>
              <tr>
                <td scope="row"><?php echo (int)$key + 1 ?></th>
                <td><?= Html::encode(CertificateLevel::list()[$value->level]) ?></td>
                <td><?= Html::encode($value->created_at ? date('d-m-Y', $value->created_at)
                      : '-')  ?></td>
                <td><?= Html::encode($value->issued_at ? date('d-m-Y', strtotime($value->issued_at))
                      : '-')  ?></td>
                <td><?= Html::encode(CertificationStatus::list()[$value->status]) ?></td>
                <td><?= Html::encode(CertificateGrade::list()[$value->grade] ?? '-') ?></td>
                <td>
                  <div>
                    <a href="<?php echo Url::to(['/saspri-k/detail', 'case_id' => $value->id]) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-magnifying-glass"></i></a>

                    <?php if (str_contains(strtolower($value->status), 'comp')): ?>
                      <a href="<?php echo Url::to(['#', 'id' => $value->id]) ?>" class="s-btn-main btn btn-sm"><i class="fa-solid fa-download"></i></a>
                    <?php endif ?>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
            <?php if (empty($completed_certifications)): ?>
              <tr>
                <td colspan="5" class="text-center">Belum ada Riwayat Sertifikasi.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div>
    <div class="bg-white px-2 py-4 rounded-2 shadow border-1 border">
      <div class="px-4">
        <p class=" fw-bold">Anggota Kawasan</p>
        <div class="mb-4">
          <div class="user-search-container">
            <input type="text" id="user-search-input" placeholder="Cari anggota baru (username) ..."
              class="form-control dropdown-toggle border border-1 shadow-sm" autocomplete="off">
            <div id="search-dropdown" class="search-dropdown dropdown-menu shadow"></div>
          </div>

          <div id="selected-users-container" class="user-chips my-3 d-flex flex-wrap"></div>

          <form id="add-members-form" method="post"
            action="<?= Url::to(['saspri-k/tambah-anggota']) ?>">
            <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
            <input type="hidden" name="user_ids" id="selected-user-ids">
            <button type="submit" id="submit-add-btn" class="btn btn-success mt-2" style="display: none;">Tambah
              Anggota</button>
          </form>
        </div>

        <table class="table text-center">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Nama Anggota</th>
              <th scope="col">Nomor Telpon</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($saspri_k_members as $key => $member) : ?>
              <tr>
                <td scope="row"><?php echo (int)$key + 1 ?></th>
                <td><?= Html::encode($member->username) ?></td>
                <!-- kok nomer gk keluar slur? -->
                <td><?= Html::encode($member->phone_number) ?></td>
                <td>
                  <div>
                    <?= Html::a('<i class="fa-solid fa-minus"></i>', ['hapus-anggota', 'user_id' => $member->id], [
                      'class' => 's-btn-red btn btn-sm',
                      'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menghapus anggota ini?',
                        'method' => 'delete',
                      ],
                    ]) ?>
                    <?= Html::a('<i class="fa-solid fa-magnifying-glass"></i>', ['#', 'user_id' => $member->id], [
                      'class' => 's-btn-main btn btn-sm',
                    ]) ?>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
            <?php if (empty($saspri_k_members)): ?>
              <tr>
                <td colspan="5" class="text-center">Belum ada anggota SASPRI-K.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
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
        fetch('<?= Url::to(['saspri-k/cari-user']) ?>?q=' +
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