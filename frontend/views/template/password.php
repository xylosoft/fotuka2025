<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\WebsitePublication $publication */

$this->title = 'Protected Page';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; color:#10233f; background:linear-gradient(180deg,#f5f8fc 0%,#ebf2fb 100%); }
        .lock-card { width:min(560px,100%); background:#fff; border:1px solid #dbe6f3; border-radius:26px; box-shadow:0 24px 60px rgba(17,40,74,.10); overflow:hidden; }
        .lock-top { padding:28px 30px 18px; border-bottom:1px solid #ebf1f8; }
        .lock-badge { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:12px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .lock-top h1 { margin:16px 0 8px; font-size:32px; font-weight:800; color:#14345c; }
        .lock-top p { margin:0; color:#6a7d98; line-height:1.55; }
        .lock-body { padding:24px 30px 30px; }
        .lock-label { display:block; margin-bottom:8px; font-size:13px; font-weight:800; color:#23446d; }
        .lock-input { width:100%; min-height:50px; border:1px solid #cfdded; border-radius:14px; padding:12px 14px; font-size:15px; color:#10233f; outline:none; }
        .lock-input:focus { border-color:#2563eb; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
        .lock-actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:18px; }
        .btn-fotuka { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:46px; padding:0 18px; border-radius:12px; font-weight:800; text-decoration:none; border:none; cursor:pointer; }
        .btn-fotuka-primary { background:#2563eb; color:#fff; box-shadow:0 14px 28px rgba(37,99,235,.18); }
        .btn-fotuka-secondary { background:#fff; color:#17355d; border:1px solid #dbe6f3; }
        .lock-alert { margin-bottom:14px; padding:14px 16px; border-radius:14px; background:#fff4f4; color:#b42318; border:1px solid #fecaca; }
    </style>
</head>
<body>
<div class="lock-card">
    <div class="lock-top">
        <div class="lock-badge">Protected Page</div>
        <h1><?= Html::encode($publication->page_title ?: 'Private Page') ?></h1>
        <p>This page is password protected. Enter the correct password to continue to the published gallery.</p>
    </div>
    <div class="lock-body">
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <?php if ($type === 'error'): ?>
                <div class="lock-alert"><?= Html::encode($message) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
            <label class="lock-label" for="pagePassword">Password</label>
            <input class="lock-input" type="password" id="pagePassword" name="page_password" autocomplete="current-password" autofocus>

            <div class="lock-actions">
                <button type="submit" class="btn-fotuka btn-fotuka-primary">View Page</button>
                <a class="btn-fotuka btn-fotuka-secondary" href="<?= Url::to(['/']) ?>">Back to Home</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>