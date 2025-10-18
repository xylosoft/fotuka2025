<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\Customer;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $display_name;
    public $username;
    public $email;
    public $password;
    public $rememberMe;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['display_name', 'trim'],
            ['display_name', 'unique', 'targetClass' => '\common\models\Customer', 'message' => 'This customer name has already been taken.'],
            ['display_name', 'string', 'max' => 100],

            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email Address',
            'display_name' => 'Company Name',
            'password' => 'Password',
        ];
    }


    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        // Create customer record.
        $customer = new Customer();
        $customer->display_name = $this->display_name?$this->display_name:null;
        $customer->ip_country_code = null;
        $customer->referral_url = null;
        $customer->status = Customer::STATUS_ACTIVE;
        $customer->seo_name = null;
        $customer->referral_url = null;
        $customer->save();

        $user = new User();
        $user->customer_id = $customer->id;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();

        $success = $user->save();
        if (!$success){
            $customer->delete();
            return null;
        }else{
            Yii::$app->user->login($user, 3600 * 24); //Only allow for 24 hours to allow time for email verification.
            $user->status= User::STATUS_INACTIVE;
            $user->save();
        }
        $this->sendEmail($user);
        return $success;
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
