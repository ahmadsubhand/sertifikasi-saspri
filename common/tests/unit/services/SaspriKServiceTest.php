<?php

namespace common\tests\unit\services;

use common\enums\ApprovalStatus;
use common\enums\RequestResponse;
use common\models\form\AddMembersForm;
use common\models\form\ChangeLevelForm;
use common\models\form\CoordinatorChangeForm;
use common\models\form\ExternalReviewForm;
use common\models\form\RegisterSaspriKForm;
use common\models\form\RequestResponseForm;
use common\models\form\UpdateSaspriKForm;
use common\models\SaspriK;
use common\models\User;
use common\models\Certification;
use common\services\SaspriKService;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class SaspriKServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _after()
    {
        Yii::$app->user->logout();
        $_FILES = []; // Clean up mocked file superglobals
    }

    /**
     * Helper untuk memalsukan proses upload file.
     */
    protected function mockUploadedFiles(string $name)
    {
        $_FILES[$name] = [
            'name' => ['dokumen_test.pdf'],
            'type' => ['application/pdf'],
            'tmp_name' => [Yii::getAlias('@common/tests/_data/test.pdf')],
            'error' => [0],
            'size' => [1024],
        ];
    }

    // ==========================================
    // 1. FIND OR FAIL & FIND MEMBER
    // ==========================================

    public function testFindOrFailSuccess()
    {
        $saspri = SaspriKService::findOrFail(1);
        $this->assertNotNull($saspri);
        $this->assertEquals(1, $saspri->id);
    }

    public function testFindOrFailNotFound()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('SASPRI-K tidak ditemukan');
        SaspriKService::findOrFail(999999);
    }

    public function testFindMemberSuccess()
    {
        // user 15 tergabung dalam saspri k 1 berdasarkan seeder
        $userId = 15;
        $saspriKId = 1;

        $response = SaspriKService::findMember($userId, $saspriKId);
        $this->assertEquals($userId, $response->id);
        $this->assertEquals($saspriKId, $response->saspri_k_id);
    }

    public function testFindMemberNotFound()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Anggota tidak ditemukan dalam SASPRI-K');
        SaspriKService::findMember(4, 1); // User 4 adalah Independent, bukan anggota SASPRI-K 1
    }

    // ==========================================
    // 2. ADD & REMOVE MEMBERS
    // ==========================================

    public function testAddMembersSuccess()
    {
        Yii::$app->user->login(User::findOne(14)); // Login sebagai Wali SASPRI-K 1

        $form = new AddMembersForm();
        $form->user_ids = [12, 13]; // User Independent

        $result = SaspriKService::addMembers($form);
        $this->assertCount(2, $result);
        $user12 = User::findOne(12); 
        $this->assertEquals(1, $user12->saspri_k_id);
        $user13 = User::findOne(13);
        $this->assertEquals(1, $user13->saspri_k_id);
    }

    public function testAddMembersInvalidUsers()
    {
        Yii::$app->user->login(User::findOne(14));

        $form = new AddMembersForm();
        $form->user_ids = [15]; // User 15 sudah terdaftar

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Beberapa anggota tidak valid atau sudah terdaftar di SASPRI-K lain');
        SaspriKService::addMembers($form);
    }

    public function testRemoveMemberSuccess()
    {
        Yii::$app->user->login(User::findOne(14));
        
        $result = SaspriKService::removeMember(15);
        $this->assertEquals(15, $result['id']);

        $user15 = User::findOne(15);
        $this->assertNull($user15->saspri_k_id);
    }

    // ==========================================
    // 3. REGISTER SASPRI-K
    // ==========================================

    public function testRegisterNoDocuments()
    {
        Yii::$app->user->login(User::findOne(4)); 
        
        $form = new RegisterSaspriKForm();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Wajib menyertakan dokumen pendukung minimal sertifikasi tingkat Natalia');
        SaspriKService::register($form);
    }

    public function testRegisterDocumentCountMismatch()
    {
        Yii::$app->user->login(User::findOne(4));
        $this->mockUploadedFiles('saspri_k_documents');
        
        $form = new RegisterSaspriKForm();
        $form->saspri_k_documents = ['type_1', 'type_2'];

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Tipe dokumen wajib disertakan lengkap');
        SaspriKService::register($form);
    }

    public function testRegisterAlreadyJoined()
    {
        Yii::$app->user->login(User::findOne(14)); // User 14 sudah memiliki SASPRI-K
        $this->mockUploadedFiles('saspri_k_documents');
        
        $form = new RegisterSaspriKForm();
        $form->saspri_k_documents = ['type_1'];

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Anda sudah tergabung dalam SASPRI-K');
        SaspriKService::register($form);
    }

    public function testRegisterPendingStatusConflict()
    {
        Yii::$app->user->login(User::findOne(4)); // User 4 memiliki pendaftaran SASPRI-K yang pending
        
        $this->mockUploadedFiles('saspri_k_documents');
        $form = new RegisterSaspriKForm(['saspri_k_documents' => ['type_1']]);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('SASPRI-Kawasan sudah pernah didaftarkan dan masih dalam proses tinjauan SASPRI-Nasional');
        SaspriKService::register($form);
    }

    public function testRegisterSuccess()
    {
        $userId = 9;
        Yii::$app->user->login(User::findOne($userId)); // User 9 tidak pernah mendaftar saspri k dan belum bergabung

        $this->mockUploadedFiles('saspri_k_documents');
        $form = new RegisterSaspriKForm(['saspri_k_documents' => ['type_1']]);
        $form->region_name = 'Test Region Kawasan';
        $form->address = 'Jl. Contoh Peternakan No. 45';
        $form->cooperative_name = 'Koperasi Ternak Jaya';
        $form->livestock_type = 'Sapi Perah';
        $form->district_id = 1;
        $form->number_of_groups = 5;
        $form->number_of_active_members = 50;
        $form->total_livestock_count = 100;
        $form->breeding_livestock_count = 40;
        $form->productive_heifer_count = 60;

        $result = SaspriKService::register($form);
        
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(ApprovalStatus::PENDING, $result['request_status']);
        $this->assertEquals($userId, $result['coordinator_id']);
    }

    // ==========================================
    // 4. DETAIL & CANCEL REGISTRATION
    // ==========================================

    public function testDetailRegistrationAlreadyJoined()
    {
        Yii::$app->user->login(User::findOne(14));
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Anda sudah tergabung dalam SASPRI-K');
        SaspriKService::detailRegistration();
    }

    public function testDetailRegistrationSuccess()
    {
        $user = User::findOne(4);
        Yii::$app->user->login($user);

        $saspriK = SaspriK::findOne(['coordinator_id' => $user->id]);

        $result = SaspriKService::detailRegistration();
        $this->assertNotNull($result['saspri_k']);
        $this->assertEquals($saspriK->id, $result['saspri_k']->id);
    }

    public function testCancelRegistrationSuccess()
    {
        $user = User::findOne(5);
        Yii::$app->user->login($user);

        $saspriK = SaspriK::findOne(['coordinator_id' => $user->id]);

        $result = SaspriKService::cancelRegistration();
        $this->assertEquals($saspriK->id, $result->id);
        $this->assertNull(SaspriK::findOne($saspriK->id)); // Memastikan telah terhapus
    }

    public function testCancelRegistrationNotRegistered()
    {
        Yii::$app->user->login(User::findOne(10)); // User 9 tidak pernah mendaftar saspri k dan belum bergabung
        
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Anda belum mendaftar sebagai Wali SASPRI-K');
        SaspriKService::cancelRegistration();
    }

    public function testCancelRegistrationApproved()
    {
        Yii::$app->user->login(User::findOne(14)); // Wali saspri k yang sudah terdaftar

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('SASPRI-K yang telah disetujui tidak bisa dibatalkan');
        SaspriKService::cancelRegistration();
    }

    // // ==========================================
    // // 5. REGISTRATION APPROVAL FLOW
    // // ==========================================

    // public function testCoordinatorRegistrationDetailSuccess()
    // {
    //     $saspriK = SaspriK::findOne(['coordinator_id' => $]);

    //     $result = SaspriKService::coordinatorRegistrationDetail($saspriK->id, 1);
    //     $this->assertEquals($saspriK->id, $result['saspri_k']->id);
    //     $this->assertArrayHasKey('certification', $result);
    // }

    // public function testChangeRegistrationLevel()
    // {
    //     $saspriK = new SaspriK(['request_status' => ApprovalStatus::PENDING]);
    //     $saspriK->save(false);

    //     $cert = new Certification(['saspri_k_id' => $saspriK->id]);
    //     $cert->save(false);

    //     $form = new ChangeLevelForm(['level' => 'natalia']);
    //     $result = SaspriKService::changeRegistrationLevel($saspriK->id, $form);
        
    //     $this->assertInstanceOf(Certification::class, $result);
    // }

    // public function testSaveRegistration()
    // {
    //     $saspriK = new SaspriK(['request_status' => ApprovalStatus::PENDING]);
    //     $saspriK->save(false);

    //     $cert = new Certification(['saspri_k_id' => $saspriK->id]);
    //     $cert->save(false);

    //     $form = new ExternalReviewForm(['indicator_scores' => []]);
    //     $result = SaspriKService::saveRegistration($saspriK->id, $form);
        
    //     $this->assertIsArray($result);
    // }

    // public function testRegistrationRequestResponseRejectMissingReason()
    // {
    //     $saspriK = new SaspriK(['request_status' => ApprovalStatus::PENDING]);
    //     $saspriK->save(false);

    //     $form = new RequestResponseForm(['action' => RequestResponse::REJECT, 'rejection_reason' => '']);
        
    //     $this->expectException(BadRequestHttpException::class);
    //     $this->expectExceptionMessage('Wajib menyertakan alasan penolakan');
    //     SaspriKService::registrationRequestResponse($saspriK->id, $form);
    // }

    // public function testRegistrationRequestResponseRejectSuccess()
    // {
    //     $saspriK = new SaspriK(['request_status' => ApprovalStatus::PENDING]);
    //     $saspriK->save(false);

    //     $form = new RequestResponseForm(['action' => RequestResponse::REJECT, 'rejection_reason' => 'Dokumen tidak valid']);
    //     $result = SaspriKService::registrationRequestResponse($saspriK->id, $form);
        
    //     $this->assertEquals(ApprovalStatus::REJECTED, $result->request_status);
    //     $this->assertEquals('Dokumen tidak valid', $result->rejection_reason);
    // }

    // public function testRegistrationRequestResponseInvalidAction()
    // {
    //     $saspriK = new SaspriK(['request_status' => ApprovalStatus::PENDING]);
    //     $saspriK->save(false);

    //     $form = new RequestResponseForm(['action' => 'INVALID_ACTION']);
        
    //     $this->expectException(BadRequestHttpException::class);
    //     $this->expectExceptionMessage('Wajib memilih antara setuju atau tolak');
    //     SaspriKService::registrationRequestResponse($saspriK->id, $form);
    // }

    // // ==========================================
    // // 6. COORDINATOR CHANGE
    // // ==========================================

    // public function testChangeCoordinatorPendingConflict()
    // {
    //     Yii::$app->user->login(User::findOne(134)); // Wali SaspriK 7 
        
    //     // Di Seeder file, SaspriK 7 memiliki change_status = 'pending'
    //     $form = new CoordinatorChangeForm(['new_coordinator_id' => 10, 'change_request_reason' => 'Uji Coba']);

    //     $this->expectException(UnprocessableEntityHttpException::class);
    //     $this->expectExceptionMessage('Pergantian wali sudah pernah diajukan dan masih dalam proses tinjauan SASPRI-Nasional');
    //     SaspriKService::changeCoordinator($form);
    // }

    // public function testChangeCoordinatorSuccess()
    // {
    //     Yii::$app->user->login(User::findOne(14)); // Wali SaspriK 1 (tidak sedang dalam pending)
        
    //     $form = new CoordinatorChangeForm(['new_coordinator_id' => 15, 'change_request_reason' => 'Pensiun']);
    //     $result = SaspriKService::changeCoordinator($form);
        
    //     $this->assertEquals(ApprovalStatus::PENDING, $result['change_status']);
    //     $this->assertEquals(15, $result['new_coordinator_id']);
    // }

    // public function testCancelCoordinatorChangeNotFound()
    // {
    //     Yii::$app->user->login(User::findOne(34)); // Wali SaspriK 2 yang tidak memiliki pending change
        
    //     $this->expectException(NotFoundHttpException::class);
    //     $this->expectExceptionMessage('Tidak ditemukan permintaan pergantian Wali SASPRI-K');
    //     SaspriKService::cancelCoordinatorChange();
    // }

    // public function testCancelCoordinatorChangeSuccess()
    // {
    //     Yii::$app->user->login(User::findOne(134)); // Wali SaspriK 7, memiliki pending change
        
    //     $result = SaspriKService::cancelCoordinatorChange();
    //     $this->assertNull($result->change_status);
    //     $this->assertNull($result->new_coordinator_id);
    // }

    // public function testCoordinatorChangeDetailSuccess()
    // {
    //     // SaspriK 7 memiliki pending change_status
    //     $result = SaspriKService::coordinatorChangeDetail(7);
    //     $this->assertEquals(7, $result['saspri_k']->id);
    //     $this->assertNotNull($result['coordinator']);
    //     $this->assertNotNull($result['new_coordinator']);
    // }

    // public function testCoordinatorChangeResponseApprove()
    // {
    //     // SaspriK 7
    //     $form = new RequestResponseForm(['action' => RequestResponse::APPROVE]);
        
    //     $result = SaspriKService::coordinatorChangeResponse(7, $form);
    //     $this->assertEquals(ApprovalStatus::APPROVED, $result->change_status);
    //     $this->assertEquals(9, $result->coordinator_id); // new_coordinator_id di SaspriK 7 adalah 9
    // }

    // public function testCoordinatorChangeResponseRejectMissingReason()
    // {
    //     $form = new RequestResponseForm(['action' => RequestResponse::REJECT, 'rejection_reason' => '']);
        
    //     $this->expectException(BadRequestHttpException::class);
    //     $this->expectExceptionMessage('Wajib menyertakan alasan penolakan');
    //     SaspriKService::coordinatorChangeResponse(7, $form);
    // }

    // public function testCoordinatorChangeResponseRejectSuccess()
    // {
    //     $form = new RequestResponseForm(['action' => RequestResponse::REJECT, 'rejection_reason' => 'Alasan tidak kuat']);
        
    //     $result = SaspriKService::coordinatorChangeResponse(7, $form);
    //     $this->assertEquals(ApprovalStatus::REJECTED, $result->change_status);
    // }

    // public function testCoordinatorChangeResponseInvalid()
    // {
    //     $form = new RequestResponseForm(['action' => 'AKSI_PALSU']);
        
    //     $this->expectException(BadRequestHttpException::class);
    //     $this->expectExceptionMessage('Wajib memilih antara setuju atau tolak');
    //     SaspriKService::coordinatorChangeResponse(7, $form);
    // }

    // // ==========================================
    // // 7. UPDATE SASPRI-K
    // // ==========================================

    // public function testUpdateSuccess()
    // {
    //     Yii::$app->user->login(User::findOne(14)); // Wali SaspriK 1

    //     $form = new UpdateSaspriKForm([
    //         'region_name' => 'Nama Wilayah Baru'
    //     ]);

    //     $result = SaspriKService::update($form);
    //     $this->assertEquals('Nama Wilayah Baru', $result->region_name);
    // }
}