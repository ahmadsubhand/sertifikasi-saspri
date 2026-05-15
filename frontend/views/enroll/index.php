<?php

use yii\bootstrap5\ActiveForm;

/** @var array $model */

?>

<div class="page-cont w-100 h-100 p-3 d-flex flex-column gap-3">
  <div class="">
    <h3 class="fw-bold">Daftar Sebagai Wali SASPRI</h3>
  </div>
  <?php $form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data']
  ]) ?>

  <div class="row">
    <div class="col-sm-6">
      <div class="mb-3">
        <label for="saspriName" class="form-label">Nama SASPRI-K</label>
        <input type="text" class="form-control border-black" id="saspriName" name="saspriName" aria-describedby="saspriName">
      </div>
      <div class="mb-3">
        <label for="secrAddress" class="form-label">Alamat Sekretariat</label>
        <input type="text" class="form-control border-black" id="secrAddress" name="secrAddress" aria-describedby="secrAddress">
      </div>
      <!-- debounce stuff here -->
      <div class="mb-3">
        <label for="subdistrict" class="form-label">Kecamatan</label>
        <input type="text" class="form-control border-black" id="subdistrict" name="subdistrict" aria-describedby="subdistrict">
      </div>
      <div class="mb-3">
        <label for="province" class="form-label">Provinsi</label>
        <input type="text" class="form-control border-black" id="province" name="province" aria-describedby="province">
      </div>
      <!--  -->
      <div class="mb-3">
        <label for="managedGroup" class="form-label">Jumlah Kelompok Yang Dibina</label>
        <input type="number" class="form-control border-black" id="managedGroup" name="managedGroup" aria-describedby="managedGroup">
      </div>
      <div class="mb-3">
        <label for="farmType" class="form-label">Ternak Yang Diusahakan</label>
        <input type="text" class="form-control border-black" id="farmType" name="farmType" aria-describedby="farmType">
      </div>
      <div class="mb-3">
        <label for="breedStock" class="form-label">Jumlah Ternak Indukan (Pernah Beranak)</label>
        <input type="number" class="form-control border-black" id="breedStock" name="breedStock" aria-describedby="breedStock">
      </div>
    </div>
    <div class="col-sm-6">
      <div class="mb-3">
        <label for="SPRCert" class="form-label">Unggah Sertifikat SPR</label>
        <input class="form-control border-black" type="file" id="SPRCert" name="SPRCert" aria-describedby="SPRCert">
      </div>
      <div class="mb-3">
        <label for="coopName" class="form-label">Nama Koperasi</label>
        <input type="text" class="form-control border-black" id="coopName" name="coopName" aria-describedby="coopName">
      </div>
      <div class="mb-3">
        <label for="city" class="form-label">Kabutapen/Kota</label>
        <input type="text" class="form-control border-black" id="city" name="city" aria-describedby="city">
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Nomor Telpon</label>
        <input type="tel" class="form-control border-black" id="phone" name="phone" aria-describedby="phone">
      </div>
      <div class="mb-3">
        <label for="memberCount" class="form-label">Jumlah Anggota Aktif</label>
        <input type="number" class="form-control border-black" id="memberCount" name="memberCount" aria-describedby="memberCount">
      </div>
      <div class="mb-3">
        <label for="cattleCount" class="form-label">Jumlah Total Ternak Anggota Aktif</label>
        <input type="number" class="form-control border-black" id="cattleCount" name="cattleCount" aria-describedby="cattleCount">
      </div>
      <div class="mb-3">
        <label for="bCount" class="form-label">Jumlah Total Ternak dara Produktif (Siap Kawin)</label>
        <input type="number" class="form-control border-black" id="bCount" name="bCount" aria-describedby="bCount">
      </div>
    </div>
    <!--  -->
    <div>
      <div class="d-flex justify-content-between mt-4">
        <p class="fw-bold">Dokumen Pendukung</p>
        <button type="button" id="add-row" class="btn btn-sm s-btn-main text-white me-2" style="background-color: #6B78B9;">
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>
      <div id="doc-row">
        <div class="f-row row">
          <div class="col-sm-6">
            <div class="mb-3">
              <label for="docType" class="form-label">Kategori</label>
              <input type="text" class="form-control border-black" id="docType" name="docType" aria-describedby="docType">
            </div>
          </div>
          <div class="col-sm-6 d-flex justify-content-between">
            <div class="mb-3" style="width: 90%;">
              <label for="docAdd" class="form-label">Unggah Dokumen Pendukung</label>
              <input class="form-control border-black" type="file" id="docAdd" name="docAdd" aria-describedby="docAdd">
            </div>
            <div class="d-flex align-items-center">
              <button type="button" class="rem-row btn btn-sm s-btn-main text-white me-2 p-2 mt-3" style="background-color: #6B78B9;">
                <i class="fa-solid fa-minus"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="w-100 my-3">
      <a href="#" class="btn s-btn-main w-100">Daftar</a>
    </div>
  </div>

  <?php ActiveForm::end() ?>
</div>