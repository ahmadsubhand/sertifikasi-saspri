<?php

namespace common\models\form;

use yii\base\Model;

class RegisterForm extends Model
{
    /** @var string */
    public $username;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $full_name;

    /** @var string */
    public $phone_number;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'full_name', 'phone_number'], 'trim'],
            [['username', 'email', 'full_name', 'phone_number'], 'required'],
            [['username', 'email', 'full_name'], 'string', 'min' => 3, 'max' => 255],

            ['email', 'email'],

            ['password', 'required'],
            ['password', 'string', 'min' => 8],

            ['phone_number', 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nama Pengguna',
            'email' => 'Email',
            'password' => 'Kata Sandi',
            'full_name' => 'Nama Lengkap',
            'phone_number' => 'Nomor Telepon',
        ];
    }
}
