<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use frontend\assets\AuthAsset;
use yii\helpers\Url;

AuthAsset::register($this);

$this->title = 'Login';
?>

<div class="profile-page container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-6">
            <div class="card fotuka-card">
                <div class="card-header fotuka-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:14px;background:rgba(47,111,237,0.12);display:flex;align-items:center;justify-content:center;">
                            <!-- Simple Fotuka-ish user icon -->
                            <svg width="22" height="22" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="12" fill="none"/>
                                <circle cx="12" cy="9" r="4" fill="#2f6fed"/>
                                <path d="M4 20c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5" fill="#2f6fed"/>
                            </svg>
                        </div>

                        <div>
                            <div class="fw-semibold" style="font-size:18px; line-height: 1.2;">Welcome back</div>
                            <div class="text-muted small">Log in to continue to Fotuka.</div>
                        </div>
                    </div>
                </div>

                <div class="google-login-wrap">
                    <a class="btn-google-signin" href="<?= Url::to(['site/auth', 'authclient' => 'google']) ?>">
                        <span class="google-g" aria-hidden="true"></span>
                        <span class="google-text">Continue with Google</span>
                    </a>
                </div>

                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin([
                        'validateOnBlur' => false,
                        'validateOnChange' => false,
                        'validateOnType' => false,
                        'validateOnSubmit' => true,
                        'id' => 'login-form',
                        'enableClientValidation' => true,
                        'enableAjaxValidation' => false,

                        // Ensure errors show under each input with Bootstrap styling:
                        'errorCssClass' => 'is-invalid',
                        'fieldConfig' => [
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            'inputOptions' => ['class' => 'form-control'],
                        ],
                    ]); ?>

                    <?= $form->field($model, 'username')->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Username or email',
                    ]) ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Password',
                    ]) ?>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <?= $form->field($model, 'rememberMe', [
                            // Make checkbox compact and aligned
                            'template' => "{input} {label}\n{hint}\n{error}",
                            'options' => ['class' => 'form-check m-0'],
                        ])->checkbox([
                            'class' => 'form-check-input',
                        ], false)->label('Remember me', ['class' => 'form-check-label']); ?>

                        <div class="small">
                            <?= Html::a('Forgot password?', ['site/request-password-reset'], ['class' => 'text-decoration-none']) ?>
                        </div>
                    </div>

                    <div class="d-grid mt-3">
                        <?= Html::submitButton('Login', [
                            'class' => 'btn btn-fotuka py-2',
                            'name' => 'login-button',
                        ]) ?>
                    </div>

                    <hr class="my-4">
                    <div class="small text-muted">
                        Forgot your password?
                        <?= Html::a('Reset Password', ['site/request-password-reset'], ['class' => 'text-decoration-none']) ?>
                    </div>
                    <!--


                        Need new verification email?
                        <?= Html::a('Resend', ['site/resend-verification-email'], ['class' => 'text-decoration-none']) ?>

                    -->

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="text-center mt-3 small text-muted">
                By continuing you agree to Fotuka’s terms and privacy policy.
            </div>
        </div>
    </div>
</div>