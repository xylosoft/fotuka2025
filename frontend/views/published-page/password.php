<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Password Protected Page';
?>

<div class="public-password-page">
    <div class="password-card">
        <h1>Password Protected</h1>
        <p>This page requires a password before it can be viewed.</p>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(); ?>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="page_password" class="form-control" autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block">View Page</button>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<style>
    .public-password-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #eff6ff, #f8fafc);
        padding: 24px;
    }
    .password-card {
        width: 100%;
        max-width: 440px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 16px 40px rgba(0,0,0,.08);
    }
    .password-card h1 {
        margin: 0 0 10px;
        font-size: 28px;
        font-weight: 700;
    }
    .password-card p {
        margin: 0 0 16px;
        color: #6b7280;
    }
</style>