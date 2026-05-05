<?php
/** @var \common\models\SaspriK $saspri_k */
?>
<div class="d-flex flex-column align-items-start gap-3">
  <h1>SASPRI-Kawasan</h1>
  
  <button class="btn btn-primary">Pengajuan Sertifikasi</button>
  
  <div class="d-flex flex-row w-100 justify-content-between align-items-start">
    <div class="card p-3 w-50 d-flex flex-column gap-2">
      <h2>Identitas</h2>
      
      <div class="d-flex flex-column">
        <div class="d-flex flex-row gap-2">
          <div>SASPRI-K </div>
          <div>:</div>
          <div><?= $saspri_k->district->name ?></div>
        </div>
        <div class="d-flex flex-row gap-2">
          <div>Nama unit usaha (koperasi)</div>
          <div>:</div>
          <div><?= $saspri_k->cooperative_name ?></div>
        </div>
        <div class="d-flex flex-row gap-2">
          <div>Jumlah anggota aktif dalam kelompok yang dibina</div>
          <div>:</div>
          <div><?= $saspri_k->number_of_active_members ?> orang</div>
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
          <div><?=  $saspri_k->validCertificate->level ?></div>
        </div>
        <div class="d-flex flex-row gap-2">
          <div>Nomor Sertifikat</div>
          <div>:</div>
          <div><?=  $saspri_k->validCertificate->code ?></div>
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
        <?php foreach ($saspri_k->certifications as $index => $certification): ?>
          <tr>
            <th scope="row"><?= $index + 1 ?></th>
            <td><?= $certification->code ?></td>
            <td><?= ucfirst($certification->level) ?></td>
            <td><?= date('Y-m-d', strtotime($certification->issued_at)) ?></td>
            <td>
              <button class="btn btn-primary">Unduh</button>
              <button class="btn btn-primary">Liat</button>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>

  <div class="card p-3 d-flex flex-column gap-2">
    <h2>Anggota Kawasan</h2>

    <input type="text" placeholder="Cari anggota ...">

    <table class="table align-middle">
      <thead>
        <tr>
          <th scope="col">No</th>
          <th scope="col">Nama Anggota</th>
          <!-- <th scope="col">Jabatan</th> -->
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($saspri_k->users as $index => $user): ?>
          <tr>
            <th scope="row"><?= $index + 1 ?></th>
            <td><?= $user->username ?></td>
            <!-- <td></td> -->
            <td>
              <button class="btn btn-primary">Hapus</button>
              <button class="btn btn-primary">Liat</button>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>