<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var common\models\WebsiteTemplate[] $templates */
/** @var yii\data\Pagination $templatePagination */
/** @var common\models\WebsitePublication[] $publications */
/** @var yii\data\Pagination $publicationPagination */
/** @var array $folderNames */

$this->title = 'Website Templates';
?>
<style>
    html, body {
        margin: 0;
        background: linear-gradient(180deg, #f4f8fc 0%, #edf4fb 100%);
    }
    .template-index-page {
        color: #10233f;
        padding: 0px;
        padding-top: 0;
        margin-top: 24px;
        background: linear-gradient(180deg, #f5f8fc 0%, #eef4fb 100%);

    }
    .template-index-shell {
        min-width:1280px;
        margin:0 auto;
    }
    .template-hero {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:18px;
        margin-bottom:24px;
        background:#fff;
        border:1px solid #dbe6f3;
        border-radius:20px;
        padding:24px 28px;
        box-shadow:0 20px 50px rgba(17,40,74,.08);
    }
    .template-hero h1 {
        margin:0 0 8px;
        font-size:30px;
        font-weight:800;
    }
    .template-hero p {
        margin:0;
        color:#5a6f8d;
        max-width:820px;
        line-height:1.55;
    }
    .template-grid {
        display:grid;
        grid-template-columns:1fr;
        gap:20px;
    }
    .template-card {
        background:#fff;
        border:1px solid #dbe6f3;
        border-radius:20px;
        padding:22px;
        box-shadow:0 18px 42px rgba(17,40,74,.06);
    }
    .template-card h2 { margin:0 0 16px;
        font-size:21px;
        font-weight:800;
    }
    .template-card table {
        width:100%;
        border-collapse:collapse;
    }
    .template-card th,.template-card td {
        padding:14px 10px;
        border-bottom:1px solid #e7eef8;
        text-align:left;
        vertical-align:middle;
    }
    .template-card th { font-size:12px;
        text-transform:uppercase;
        letter-spacing:.08em;
        color:#6b7f9b;
    }
    .template-card tr:last-child td {
        border-bottom:none;
    }
    .template-actions {
        display:flex;
        gap:8px;
        flex-wrap:wrap;
    }
    .btn-fotuka {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        background:#2563eb;
        color:#fff;
        border:none;
        border-radius:12px;
        padding:10px 16px;
        font-weight:700;
        text-decoration:none;
        box-shadow:0 14px 26px rgba(37,99,235,.18);
    }
    .btn-fotuka:hover {
        color:#fff;
        text-decoration:none;
        background:#1d4ed8;
    }
    .btn-fotuka-secondary {
        background:#fff;
        color:#12335c;
        border:1px solid #dbe6f3;
        box-shadow:none;
    }
    .btn-fotuka-secondary:hover {
        background:#f8fbff;
        color:#12335c;
    }
    .btn-fotuka-danger {
        background:#fff5f5;
        color:#b42318;
        border:1px solid #fecaca;
        box-shadow:none;
    }
    .muted {
        color:#6b7f9b;
    }
    .table-empty {
        padding:26px;
        text-align:center;
        color:#6b7f9b;
        border:1px dashed #c6d5e7;
        border-radius:16px;
        background:#fafcff;
    }
    .pager-shell {
        margin-top:18px;
    }
    .pagination {
        display:flex;
        gap:8px;
        padding-left:0;
        list-style:none;
    }
    .pagination li a,.pagination li span {
        display:inline-flex;
        min-width:38px;
        height:38px;
        padding:0 12px;
        align-items:center;
        justify-content:center;
        border-radius:10px;
        border:1px solid #d6e2f0;
        background:#fff;
        color:#16345b;
        text-decoration:none;
    }
    .pagination .active a,.pagination .active span {
        background:#2563eb;
        border-color:#2563eb;
        color:#fff;
    }
</style>
<div class="template-index-page">
    <div class="template-index-shell">
        <a class="breadcrum-link" href="/folders">Folders</a>
        &nbsp;&nbsp;/&nbsp;&nbsp;
        <span class="breadcrum-static">Website Templates</span>

        <div class="template-hero">
            <div>
                <h1>Website Templates</h1>
                <p>Build polished, publishable pages with a live canvas editor, then bind each folder to a public page with text, gallery, carousel, password, and download controls.</p>
            </div>
            <div>
                <a class="btn-fotuka" href="<?= Url::to(['template-editor']) ?>">+ New Template</a>
            </div>
        </div>

        <div class="template-grid">
            <div class="template-card">
                <h2>Templates</h2>

                <?php if ($templates): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Updated</th>
                            <th>In Use</th>
                            <th style="width:260px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td>
                                    <strong><?= Html::encode($template->name) ?></strong><br>
                                    <span class="muted">ID #<?= (int) $template->id ?></span>
                                </td>
                                <td><?= date('M j, Y g:i a', (int) $template->updated_at) ?></td>
                                <td><?= $template->isInUse() ? 'Yes' : 'No' ?></td>
                                <td>
                                    <div class="template-actions">
                                        <a class="btn-fotuka btn-fotuka-secondary" href="/templateeditor/<?= $template->id ?>">Edit</a>
                                        <a class="btn-fotuka btn-fotuka-danger" href="<?= Url::to(['delete', 'id' => $template->id]) ?>" onclick="return confirm('Delete this template?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="pager-shell">
                        <?= LinkPager::widget(['pagination' => $templatePagination]) ?>
                    </div>
                <?php else: ?>
                    <div class="table-empty">No templates exist yet. Click <strong>New Template</strong> to create your first page design.</div>
                <?php endif; ?>
            </div>

            <div class="template-card">
                <h2>Published Folders</h2>

                <?php if ($publications): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Folder</th>
                            <th>Publishing Name</th>
                            <th>Template</th>
                            <th>Publish Date</th>
                            <th style="width:280px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($publications as $publication): ?>
                            <tr>
                                <td>
                                    <span class="muted"><a href="/folders/<?=$publication->folder->id?>"><?=$publication->folder->name ?></a></span>
                                </td>
                                <td>
                                    <a href="<?= Url::to(['page', 'uri' => $publication->uri]) ?>" target="_blank"><?= $publication->page_title ?></a>
                                </td>
                                <td>
                                    <a href="/templateeditor/<?=$publication->template->id?>"><?= Html::encode($publication->template ? $publication->template->name : ('Template #' . $publication->template_id)) ?></a>
                                </td>
                                <td><?= date('M j, Y g:i a', (int) $publication->created_at) ?></td>
                                <td>
                                    <div class="template-actions">
                                        <a class="btn-fotuka btn-fotuka-secondary" href="/publish/<?= $publication->folder_id ?>">Edit</a>
                                        <a class="btn-fotuka btn-fotuka-danger" href="<?= Url::to(['publication-delete', 'id' => $publication->id]) ?>" onclick="return confirm('Delete this published page? The public page will no longer be available for anyone.');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="pager-shell">
                        <?= LinkPager::widget(['pagination' => $publicationPagination]) ?>
                    </div>
                <?php else: ?>
                    <div class="table-empty">There are no published folders yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>