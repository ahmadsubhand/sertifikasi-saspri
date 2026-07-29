<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "history".
 *
 * @property int $id
 * @property string $date
 * @property int $livestock_id
 * @property int $sell_price
 * @property int $pedet_price
 * @property int $additional_cost
 * @property string $business_type
 * @property int $hpp_price
 * @property int $forage_price
 * @property int $concentrate_price
 * @property int $additive_price
 * @property int $insemination
 * @property int $vaccine
 * @property int $vitamin
 * @property int $pregnancy_check
 * @property int $antibiotics
 * @property int $anthelmintic
 * @property int $cage_price
 * @property int $cage_productive_age
 * @property int $workers_price
 * @property int $workers_per_livestock
 * @property int $margin
 * @property int $inflation
 * @property int $number_of_workers
 *
 * @property Livestock $livestock
 */
class History extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%history}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'livestock_id', 'sell_price', 'hpp_price', 'pedet_price', 'forage_price', 'concentrate_price', 'additive_price', 'insemination', 'vaccine', 'vitamin', 'pregnancy_check', 'antibiotics', 'anthelmintic', 'cage_price', 'cage_productive_age', 'workers_price', 'workers_per_livestock', 'margin', 'inflation', 'number_of_workers'], 'required'],
            [['date'], 'safe'],
            [['livestock_id', 'sell_price', 'hpp_price', 'pedet_price', 'additional_cost', 'forage_price', 'concentrate_price', 'additive_price', 'insemination', 'vaccine', 'vitamin', 'pregnancy_check', 'antibiotics', 'anthelmintic', 'cage_price', 'cage_productive_age', 'workers_price', 'workers_per_livestock', 'margin', 'inflation', 'number_of_workers'], 'integer'],
            [['business_type'], 'string', 'max' => 20],
            [['livestock_id'], 'exist', 'skipOnError' => true, 'targetClass' => Livestock::class, 'targetAttribute' => ['livestock_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'livestock_id' => 'Livestock',
            'sell_price' => 'Sell Price',
            'pedet_price' => 'Harga Pedet / Investasi Indukan',
            'additional_cost' => 'Biaya Tambahan',
            'business_type' => 'Jenis Usaha',
            'hpp_price' => 'HPP Price',
            'forage_price' => 'Forage Price',
            'concentrate_price' => 'Concentrate Price',
            'additive_price' => 'Additive Price',
            'insemination' => 'Insemination',
            'vaccine' => 'Vaccine',
            'vitamin' => 'Vitamin',
            'pregnancy_check' => 'Pregnancy Check',
            'antibiotics' => 'Antibiotics',
            'anthelmintic' => 'Anthelmintic',
            'cage_price' => 'Cage Price',
            'cage_productive_age' => 'Cage Productive Age',
            'workers_price' => 'Workers Price',
            'workers_per_livestock' => 'Workers Per Livestock',
            'margin' => 'Margin',
            'inflation' => 'Inflation',
            'number_of_workers' => 'Number Of Workers',
        ];
    }

    /**
     * Gets query for [[Livestock]].
     * @return \yii\db\ActiveQuery
     */
    public function getLivestock()
    {
        return $this->hasOne(Livestock::class, ['id' => 'livestock_id']);
    }

    public function getChangeLogs()
    {
        return $this->hasMany(HistoryChangeLog::class, ['history_id' => 'id'])->orderBy(['changed_at' => SORT_DESC]);
    }

    public function buildSnapshot(): array
    {
        return [
            'pedet_price' => (int) $this->pedet_price,
            'additional_cost' => (int) $this->additional_cost,
            'business_type' => (string) $this->business_type,
            'feed' => [
                'forage'      => (int) $this->forage_price,
                'concentrate' => (int) $this->concentrate_price,
                'additive'    => (int) $this->additive_price,
            ],
            'health' => [
                'insemination'    => (int) $this->insemination,
                'vaccine'         => (int) $this->vaccine,
                'vitamin'         => (int) $this->vitamin,
                'pregnancy_check' => (int) $this->pregnancy_check,
                'antibiotics'     => (int) $this->antibiotics,
                'anthelmintic'    => (int) $this->anthelmintic,
            ],
            'cage_price' => (int) $this->cage_price,
            'hpp_price'  => (int) $this->hpp_price,
            'sell_price' => (int) $this->sell_price,
        ];
    }

    public static function recalculateForLivestock(Livestock $livestock): void
    {
        $histories = self::find()->where(['livestock_id' => $livestock->id])->all();
        if (empty($histories)) {
            return;
        }

        $snapshot = self::buildComponentSnapshot($livestock);

        foreach ($histories as $history) {
            $businessType = $history->business_type ?: (strtolower($livestock->purpose) === 'indukan' ? 'breeding' : 'penggemukan');
            $history->business_type = $businessType;

            $newPedet = $businessType === 'breeding'
                ? (int) round($livestock->breeding_investment ?? 0)
                : (int) round($livestock->first_price ?? 0);

            $newFeed = $snapshot['feed'];
            $newHealth = $snapshot['health'];
            $newCage = (int) round($snapshot['cage_cost']);
            $newCageAge = (int) round($snapshot['cage_age']);

            $previous = [
                'pedet_price' => (int) $history->pedet_price,
                'additional_cost' => (int) $history->additional_cost,
                'feed' => [
                    'forage'      => (int) $history->forage_price,
                    'concentrate' => (int) $history->concentrate_price,
                    'additive'    => (int) $history->additive_price,
                ],
                'health' => [
                    'insemination'    => (int) $history->insemination,
                    'vaccine'         => (int) $history->vaccine,
                    'vitamin'         => (int) $history->vitamin,
                    'pregnancy_check' => (int) $history->pregnancy_check,
                    'antibiotics'     => (int) $history->antibiotics,
                    'anthelmintic'    => (int) $history->anthelmintic,
                ],
                'cage_price' => (int) $history->cage_price,
                'hpp_price'  => (int) $history->hpp_price,
                'sell_price' => (int) $history->sell_price,
            ];

            $newHealthTotal = array_sum($newHealth);
            $oldHealthTotal = array_sum($previous['health']);

            $newFeedTotal = array_sum($newFeed);
            $oldFeedTotal = array_sum($previous['feed']);

            $changed = $newPedet !== $previous['pedet_price']
                || $newFeed['forage'] !== $previous['feed']['forage']
                || $newFeed['concentrate'] !== $previous['feed']['concentrate']
                || $newFeed['additive'] !== $previous['feed']['additive']
                || $newHealth['insemination'] !== $previous['health']['insemination']
                || $newHealth['vaccine'] !== $previous['health']['vaccine']
                || $newHealth['vitamin'] !== $previous['health']['vitamin']
                || $newHealth['pregnancy_check'] !== $previous['health']['pregnancy_check']
                || $newHealth['antibiotics'] !== $previous['health']['antibiotics']
                || $newHealth['anthelmintic'] !== $previous['health']['anthelmintic']
                || $newCage !== $previous['cage_price'];

            if (!$changed) {
                continue;
            }

            $history->pedet_price = $newPedet;
            $history->forage_price = (int) round($newFeed['forage']);
            $history->concentrate_price = (int) round($newFeed['concentrate']);
            $history->additive_price = (int) round($newFeed['additive']);
            $history->insemination = (int) round($newHealth['insemination']);
            $history->vaccine = (int) round($newHealth['vaccine']);
            $history->vitamin = (int) round($newHealth['vitamin']);
            $history->pregnancy_check = (int) round($newHealth['pregnancy_check']);
            $history->antibiotics = (int) round($newHealth['antibiotics']);
            $history->anthelmintic = (int) round($newHealth['anthelmintic']);
            $history->cage_price = $newCage;
            $history->cage_productive_age = $newCageAge;

            $oldHpp = (int) $history->hpp_price;
            $newHpp = $oldHpp
                - $previous['pedet_price']
                - $oldFeedTotal
                - $oldHealthTotal
                - $previous['cage_price']
                + $newPedet
                + $newFeedTotal
                + $newHealthTotal
                + $newCage;

            $history->hpp_price = $newHpp;
            $history->sell_price = (int) round($newHpp * (1 + (((int)$history->margin + (int)$history->inflation) / 100)));
            $history->save(false);

            HistoryChangeLog::logChange($history, $previous, $history->buildSnapshot());
        }
    }

    private static function buildComponentSnapshot(Livestock $livestock): array
    {
        $feed = [
            'forage'      => 0.0,
            'concentrate' => 0.0,
            'additive'    => 0.0,
        ];

        $health = [
            'insemination'    => 0.0,
            'vaccine'         => 0.0,
            'vitamin'         => 0.0,
            'pregnancy_check' => 0.0,
            'antibiotics'     => 0.0,
            'anthelmintic'    => 0.0,
        ];

        foreach ($livestock->notes as $note) {
            $feed['forage']      += (float) $note->forage_costs * (float) $note->forage_weight;
            $feed['concentrate'] += (float) $note->consentrate_costs * (float) $note->consentrate_weight;
            $feed['additive']    += (float) $note->additive_costs * (float) $note->additive_weight;

            $health['insemination']    += (float) $note->insemination;
            $health['vaccine']         += (float) $note->vaccine;
            $health['vitamin']         += (float) $note->vitamin;
            $health['pregnancy_check'] += (float) $note->pregnancy_check;
            $health['antibiotics']     += (float) $note->antibiotics;
            $health['anthelmintic']    += (float) $note->anthelmintic;
        }

        $cage = $livestock->cage;
        $investment = $cage ? (float) $cage->investasi_kandang : 0.0;
        $capacity   = $cage && (int) $cage->capacity > 0 ? (int) $cage->capacity : 1;
        $economic   = $cage ? (float) $cage->umur_ekonomis : 0.0;

        return [
            'feed'      => array_map('round', $feed),
            'health'    => array_map('round', $health),
            'cage_cost' => $capacity > 0 ? $investment / $capacity : 0,
            'cage_age'  => $economic,
        ];
    }
}
