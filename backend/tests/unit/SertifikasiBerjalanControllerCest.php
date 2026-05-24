<?php

declare(strict_types=1);

namespace backend\tests\Unit;

use backend\controllers\SertifikasiBerjalanController;
use backend\tests\UnitTester;
use common\enums\CertificationStatus;
use common\models\Assessment;
use common\models\Certification;
use common\models\District;
use common\models\Province;
use common\models\Regency;
use common\models\SaspriK;
use common\models\User;
use Yii;

final class SertifikasiBerjalanControllerCest
{
    /** @var SertifikasiBerjalanController */
    private $controller;

    public function _before(UnitTester $I): void
    {
        $this->controller = new SertifikasiBerjalanController('sertifikasi-berjalan', Yii::$app);
        Yii::$app->controller = $this->controller;
        
        // Find or create admin user for AccessControl
        $admin = User::findOne(['username' => 'admin.nasional']) ?? new User([
            'username' => 'admin.nasional',
            'email' => 'admin@test.id',
            'status' => 10,
        ]);
        
        if ($admin->isNewRecord) {
            $admin->setPassword('admin123');
            $admin->generateAuthKey();
            $admin->save(false);
        }
        
        Yii::$app->user->login($admin);
    }

    public function testIndexLogic(UnitTester $I): void
    {
        // 1. Prepare geographical data
        $provId = $I->haveRecord(Province::class, ['name' => 'Provinsi Test Unit', 'code' => 'PTU']);
        $regId = $I->haveRecord(Regency::class, ['province_id' => $provId, 'name' => 'Kabupaten Test Unit', 'code' => 'KTU']);
        $distId = $I->haveRecord(District::class, ['regency_id' => $regId, 'name' => 'Kecamatan Test Unit', 'code' => 'KCTU']);
        
        $assessmentId = $I->haveRecord(Assessment::class, [
            'title' => 'Assessment Test Unit',
            'level' => 'weania',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $saspriId = $I->haveRecord(SaspriK::class, [
            'coordinator_id' => 1,
            'district_id' => $distId,
            'region_name' => 'Region Test Unit',
            'address' => 'Address Test Unit',
            'cooperative_name' => 'Koperasi Test Unit',
            'number_of_groups' => 1,
            'number_of_active_members' => 10,
            'livestock_type' => 'Sapi',
            'total_livestock_count' => 10,
            'breeding_livestock_count' => 5,
            'productive_heifer_count' => 5,
            'request_status' => 'approved',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Create a running certification
        $I->haveRecord(Certification::class, [
            'saspri_k_id' => $saspriId,
            'assessment_id' => $assessmentId,
            'purpose' => 'level_up',
            'status' => CertificationStatus::SELF_REVIEW,
            'level' => 'weania',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Create an excluded certification (Completed)
        $I->haveRecord(Certification::class, [
            'saspri_k_id' => $saspriId,
            'assessment_id' => $assessmentId,
            'purpose' => 'level_up',
            'status' => CertificationStatus::COMPLETED,
            'level' => 'weania',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // 2. Call actionIndex and assert on rendered HTML
        $html = $this->controller->actionIndex();
        $I->assertIsString($html);
        $I->assertStringContainsString('Region Test Unit', $html);
        $I->assertStringContainsString(CertificationStatus::list()[CertificationStatus::SELF_REVIEW], $html);

        // 3. Test filtering by province
        $htmlProv = $this->controller->actionIndex($provId);
        $I->assertStringContainsString('Region Test Unit', $htmlProv);

        $htmlNoProv = $this->controller->actionIndex($provId + 999); 
        $I->assertStringNotContainsString('Region Test Unit', $htmlNoProv);

        // 4. Test filtering by regency
        $htmlReg = $this->controller->actionIndex(null, $regId);
        $I->assertStringContainsString('Region Test Unit', $htmlReg);

        // 5. Test filtering by district
        $htmlDist = $this->controller->actionIndex(null, null, $distId);
        $I->assertStringContainsString('Region Test Unit', $htmlDist);

        // 6. Test pagination (limit)
        $htmlLimit = $this->controller->actionIndex(null, null, null, 1);
        // Should only show 1 row. We can check if it contains the first one 
        // (usually the one we just created since it's ORDER BY updated_at DESC)
        $I->assertStringContainsString('Region Test Unit', $htmlLimit);
    }
}
