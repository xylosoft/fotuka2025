<?php

namespace frontend\controllers;

use common\models\WebsitePublication;
use common\models\WebsiteTemplate;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


class TemplateController extends Controller
{
    public function actionTemplates()
    {
        $templateQuery = WebsiteTemplate::findActive()->orderBy(['updated_at' => SORT_DESC]);
        $templateQuery = $this->applyOwnershipScope($templateQuery, WebsiteTemplate::tableName());

        $templatePagination = new Pagination([
            'totalCount' => $templateQuery->count(),
            'pageSize' => 10,
            'pageParam' => 'templatePage',
        ]);

        $templates = $templateQuery
            ->offset($templatePagination->offset)
            ->limit($templatePagination->limit)
            ->all();

        $publicationQuery = WebsitePublication::findActive('publication')
            ->joinWith(['template template'])
            ->orderBy(['publication.updated_at' => SORT_DESC]);
        //echo $publicationQuery->createCommand()->rawSql;die;

        $publicationQuery = $this->applyOwnershipScope($publicationQuery, 'publication');

        $publicationPagination = new Pagination([
            'totalCount' => $publicationQuery->count(),
            'pageSize' => 10,
            'pageParam' => 'publicationPage',
        ]);

        $publications = $publicationQuery
            ->offset($publicationPagination->offset)
            ->limit($publicationPagination->limit)
            ->all();

        $folderNames = [];
        foreach ($publications as $publication) {
            $folderNames[$publication->id] = $this->resolveFolderNameFromId($publication->id);
        }

        return $this->render('templates', [
            'templates' => $templates,
            'templatePagination' => $templatePagination,
            'publications' => $publications,
            'publicationPagination' => $publicationPagination,
            'folderNames' => $folderNames,
        ]);
    }

    public function actionTemplateEditor($id = null)
    {
        if ($id) {
            $model = $this->findTemplate($id);
        } else {
            $model = new WebsiteTemplate();
            $model->name = 'Untitled Template';
            $model->setDefinitionArray(WebsiteTemplate::defaultDefinition());
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->name = trim((string) ($post['WebsiteTemplate']['name'] ?? $model->name));
            $model->definition_json = (string) ($post['WebsiteTemplate']['definition_json'] ?? '');

            if ($model->isNewRecord) {
                $model->user_id = $this->currentUserId();
                $model->customer_id = $this->currentCustomerId();
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Template saved successfully.');
                return $this->redirect(['template-editor', 'id' => $model->id]);
            }

            Yii::$app->session->setFlash('error', 'Please fix the highlighted problems and save again.');
        }

        return $this->render('editor', [
            'model' => $model,
            'definition' => $model->getDefinitionArray(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findTemplate($id);

        if ($model->isInUse()) {
            Yii::$app->session->setFlash('error', 'This template cannot be deleted because it is currently in use by a published folder.');
            return $this->redirect(['templates']);
        }

        $model->deleted = time();
        $model->deleted_by_user_id = $this->currentUserId();
        $model->save(false, ['deleted', 'deleted_by_user_id']);

        Yii::$app->session->setFlash('success', 'Template deleted.');
        return $this->redirect(['templates']);
    }

    public function actionPublish($id, $template_id = null, $publication_id = null)
    {
        $this->layout = 'folder';
        $folder = $this->fetchFolder($id);

        if (!$folder) {
            throw new NotFoundHttpException('Folder not found.');
        }

        $publication = null;
        $template = null;
        $definition = null;

        if ($publication_id) {
            $publication = $this->findPublication($publication_id);
        } else {
            $publication = WebsitePublication::findActive()
                ->andWhere(['id' => (int) $id])
                ->one();
        }

        if ($publication) {
            $template = $publication->template;
            $definition = $publication->getSnapshotArray();
        } elseif ($template_id) {
            $template = $this->findTemplate($template_id);
            $definition = $template->getDefinitionArray();
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            if (!$publication) {
                $publication = new WebsitePublication();
                $publication->id = (int) $id;
                $publication->user_id = $this->currentUserId();
                $publication->customer_id = $this->currentCustomerId();
            }

            $selectedTemplateId = (int) ($post['WebsitePublication']['id'] ?? $template_id ?: ($publication->template_id ?? 0));

            if (!$publication->id || $publication->id != $selectedTemplateId) {
                $template = $this->findTemplate($selectedTemplateId);
                $definition = $template->getDefinitionArray();
            } elseif ($publication->id) {
                $template = $publication->template ?: $this->findTemplate($publication->id);
                $definition = $publication->getSnapshotArray();
            }

            $publishDefaults = $definition['publish_defaults'] ?? [
                    'is_password_protected' => false,
                    'allow_download_all' => false,
                ];

            if ($publication->isNewRecord) {
                $publication->is_password_protected = !empty($publishDefaults['is_password_protected']) ? 1 : 0;
                $publication->allow_download_all = !empty($publishDefaults['allow_download_all']) ? 1 : 0;
            }

            if (!$template) {
                Yii::$app->session->setFlash('error', 'Please select a template before publishing.');
                return $this->refresh();
            }

            $uri = trim((string) ($post['WebsitePublication']['uri'] ?? ''));
            $pageTitle = trim((string) ($post['WebsitePublication']['page_title'] ?? $this->resolveFolderName($folder)));
            $valuesJson = (string) ($post['WebsitePublication']['values_json'] ?? '{}');
            $isProtected = !empty($post['WebsitePublication']['is_password_protected']) ? 1 : 0;
            $allowDownloadAll = !empty($post['WebsitePublication']['allow_download_all']) ? 1 : 0;

            $publication->id = $template->id;
            $publication->page_title = $pageTitle;
            $publication->uri = $uri ?: $this->defaultFolderSlug($folder);
            $publication->is_password_protected = $isProtected;
            $publication->allow_download_all = $allowDownloadAll;
            $publication->plain_password = (string) ($post['WebsitePublication']['plain_password'] ?? '');
            $publication->template_snapshot_json = Json::encode($definition, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $publication->values_json = $valuesJson;

            if ($publication->save()) {
                Yii::$app->session->setFlash('success', 'Published page saved successfully.');
                return $this->redirect(['templates']);
            }

            Yii::$app->session->setFlash('error', 'Please review the publishing form and try again.');
        }

        $templates = WebsiteTemplate::findActive()->orderBy(['name' => SORT_ASC])->all();
        $assets = $this->fetchAssets($id);

        return $this->render('publish', [
            'folder' => $folder,
            'folderName' => $this->resolveFolderName($folder),
            'folderDefaultSlug' => $this->defaultFolderSlug($folder),
            'publication' => $publication ?: new WebsitePublication(),
            'template' => $template,
            'definition' => $definition,
            'templates' => $templates,
            'assets' => $assets,
        ]);
    }

    public function actionPublicationDelete($id)
    {
        $publication = $this->findPublication($id);
        $publication->deleted = time();
        $publication->deleted_by_user_id = $this->currentUserId();
        $publication->save(false, ['deleted', 'deleted_by_user_id']);

        Yii::$app->session->setFlash('success', 'Published page removed. It will no longer be available publicly.');
        return $this->redirect(['templates']);
    }

    public function actionPage($uri)
    {
        $this->layout = false;
        $publication = WebsitePublication::findActive()
            ->where(['uri' => $uri])
            ->one();

        if (!$publication) {
            throw new NotFoundHttpException('Published page not found.');
        }

        if ((int) $publication->is_password_protected === 1 && !$this->hasPageAccess($publication)) {
            return $this->redirect(['page-password', 'uri' => $uri]);
        }

        return $this->render('page', [
            'publication' => $publication,
            'definition' => $publication->getSnapshotArray(),
            'values' => $publication->getValuesArray(),
            'folderName' => $this->resolveFolderNameFromId($publication->id),
        ]);
    }

    public function actionPagePassword($uri)
    {
        $this->layout = false;
        $publication = WebsitePublication::findActive()
            ->where(['uri' => $uri])
            ->one();

        if (!$publication) {
            throw new NotFoundHttpException('Published page not found.');
        }

        if ((int) $publication->is_password_protected !== 1) {
            return $this->redirect(['page', 'uri' => $uri]);
        }

        if (Yii::$app->request->isPost) {
            $password = (string) Yii::$app->request->post('page_password', '');

            if ($publication->validatePassword($password)) {
                Yii::$app->session->set($this->pageAccessSessionKey($publication), true);
                return $this->redirect(['page', 'uri' => $uri]);
            }

            Yii::$app->session->setFlash('error', 'Invalid password.');
        }

        return $this->render('password', [
            'publication' => $publication,
        ]);
    }

    protected function applyOwnershipScope($query, $alias = null)
    {
        $columnPrefix = $alias ? $alias . '.' : '';
        $customerId = $this->currentCustomerId();
        $userId = $this->currentUserId();

        if ($customerId) {
            $query->andWhere([$columnPrefix . 'customer_id' => $customerId]);
        } elseif ($userId) {
            $query->andWhere([$columnPrefix . 'user_id' => $userId]);
        }

        return $query;
    }

    protected function findTemplate($id)
    {
        $query = WebsiteTemplate::findActive()->andWhere(['id' => (int) $id]);
        $query = $this->applyOwnershipScope($query, WebsiteTemplate::tableName());
        $model = $query->one();

        if (!$model) {
            throw new NotFoundHttpException('Template not found.');
        }

        return $model;
    }

    protected function findPublication($id)
    {
        $query = WebsitePublication::findActive()->andWhere(['id' => (int) $id]);
        $query = $this->applyOwnershipScope($query, WebsitePublication::tableName());
        $model = $query->one();

        if (!$model) {
            throw new NotFoundHttpException('Publication not found.');
        }

        return $model;
    }

    protected function fetchFolder($folderId)
    {
        if ((int) $folderId <= 0) {
            return null;
        }

        return (new Query())
            ->from('folders')
            ->where(['id' => (int) $folderId])
            ->one();
    }

    protected function fetchAssets($folderId)
    {
        if ((int) $folderId <= 0) {
            return [];
        }

        $rows = (new Query())
            ->from('assets')
            ->where(['folder_id' => (int) $folderId])
            ->andWhere(['deleted' => null])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return array_map(function ($row) {
            return [
                'id' => $row['id'] ?? null,
                'title' => $row['title'] ?? $row['filename'] ?? ('Asset #' . ($row['id'] ?? '')),
                'filename' => $row['filename'] ?? $row['title'] ?? '',
                'preview_url' => $row['preview_url'] ?? '',
                'thumbnail_url' => $row['thumbnail_url'] ?? '',
                'file_type' => $row['file_type'] ?? $row['mime_type'] ?? '',
            ];
        }, $rows);
    }

    protected function resolveFolderNameFromId($folderId)
    {
        $folder = $this->fetchFolder($folderId);
        return $this->resolveFolderName($folder);
    }

    protected function resolveFolderName($folder)
    {
        if (!$folder || !is_array($folder)) {
            return 'Folder';
        }

        if (!empty($folder['name'])) {
            return (string) $folder['name'];
        }

        return 'Folder #' . ($folder['id'] ?? '');
    }

    protected function defaultFolderSlug($folder)
    {
        return Inflector::slug($this->resolveFolderName($folder), '-');
    }

    protected function currentUserId()
    {
        return (int) (Yii::$app->user->id ?? 0);
    }

    protected function currentCustomerId()
    {
        $identity = Yii::$app->user->identity ?? null;
        return (int) ($identity->customer_id ?? 0);
    }

    protected function pageAccessSessionKey(WebsitePublication $publication)
    {
        return 'published_page_access_' . $publication->id;
    }

    protected function hasPageAccess(WebsitePublication $publication)
    {
        return (bool) Yii::$app->session->get($this->pageAccessSessionKey($publication), false);
    }
}