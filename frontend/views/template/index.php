<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Templates';
?>

<div class="template-index-page">
    <div class="template-index-header">
        <div>
            <h1>Templates</h1>
            <p>Create and manage your website/page templates.</p>
        </div>
        <div>
            <a href="<?= Url::to(['template/editor']) ?>" class="btn btn-primary">+ New Template</a>
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <div class="template-table-wrap">
        <table class="table table-striped table-hover template-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Password Protected</th>
                <th>Allow Downloads</th>
                <th>Created</th>
                <th style="width:180px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$templates): ?>
                <tr>
                    <td colspan="6" class="text-center">No templates found.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($templates as $template): ?>
                <tr>
                    <td><?= (int)$template->template_id ?></td>
                    <td><?= Html::encode($template->name) ?></td>
                    <td><?= $template->password_enabled ? 'Yes' : 'No' ?></td>
                    <td><?= $template->allow_downloads ? 'Yes' : 'No' ?></td>
                    <td><?= Html::encode($template->created_at) ?></td>
                    <td>
                        <a href="/templateeditor/<?= (int)$template->template_id ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="'template/delete/<?= (int)$template->template_id ?>"
                           class="btn btn-sm btn-outline-danger" data-method="post"
                           data-confirm="Are you sure you want to delete this template?">Delete</a>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .template-index-page {
        padding: 24px;
    }
    .template-index-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .template-index-header h1 {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 700;
    }
    .template-index-header p {
        margin: 0;
        color: #6b7280;
    }
    .template-table-wrap {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 12px 30px rgba(0,0,0,.04);
    }

</style>