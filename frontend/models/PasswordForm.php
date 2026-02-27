<?php
namespace frontend\models;

use Yii;
use yii\base\Model;

class PasswordForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $confirmNewPassword;

    private $_user;

    public function __construct($user, $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['currentPassword', 'newPassword', 'confirmNewPassword'], 'required'],
            [['newPassword'], 'string', 'min' => 8, 'max' => 255],
            ['confirmNewPassword', 'compare', 'compareAttribute' => 'newPassword'],
            ['currentPassword', 'validateCurrentPassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'currentPassword' => 'Current password',
            'newPassword' => 'New password',
            'confirmNewPassword' => 'Confirm new password',
        ];
    }

    public function validateCurrentPassword($attribute)
    {
        if (!$this->_user || !$this->_user->validatePassword($this->$attribute)) {
            $this->addError($attribute, 'Current password is incorrect.');
        }
    }

    public function changePassword()
    {
        $this->_user->setPassword($this->newPassword);

        // IMPORTANT:
        // Do NOT regenerate auth_key here, or Yii may invalidate the current login/remember-me cookie.
        // $this->_user->generateAuthKey();

        return $this->_user->save(false, ['password_hash']);    }
}