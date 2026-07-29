<?php

namespace frontend\controllers;

use common\enums\UserRole;
use common\models\form\CattleCalculatorForm;
use common\models\form\CattleSimulationForm;
use common\models\History;
use common\models\HistoryChangeLog;
use common\models\Livestock;
use common\models\PriceList;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

class HargaJualController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::COORDINATOR, UserRole::USER],
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST', 'DELETE'],
                    'simulation' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Anda harus login untuk membuka page ini.');
        }

        $model = new CattleCalculatorForm();
        $results = null;
        $userId  = Yii::$app->user->id;

        if ($model->biayaTambahan === null) {
            $model->biayaTambahan = 0;
        }

        $priceList = PriceList::find()
            ->where(['user_id' => $userId])
            ->one();

        $livestockList = Livestock::find()
            ->with(['notes', 'cage'])
            ->where(['user_id' => $userId])
            ->andWhere(['purpose' => ['Penggemukan', 'Indukan']])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $dropdownData = [];
        $statsMap = [];

        foreach ($livestockList as $livestock) {
            $businessTypeForLivestock = $this->resolveBusinessType($livestock);
            if ($businessTypeForLivestock === null) {
                continue;
            }

            $statsMap[$livestock->id] = $this->buildLivestockStats($livestock, $priceList, $businessTypeForLivestock);
            $dropdownData[$livestock->id] = $livestock->name;
        }

        if ($model->businessType === null) {
            $model->businessType = 'penggemukan';
        }

        if ($model->marginKeuntungan === null && $priceList) {
            $model->marginKeuntungan = $priceList->margin;
        }

        if ($model->inflasi === null && $priceList) {
            $model->inflasi = $priceList->inflation;
        }

        $saveRequested = Yii::$app->request->post('save') === '1';

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->biayaTambahan === null) {
                $model->biayaTambahan = 0;
            }
            $selectedId = (int) $model->nama_sapi;
            $selectedLivestock = Livestock::find()
                ->with(['notes', 'cage'])
                ->where(['id' => $selectedId, 'user_id' => $userId])
                ->andWhere(['purpose' => ['Penggemukan', 'Indukan']])
                ->one();

            if ($selectedLivestock === null) {
                Yii::$app->session->setFlash('error', 'Data sapi tidak ditemukan.');
            } else {
                $model->businessType = $this->resolveBusinessType($selectedLivestock);

                if ($model->businessType === null) {
                    Yii::$app->session->setFlash('error', 'Jenis usaha sapi tidak tersedia untuk perhitungan.');
                } elseif ($model->validate()) {
                    $stats = $statsMap[$selectedLivestock->id] ?? $this->buildLivestockStats($selectedLivestock, $priceList, $model->businessType);
                    $results = $model->calculate($stats);

                    if ($saveRequested) {
                        if ($this->saveHistoryRecord($selectedLivestock, $model, $stats, $results)) {
                            Yii::$app->session->setFlash('success', 'Perhitungan berhasil disimpan ke history.');
                        } else {
                            Yii::$app->session->setFlash('error', 'Gagal menyimpan data ke history.');
                        }
                    }
                }
            }
        }

        return $this->render('index', [
            'model'        => $model,
            'results'      => $results,
            'dropdownData' => $dropdownData,
            'priceList'    => $priceList,
            'statsMap'     => $statsMap,
        ]);
    }

    public function actionData()
    {
        if (Yii::$app->user->isGuest) {
            throw new \yii\web\ForbiddenHttpException('Anda harus login untuk membuka page ini.');
        }
        return $this->render('data');
    }

    /**
     * Simulasi perhitungan harga jual (akses bebas, tanpa login, tidak menyimpan data).
     */
    public function actionSimulation()
    {
        $model = new CattleSimulationForm();
        $results = null;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $results = $model->calculate();
        }

        return $this->render('simulation', [
            'model' => $model,
            'results' => $results,
        ]);
    }

    // Endpoint AJAX untuk ambil info sapi berdasarkan nama
    public function actionGetLivestockInfo(string $name)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $livestock = Livestock::findOne(['name' => $name, 'user_id' => $userId]);

        if ($livestock) {
            return [
                'success' => true,
                'data' => [
                    'ras_sapi' => $livestock->breed_of_livestock,
                    'visual_id' => $livestock->vid,
                ],
            ];
        }

        return ['success' => false];
    }
    

    public function actionHistory()
    {
        if (Yii::$app->user->isGuest) {
            throw new \yii\web\ForbiddenHttpException('Anda harus login.');
        }

        $userId = Yii::$app->user->id;

        // 1) Fetch histories belonging to this user's livestock
        $histories = History::find()
            ->joinWith('livestock')
            ->where(['livestock.user_id' => $userId])
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC]) // tampilkan urutan sesuai waktu simpan
            ->all();

        // 2) Map into your desired array shape
        $calculationHistory = [];
        foreach ($histories as $h) {
            /* @var $h \app\models\History */
            /* @var $l \app\models\Livestock */
            $l = $h->livestock;
            $businessTypeLabel = $h->business_type ?: ($this->resolveBusinessType($l) ?? 'penggemukan');
            $healthTotal = (int)$h->insemination + (int)$h->vaccine + (int)$h->vitamin + (int)$h->pregnancy_check + (int)$h->antibiotics + (int)$h->anthelmintic;
            $feedTotal = (int)$h->forage_price + (int)$h->concentrate_price + (int)$h->additive_price;

            $calculationHistory[] = [
                'id'                    => $h->id,
                'nama_sapi'             => $l->name,
                'rasSapi'               => $l->breed_of_livestock,
                'visualId'              => $l->vid,
                'businessType'          => $businessTypeLabel,
                'tanggalPerhitungan'    => Yii::$app->formatter->asDate($h->date, 'php:Y-m-d'),
                'waktuPemeliharaan'    => "-",
                'hargaJual'             => (int)$h->sell_price,
                'totalHPP'              => (int)$h->hpp_price,
                'hargaPedet'            => (int)$h->pedet_price,
                'pakanHijauan'          => (int)$h->forage_price,
                'konsentrat'            => (int)$h->concentrate_price,
                'feedAdditive'          => (int)$h->additive_price,
                'insemination'          => (int)$h->insemination,
                'vaccine'               => (int)$h->vaccine,
                'vitamin'               => (int)$h->vitamin,
                'pemeriksaanKebuntingan'=> (int)$h->pregnancy_check,
                'antibiotics'           => (int)$h->antibiotics,
                'anthelmintic'          => (int)$h->anthelmintic,
                'investasiKandang'      => (int)$h->cage_price,
                'umurEkonomis'          => (int)$h->cage_productive_age,
                'gajiPekerja'           => (int)$h->workers_price,
                'jumlahSapi'            => (int)$h->workers_per_livestock,
                'marginKeuntungan'      => (int)$h->margin,
                'inflasi'               => (int)$h->inflation,
                'numberOfWorkers'       => (int)$h->number_of_workers,
                'biayaTambahan'         => (int)$h->additional_cost,
                'healthTotal'           => $healthTotal,
                'feedTotal'             => $feedTotal,
            ];
        }

        // 3) Render to your 'history' view
        return $this->render('history', [
            'calculationHistory' => $calculationHistory,
        ]);
    }

    public function actionHistoryLog($historyId = null)
    {
        if (Yii::$app->user->isGuest) {
            throw new \yii\web\ForbiddenHttpException('Anda harus login.');
        }

        if ($historyId === null) {
            $historyId = Yii::$app->request->get('id');
        }

        if ($historyId === null) {
            return $this->redirect(['history']);
        }

        $history = History::find()
            ->joinWith('livestock')
            ->where([
                History::tableName() . '.id' => $historyId,
                'livestock.user_id' => Yii::$app->user->id
            ])
            ->one();

        if ($history === null) {
            throw new \yii\web\NotFoundHttpException('History tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $logs = HistoryChangeLog::find()
            ->where(['history_id' => $historyId])
            ->orderBy(['changed_at' => SORT_DESC])
            ->all();

        return $this->render('log', [
            'logs' => $logs,
            'history' => $history,
        ]);
    }

    public function actionHistoryLogs()
    {
        if (Yii::$app->user->isGuest) {
            throw new \yii\web\ForbiddenHttpException('Anda harus login.');
        }

        $logs = HistoryChangeLog::find()
            ->joinWith(['history.livestock'])
            ->where(['livestock.user_id' => Yii::$app->user->id])
            ->orderBy(['changed_at' => SORT_DESC])
            ->all();

        return $this->render('logs', [
            'logs' => $logs,
        ]);
    }

    public function actionDelete(int $id)
    {
        if (Yii::$app->user->isGuest) {
            throw new \yii\web\ForbiddenHttpException('Anda harus login.');
        }

        $history = History::find()
            ->joinWith('livestock')
            ->where([History::tableName() . '.id' => $id, 'livestock.user_id' => Yii::$app->user->id])
            ->one();

        if ($history === null) {
            throw new \yii\web\NotFoundHttpException('History tidak ditemukan atau bukan milik Anda.');
        }

        // Catat log penghapusan sebelum menghapus
        HistoryChangeLog::logDeletion($history);

        $history->delete();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['success' => true];
    }

    // public function actionUpdate($id)
    // {
    //     if (Yii::$app->user->isGuest) {
    //         throw new \yii\web\ForbiddenHttpException('Anda harus login untuk membuka page ini');
    //     }

    //     $model = new CattleCalculatorForm();

    //     // Dummy histori data sama seperti di actionHistory
    //     $dummyData = [
    //         1 => [
    //             'nama_sapi' => "Brahman-001",
    //             'ras_sapi' => "Brahman",
    //             'visual_id' => "BR001",
    //             'businessType' => "penggemukan",
    //             'tanggalPerhitungan' => "2024-04-01",
    //             'hargaJual' => 27221700,
    //             'totalHPP' => 24090000,
    //             'hargaBakalan' => 7000000,
    //             'pakanHijauan' => 800000,
    //             'konsentrat' => 500000,
    //             'feedAdditive' => 300000,
    //             'waktuPemeliharaan' => 8,
    //             'obatCacing' => 200000,
    //             'vitamin' => 200000,
    //             'antibiotik' => 200000,
    //             'investasiKandang' => 9000000,
    //             'umurEkonomis' => 10,
    //             'gajiPekerja' => 3000000,
    //             'jumlahSapi' => 10,
    //             'marginKeuntungan' => 10,
    //             'inflasi' => 3,
    //         ],

    //         2 => [
    //             'nama_sapi' => "Brahman-001",
    //             'rasSapi' => "Brahman",
    //             'visualId' => "BR001",
    //             'businessType' => "penggemukan",
    //             'tanggalPerhitungan' => "2024-06-15",
    //             'waktuPemeliharaan' => 12,
    //             'hargaJual' => 34905700,
    //             'totalHPP' => 30890000.,
    //             'hargaBakalan' => 7000000,
    //             'pakanHijauan' => 800000,
    //             'konsentrat' => 500000,
    //             'feedAdditive' => 300000,
    //             'obatCacing' => 300000,
    //             'vitamin' => 400000,
    //             'antibiotik' => 300000,
    //             'investasiKandang' => 9000000,
    //             'umurEkonomis' => 10,
    //             'gajiPekerja' => 3000000,
    //             'jumlahSapi' => 10,
    //             'marginKeuntungan' => 10,
    //             'inflasi' => 3,
    //         ],

    //         // 🐂 LIMOUSIN-A2 - PENGGEMUKAN - PERHITUNGAN 1
    //         3 => [
    //             'nama_sapi' => "Limousin-A2",
    //             'rasSapi' => "Limousin",
    //             'visualId' => "LM002",
    //             'businessType' => "penggemukan",
    //             'tanggalPerhitungan' => "2024-05-01",
    //             'waktuPemeliharaan' => 10,
    //             'hargaJual' => 17000000,
    //             'totalHPP' => 14000000,
    //             'hargaBakalan' => 8000000,
    //             'pakanHijauan' => 2500000,
    //             'konsentrat' => 1800000,
    //             'feedAdditive' => 900000,
    //             'obatCacing' => 300000,
    //             'vitamin' => 350000,
    //             'antibiotik' => 250000,
    //             'investasiKandang' => 9500000,
    //             'umurEkonomis' => 9,
    //             'gajiPekerja' => 2800000,
    //             'jumlahSapi' => 4,
    //             'marginKeuntungan' => 11,
    //             'inflasi' => 3,
    //         ],
    //         // 🐂 LIMOUSIN-A2 - PENGGEMUKAN - PERHITUNGAN 2

    //         4 => [
    //             'nama_sapi' => "Limousin-A2",
    //             'rasSapi' => "Limousin",
    //             'visualId' => "LM002",
    //             'businessType' => "penggemukan",
    //             'tanggalPerhitungan' => "2024-07-10",
    //             'waktuPemeliharaan' => 14,
    //             'hargaJual' => 20000000,
    //             'totalHPP' => 16000000,
    //             'hargaBakalan' => 8000000,
    //             'pakanHijauan' => 4000000,
    //             'konsentrat' => 2500000,
    //             'feedAdditive' => 1200000,
    //             'obatCacing' => 400000,
    //             'vitamin' => 500000,
    //             'antibiotik' => 300000,
    //             'investasiKandang' => 9500000,
    //             'umurEkonomis' => 9,
    //             'gajiPekerja' => 2800000,
    //             'jumlahSapi' => 4,
    //             'marginKeuntungan' => 13,
    //             'inflasi' => 4,
    //         ],

    //         5 => [
    //             'nama_sapi' => "Simental-B1",
    //             'ras_sapi' => "Simental",
    //             'visual_id' => "SM003",
    //             'businessType' => "breeding",
    //             'tanggalPerhitungan' => "2024-04-20",
    //             'nilaiIndukan' => 12000000,
    //             'umurProduktif' => 6,
    //             'pakanIndukanHijauan' => 2000000,
    //             'pakanIndukanKonsentrat' => 2500000,
    //             'pakanIndukanfeedAdditive' => 1100000,
    //             'waktuPemeliharaanIndukan' => 15,
    //             'biayaIB' => 450000,
    //             'vaksin' => 350000,
    //             'vitaminBreeding' => 250000,
    //             'pemeriksaanKebuntingan' => 300000,
    //             'obatCacingBreeding' => 220000,
    //             'antibiotikBreeding' => 280000,
    //             'investasiKandang' => 8500000,
    //             'umurEkonomis' => 8,
    //             'gajiPekerja' => 2700000,
    //             'jumlahSapi' => 3,
    //             'marginKeuntungan' => 12,
    //             'inflasi' => 4,
    //         ],

    //         // 🐄 SIMENTAL-B1 - BREEDING - PERHITUNGAN 2
    //         6 => [
    //             'nama_sapi' => "Simental-B1",
    //             'rasSapi' => "Simental",
    //             'visualId' => "SM003",
    //             'businessType' => "breeding",
    //             'tanggalPerhitungan' => "2024-08-01",
    //             'waktuPemeliharaan' => 20,
    //             'hargaJual' => 23000000,
    //             'totalHPP' => 18500000,
    //             'nilaiIndukan' => 12000000,
    //             'umurProduktif' => 6,
    //             'pakanIndukanHijauan' => 3000000,
    //             'pakanIndukanKonsentrat' => 3500000,
    //             'pakanIndukanfeedAdditive' => 1500000,
    //             'waktuPemeliharaanIndukan' => 20,
    //             'biayaIB' => 500000,
    //             'vaksin' => 400000,
    //             'vitaminBreeding' => 300000,
    //             'pemeriksaanKebuntingan' => 400000,
    //             'obatCacingBreeding' => 250000,
    //             'antibiotikBreeding' => 300000,
    //             'investasiKandang' => 8500000,
    //             'umurEkonomis' => 8,
    //             'gajiPekerja' => 2700000,
    //             'jumlahSapi' => 3,
    //             'marginKeuntungan' => 14,
    //             'inflasi' => 5,
    //         ],
    //     ];

    //     if (!isset($dummyData[$id])) {
    //         throw new \yii\web\NotFoundHttpException("Data tidak ditemukan.");
    //     }

    //     // Load dummy data ke model
    //     $model->attributes = $dummyData[$id];

    //     // Ambil list sapi user (boleh dummy juga atau dari Livestock)
    //     $userId = Yii::$app->user->id;
    //     $livestockList = Livestock::find()->where(['user_id' => $userId])->all();

    //     $namaSapiList = [];
    //     foreach ($livestockList as $sapi) {
    //         $namaSapiList[] = [
    //             'nama_sapi' => $sapi->name,
    //             'ras_sapi' => $sapi->breed_of_livestock,
    //             'visual_id' => $sapi->vid,
    //         ];
    //     }

    //     // Hitung hasil dari data yang di-load
    //     $results = $model->calculate();

    //     return $this->render('index', [ // ✅ Gunakan view index yang sudah jadi
    //         'model' => $model,
    //         'results' => $results,
    //         'namaSapiList' => $namaSapiList,
    //     ]);
    // }


    private function resolveBusinessType(Livestock $livestock): ?string
    {
        $purpose = strtolower($livestock->purpose ?? '');

        if ($purpose === 'penggemukan') {
            return 'penggemukan';
        }

        if ($purpose === 'indukan') {
            return 'breeding';
        }

        return null;
    }

    private function buildLivestockStats(Livestock $livestock, ?PriceList $priceList, string $businessType): array
    {
        $notes = $livestock->notes;

        $forageTotal = 0.0;
        $concentrateTotal = 0.0;
        $additiveTotal = 0.0;

        $health = [
            'insemination'    => 0.0,
            'vaccine'         => 0.0,
            'vitamin'         => 0.0,
            'pregnancy_check' => 0.0,
            'antibiotics'     => 0.0,
            'anthelmintic'    => 0.0,
        ];

        $dates = [];

        foreach ($notes as $note) {
            $forageTotal      += (float) $note->forage_costs * (float) $note->forage_weight;
            $concentrateTotal += (float) $note->consentrate_costs * (float) $note->consentrate_weight;
            $additiveTotal    += (float) $note->additive_costs * (float) $note->additive_weight;

            $health['insemination']    += (float) $note->insemination;
            $health['vaccine']         += (float) $note->vaccine;
            $health['vitamin']         += (float) $note->vitamin;
            $health['pregnancy_check'] += (float) $note->pregnancy_check;
            $health['antibiotics']     += (float) $note->antibiotics;
            $health['anthelmintic']    += (float) $note->anthelmintic;

            if (!empty($note->note_date)) {
                $dates[] = (new \DateTime($note->note_date))->format('Y-m-d');
            }
        }

        $uniqueDays = count(array_unique($dates));
        if ($uniqueDays === 0) {
            $baseMonths = max((int) $livestock->age, 1);
            $maintenanceDays = $baseMonths * 30;
        } else {
            $maintenanceDays = max($uniqueDays, 1);
        }

        $maintenanceMonths = (int) max(1, ceil($maintenanceDays / 30));
        $maintenanceYears  = max($maintenanceDays / 365, $maintenanceMonths / 12, 1 / 12);

        $feedBreakdown = [
            'forage'      => $forageTotal,
            'concentrate' => $concentrateTotal,
            'additive'    => $additiveTotal,
        ];

        $totalFeedCost = array_sum($feedBreakdown);
        $perDayFeedCost = $maintenanceDays > 0 ? $totalFeedCost / $maintenanceDays : 0.0;
        $feedCost = $maintenanceDays * $perDayFeedCost;

        $healthTotal = array_sum($health);

        $cage = $livestock->cage;
        $cageInvestment = $cage ? (float) $cage->investasi_kandang : 0.0;
        $cageCapacity   = $cage && (int) $cage->capacity > 0 ? (int) $cage->capacity : 1;
        $cageEconomicAge = $cage ? (float) $cage->umur_ekonomis : 0.0;
        $cageCostPerHead = $cageInvestment / max(1, $cageCapacity);

        $wagePerWorker = $priceList ? (float) $priceList->wage : 0.0;
        $employeeCount = $priceList ? (int) $priceList->employee : 0;
        $livestockPerEmployee = $priceList ? max(1, (int) $priceList->livestock_per_employee) : 1;
        $totalWage = $wagePerWorker * max(1, $employeeCount);
        $labourCost = ($totalWage / ($livestockPerEmployee * max(1, $employeeCount))) * $maintenanceYears;

        $landYearly             = $priceList ? (float) $priceList->land : 0.0;
        $electricityWaterYearly = $priceList ? (float) $priceList->electricity_water : 0.0;
        $landCost               = $landYearly * $maintenanceYears;
        $electricityCost        = $electricityWaterYearly * $maintenanceYears;

        $firstPrice           = (float) $livestock->first_price;
        $breedingInvestment   = (float) $livestock->breeding_investment;

        $commonComponents = [
            'harga_pakan'        => $feedCost,
            'harga_kandang'      => $cageCostPerHead,
            'harga_kesehatan'    => $healthTotal,
            'harga_tenaga_kerja' => $labourCost,
            'harga_listrik'      => $electricityCost,
            'harga_lahan'        => $landCost,
            'feed_breakdown'     => $feedBreakdown,
            'health_breakdown'   => $health,
        ];

        $currentComponents = $commonComponents;

        if ($businessType === 'breeding') {
            $currentComponents['harga_pedet'] = $breedingInvestment;
        } else {
            $currentComponents['harga_pedet'] = $firstPrice;
        }

        return [
            'id'            => $livestock->id,
            'name'          => $livestock->name,
            'breed'         => $livestock->breed_of_livestock,
            'vid'           => $livestock->vid,
            'businessType'  => $businessType,
            'base'          => [
                $businessType => $currentComponents,
                'shared'      => [
                    'maintenance_days'   => $maintenanceDays,
                    'maintenance_months' => $maintenanceMonths,
                    'maintenance_years'  => $maintenanceYears,
                    'cage_economic_age'  => $cageEconomicAge,
                    'land_yearly'        => $landYearly,
                    'electricity_yearly' => $electricityWaterYearly,
                ],
            ],
            'labour'        => [
                'wage_total'             => $totalWage,
                'livestock_per_employee' => $livestockPerEmployee,
                'employee_count'         => $employeeCount,
            ],
            'cage'          => [
                'investment'    => $cageInvestment,
                'capacity'      => $cageCapacity,
                'cost_per_head' => $cageCostPerHead,
            ],
        ];
    }

    private function saveHistoryRecord(Livestock $livestock, CattleCalculatorForm $form, array $stats, array $results): bool
    {
        if (empty($results)) {
            return false;
        }

        $businessType = $stats['businessType'] ?? $form->businessType;

        $history = new History();
        $history->date = date('Y-m-d H:i:s');
        $history->livestock_id = $livestock->id;
        $history->sell_price = (int) round($results['hargaJual'] ?? 0);
        $history->hpp_price = (int) round($results['totalHPP'] ?? 0);
        $history->business_type = $businessType ?: 'penggemukan';

        $baseData = ($businessType && isset($stats['base'][$businessType])) ? $stats['base'][$businessType] : [];

        $feedBreakdown   = $baseData['feed_breakdown'] ?? [];
        $healthBreakdown = $baseData['health_breakdown'] ?? [];

        $history->pedet_price = (int) round($baseData['harga_pedet'] ?? 0);
        $history->additional_cost = (int) round($form->biayaTambahan ?? 0);

        $history->forage_price       = (int) round($feedBreakdown['forage'] ?? 0);
        $history->concentrate_price  = (int) round($feedBreakdown['concentrate'] ?? 0);
        $history->additive_price     = (int) round($feedBreakdown['additive'] ?? 0);
        $history->insemination       = (int) round($healthBreakdown['insemination'] ?? 0);
        $history->vaccine            = (int) round($healthBreakdown['vaccine'] ?? 0);
        $history->vitamin            = (int) round($healthBreakdown['vitamin'] ?? 0);
        $history->pregnancy_check    = (int) round($healthBreakdown['pregnancy_check'] ?? 0);
        $history->antibiotics        = (int) round($healthBreakdown['antibiotics'] ?? 0);
        $history->anthelmintic       = (int) round($healthBreakdown['anthelmintic'] ?? 0);

        $history->cage_price = (int) round($stats['cage']['cost_per_head'] ?? 0);
        $history->cage_productive_age = (int) round($stats['base']['shared']['cage_economic_age'] ?? 0);

        $labourCost = $baseData['harga_tenaga_kerja'] ?? 0;
        $history->workers_price = (int) round($labourCost);
        $history->workers_per_livestock = (int) ($stats['labour']['livestock_per_employee'] ?? 0);
        $history->number_of_workers = (int) ($stats['labour']['employee_count'] ?? 0);

        $history->margin = (int) round($form->marginKeuntungan ?? 0);
        $history->inflation = (int) round($form->inflasi ?? 0);

        if (!$history->save()) {
            Yii::error([
                'message' => 'Failed to save history record',
                'errors'  => $history->errors,
            ], __METHOD__);

            return false;
        }

        HistoryChangeLog::logChange($history, [], $history->buildSnapshot());

        return true;
    }

}
