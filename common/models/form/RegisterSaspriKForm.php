<?php

namespace common\models\form;

use common\helpers\FileHelper;
use yii\base\Model;
use yii\web\UploadedFile;

class RegisterSaspriKForm extends Model
{
    /** @var int */
    public $district_id;

    /** @var string */
    public $region_name;

    /** @var string */
    public $address;

    /** @var string */
    public $cooperative_name;

    /** @var int */
    public $number_of_groups;

    /** @var int */
    public $number_of_active_members;

    /** @var string */
    public $livestock_type;

    /** @var int */
    public $total_livestock_count;

    /** @var int */
    public $breeding_livestock_count;

    /** @var int */
    public $productive_heifer_count;

    /** @var array */
    public $saspri_k_documents = [];

    public function rules()
    {
        return [
            ['district_id', 'required'],
            ['district_id', 'integer', 'min' => 1],

            [
                [
                    'region_name',
                    'address',
                    'cooperative_name',
                    'livestock_type',
                ],
                'required'
            ],

            [
                [
                    'region_name',
                    'address',
                    'cooperative_name',
                    'livestock_type',
                ],
                'string'
            ],

            [
                [
                    'number_of_groups',
                    'number_of_active_members',
                    'total_livestock_count',
                    'breeding_livestock_count',
                    'productive_heifer_count',
                ],
                'required'
            ],

            [
                [
                    'number_of_groups',
                    'number_of_active_members',
                    'total_livestock_count',
                    'breeding_livestock_count',
                    'productive_heifer_count',
                ],
                'integer',
                'min' => 0
            ],

            ['saspri_k_documents', 'required'],
            ['saspri_k_documents', 'validateDocuments'],
        ];
    }

    public function validateDocuments(string $attribute)
    {
        if (!is_array($this->$attribute)) {
            $this->addError(
                $attribute,
                'Parameter saspri_k_documents harus berupa array'
            );
            return;
        }

        if (empty($this->$attribute)) {
            $this->addError(
                $attribute,
                'Wajib menyertakan dokumen pendukung minimal sertifikasi tingkat Natalia'
            );
            return;
        }

        foreach ($this->$attribute as $index => $document_type) {
            if (empty($document_type)) {
                $this->addError(
                    $attribute,
                    "Tipe dokumen pada index {$index} wajib diisi"
                );
                continue;
            }

            $file = UploadedFile::getInstanceByName(
                "saspri_k_documents[{$index}]"
            );

            if (!$file) {
                $this->addError(
                    $attribute,
                    "File dokumen pada index {$index} wajib diunggah"
                );
                continue;
            }

            if (!in_array($file->extension, FileHelper::$allowed_extensions)) {
                $this->addError(
                    $attribute,
                    "Dokumen pada index {$index} harus berupa file pdf, gambar, word, excel, atau csv"
                );
            }

            if ($file->size > 5 * 1024 * 1024) {
                $this->addError(
                    $attribute,
                    "Ukuran dokumen pada index {$index} maksimal 5MB"
                );
            }
        }
    }
}