<?php
/**
 * Fotuka-styled "Request password reset" page
 *
 * @var yii\web\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var frontend\models\PasswordResetRequestForm $model
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use frontend\assets\AuthAsset;

AuthAsset::register($this);

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="profile-page container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-6">
            <div class="card fotuka-card">
                <div class="card-header fotuka-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:14px;background:rgba(47,111,237,0.12);display:flex;align-items:center;justify-content:center;">
                            <!-- Email icon -->
                            <svg width="22" height="22" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 7h16v10H4V7z" fill="none" stroke="#2f6fed" stroke-width="2"/>
                                <path d="M4 7l8 6 8-6" fill="none" stroke="#2f6fed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div>
                            <div class="fw-semibold" style="font-size:18px; line-height: 1.2;">Request password reset</div>
                            <div class="text-muted small">Enter your email and we’ll send you a reset link.</div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin([
                        'id' => 'request-password-reset-form',
                        'enableClientValidation' => true,
                        'enableAjaxValidation' => false,

                        // Inline errors (red) below the field:
                        'errorCssClass' => 'is-invalid',
                        'fieldConfig' => [
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            'inputOptions' => ['class' => 'form-control'],
                        ],
                    ]); ?>

                    <?= $form->field($model, 'email')->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Email address',
                        'autocomplete' => 'email',
                    ])->hint('We’ll send a reset link if the email exists in our system.') ?>

                    <div class="d-grid mt-3">
                        <?= Html::submitButton('Send reset link', [
                            'class' => 'btn btn-fotuka py-2',
                            'name' => 'reset-button',
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <hr class="my-4">

                    <div class="small text-muted">
                        Remembered it?
                        <?= Html::a('Back to login', ['site/login'], ['class' => 'text-decoration-none']) ?>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3 small text-muted">
                If you don’t receive an email, check your spam folder.
            </div>
        </div>
    </div>
</div>