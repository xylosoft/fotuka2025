<?php
/** @var $this yii\web\View */
/** @var $user app\models\User */
/** @var $profileForm app\models\forms\ProfileForm */
/** @var $passwordForm app\models\forms\PasswordForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\assets\ProfileAsset;
use frontend\controllers\UserController;

ProfileAsset::register($this);

$avatarUrl = $user->profile_picture;
if (!$avatarUrl) {
    $avatarUrl = '/img/default-avatar.png'; // add your own default
}

$this->title = 'Your Profile';
?>

<div class="profile-page container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="m-0"><?= Html::encode($this->title) ?></h2>
            <div class="text-muted">Manage your account details, avatar, and password.</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card fotuka-card">
                <div class="card-header fotuka-card-header">
                    <div class="fw-semibold">Profile</div>
                    <div class="text-muted small">Update your name, email, and profile photo.</div>
                </div>

                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'profileForm',
                        'enableClientValidation' => true,
                        'enableAjaxValidation' => false,
                        'errorCssClass' => 'is-invalid',
                        'fieldConfig' => [
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            'inputOptions' => ['class' => 'form-control'],
                        ],
                    ]); ?>

                    <?= Html::hiddenInput('formType', 'profile') ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($profileForm, 'username')->textInput(['maxlength' => true,'class' => 'form-control']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($profileForm, 'email')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex align-items-start gap-3 profile-avatar-block">
                        <div class="avatar-preview-wrap">
                            <img id="currentAvatar" class="avatar-preview" src="<?= Html::encode($avatarUrl) ?>" alt="Avatar">
                        </div>

                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1">Profile photo</div>
                            <div class="text-muted small mb-2">Upload a photo and crop it before saving.</div>

                            <input id="avatarInput" type="file" accept="image/*" class="form-control">

                            <?= $form->field($profileForm, 'avatarCropped')
                                ->hiddenInput(['id' => 'avatarCropped'])
                                ->label(false); ?>

                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <button type="button" id="btnCrop" class="btn btn-fotuka" disabled>Crop</button>
                                <button type="button" id="btnReset" class="btn btn-outline-secondary" disabled>Reset</button>
                            </div>

                            <div class="cropper-area mt-3">
                                <img id="cropperImage" class="cropper-image" alt="Cropper target" style="display:none;">
                            </div>

                            <div class="text-muted small mt-2">
                                Tip: square crops look best (Fotuka will save a clean avatar).
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button class="btn btn-fotuka px-4" type="submit">Save Profile</button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card fotuka-card">
                <div class="card-header fotuka-card-header">
                    <div class="fw-semibold">Change password</div>
                    <div class="text-muted small">Use a strong password you don’t reuse elsewhere.</div>
                </div>

                <div class="card-body">
                    <?php $form2 = ActiveForm::begin([
                        'id' => 'passwordForm',
                        'enableClientValidation' => true,
                        'enableAjaxValidation' => false,
                        'errorCssClass' => 'is-invalid',
                        'fieldConfig' => [
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            'inputOptions' => ['class' => 'form-control'],
                        ],
                    ]); ?>
                    <?= Html::hiddenInput('formType', 'password') ?>

                    <?= $form2->field($passwordForm, 'currentPassword')->passwordInput(['class' => 'form-control']) ?>
                    <?= $form2->field($passwordForm, 'newPassword')->passwordInput(['class' => 'form-control']) ?>
                    <?= $form2->field($passwordForm, 'confirmNewPassword')->passwordInput(['class' => 'form-control']) ?>

                    <div class="mt-3 d-flex justify-content-end">
                        <button class="btn btn-outline-primary px-4" type="submit">Update Password</button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="small text-muted mt-3">
                If you want, we can also add: password strength meter, “show password” toggles, and rate limiting.
            </div>
        </div>
    </div>
</div>