<?php
namespace frontend\models;

use Yii;
use yii\base\Model;

class ProfileForm extends Model
{
    public $username;
    public $email;

    /**
     * Base64 data URL from CropperJS:
     * e.g. data:image/jpeg;base64,....
     */
    public $avatarCropped;

    public function rules()
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            ['email', 'email'],
            [['username'], 'string', 'min' => 2, 'max' => 64],

            // avatar is optional
            ['avatarCropped', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
        ];
    }

    public function loadFromUser($user)
    {
        $this->username = $user->username;
        $this->email = $user->email;
    }

    public function saveToUser($user)
    {
        $user->username = $this->username;
        $user->email = $this->email;
        return $user->save(false, ['username', 'email']);
    }
}