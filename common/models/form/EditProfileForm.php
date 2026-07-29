<?php

namespace common\models\form;

use common\models\User;
use yii\base\Model;

class EditProfileForm extends Model
{
    public ?string $username = null;
    public ?string $nik = null;
    public ?string $full_name = null;
    public ?string $birthdate = null;
    public ?string $phone_number = null;
    public ?string $gender = null;
    public ?string $address = null;
    public ?int $user_id = null;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['username', 'string', 'max' => 50],
            ['phone_number', 'string', 'max' => 16],
            ['nik', 'string', 'max' => 16, 'message' => 'NIK harus terdiri dari 16 digit.'],
            ['nik', 'unique', 'targetClass' => User::class, 'message' => 'NIK sudah terdaftar'],
            [['nik'], 'match', 'pattern' => '/^\d{16}$/', 'message' => 'NIK harus terdiri dari 16 digit.'],
            [['birthdate'], 'date', 'format' => 'php:Y-m-d', 'message' => 'Format tanggal tidak sesuai. Gunakan format YYYY-MM-DD.'],
            [['birthdate'], 'validateBirthdate'],
            [['full_name', 'address', 'gender'], 'string', 'max' => 255],
            [['full_name'], 'match', 'pattern' => '/^[a-zA-Z\s]+$/', 'message' => 'Nama lengkap hanya boleh berisi huruf dan spasi.'],
            [['phone_number'], 'match', 'pattern' => '/^08\d{1,15}$/', 'message' => 'Nomor telepon tidak valid. Gunakan format 08xxxxxxxxxx.'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nama Pengguna',
            'nik' => 'NIK',
            'full_name' => 'Nama Lengkap',
            'birthdate' => 'Tanggal Lahir',
            'phone_number' => 'No. Handphone',
            'gender' => 'Jenis Kelamin',
            'address' => 'Alamat',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // Keep all attributes before validation
        $this->setAttributes([
            'username' => $this->username,
            'nik' => $this->nik,
            'full_name' => $this->full_name,
            'birthdate' => $this->birthdate,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'address' => $this->address,
        ]);

        return parent::beforeValidate();
    }

    /**
     * Validates the birthdate.
     * This method serves as the inline validation for birthdate.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateBirthdate($attribute, $params)
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $birthdate = \DateTime::createFromFormat('Y-m-d', $this->$attribute);
    
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');
    
        if ($birthdate >= $tomorrow) {
            $this->addError($attribute, 'Tanggal lahir tidak boleh lebih dari hari ini.');
        }
    }
}
