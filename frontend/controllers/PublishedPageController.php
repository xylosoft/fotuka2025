<?php

namespace frontend\controllers;

use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use common\models\Asset;
use common\models\File;
use common\models\Folder;
use common\models\Customer;
use common\models\Template;
use common\models\TemplateSection;
use common\models\PublishedPage;
use common\models\PublishedPageCustomFieldValue;
use common\models\PublishedPageSectionAsset;

class PublishedPageController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['publish'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionPublish($folder_id)
    {
        $user = Yii::$app->user->identity;

        $folder = Folder::findOne([
            'id' => $folder_id,
            'customer_id' => $user->customer_id,
            'deleted' => null,
        ]);

        if (!$folder) {
            throw new NotFoundHttpException('Folder not found.');
        }

        $templates = Template::find()
            ->where([
                'customer_id' => $user->customer_id,
                'deleted' => null,
            ])
            ->with(['sections.customField', 'customFields'])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $imageAssets = Asset::find()
            ->alias('a')
            ->joinWith('file f')
            ->where([
                'a.customer_id' => $user->customer_id,
                'a.folder_id' => $folder_id,
                'a.status' => 'active',
                'f.type' => File::TYPE_IMAGE,
            ])
            ->orderBy(['a.id' => SORT_ASC])
            ->all();

        $page = new PublishedPage();
        $page->folder_id = $folder_id;
        $page->customer_id = $user->customer_id;
        $page->user_id = $user->id;

        // Default values
        if (empty($page->page_title)) {
            $page->page_title = $folder->name;
        }

        if (empty($page->uri)) {
            $page->uri = urlencode($folder->name);
        }
        $page->uri = strtolower($page->uri);


        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $page->load($post);

            $template = Template::findOne([
                'template_id' => $page->template_id,
                'customer_id' => $user->customer_id,
                'deleted' => null,
            ]);

            if (!$template) {
                throw new NotFoundHttpException('Template not found.');
            }

            $page->folder_id = $folder_id;
            $page->customer_id = $user->customer_id;
            $page->user_id = $user->id;
            $page->password_required = (int)$template->password_enabled;
            $page->allow_downloads = (int)$template->allow_downloads;
            $page->published_at = date('Y-m-d H:i:s');

            if ($page->password_required && empty($page->page_password)) {
                $page->addError('page_password', 'Password is required for this template.');
            }

            if ($page->validate()) {
                $tx = Yii::$app->db->beginTransaction();
                try {
                    $page->save(false);

                    $customFieldValues = $post['custom_field_values'] ?? [];
                    foreach ($template->customFields as $field) {
                        $value = new PublishedPageCustomFieldValue();
                        $value->published_page_id = $page->published_page_id;
                        $value->custom_field_id = $field->custom_field_id;
                        $value->value = $customFieldValues[$field->custom_field_id] ?? null;
                        $value->save(false);
                    }

                    $selectedAssets = $post['section_assets'] ?? [];
                    foreach ($template->sections as $section) {
                        if (!$section->requiresAssetSelection()) {
                            continue;
                        }

                        $assetIds = $selectedAssets[$section->section_id] ?? [];
                        if (!is_array($assetIds)) {
                            $assetIds = [$assetIds];
                        }

                        $assetIds = array_values(array_filter(array_map('intval', $assetIds)));

                        if ($section->type === TemplateSection::TYPE_HEADER_IMAGE && count($assetIds) > 1) {
                            $assetIds = array_slice($assetIds, 0, 1);
                        }

                        if ($section->type === TemplateSection::TYPE_SINGLE_IMAGE && $section->image_count > 0) {
                            $assetIds = array_slice($assetIds, 0, $section->image_count);
                        }

                        if ($section->type === TemplateSection::TYPE_IMAGE_CAROUSEL && $section->image_count > 0) {
                            $assetIds = array_slice($assetIds, 0, $section->image_count);
                        }

                        $slot = 1;
                        foreach ($assetIds as $assetId) {
                            $row = new PublishedPageSectionAsset();
                            $row->published_page_id = $page->published_page_id;
                            $row->section_id = $section->section_id;
                            $row->asset_id = $assetId;
                            $row->slot_no = $slot++;
                            $row->save(false);
                        }
                    }

                    $tx->commit();
                    Yii::$app->session->setFlash('success', 'Page published successfully.');
                    return $this->redirect(['/pages/' . $page->uri]);
                } catch (\Throwable $e) {
                    $tx->rollBack();
                    throw $e;
                }
            }
        }

        $templatesPayload = [];
        foreach ($templates as $template) {
            $templatesPayload[$template->template_id] = [
                'template_id' => $template->template_id,
                'name' => $template->name,
                'password_enabled' => (int)$template->password_enabled,
                'allow_downloads' => (int)$template->allow_downloads,
                'custom_fields' => array_map(function($field) {
                    return [
                        'custom_field_id' => $field->custom_field_id,
                        'name' => $field->name,
                        'slug' => $field->slug,
                        'text_color' => $field->text_color,
                        'font_size' => $field->font_size,
                        'font_weight' => $field->font_weight,
                        'font_style' => $field->font_style,
                        'text_align' => $field->text_align,
                    ];
                }, $template->customFields),
                'sections' => array_map(function($section) {
                    return [
                        'section_id' => $section->section_id,
                        'type' => $section->type,
                        'label' => $section->label,
                        'text' => $section->text,
                        'image_count' => $section->image_count,
                        'custom_field_id' => $section->custom_field_id,
                        'custom_field_name' => $section->customField ? $section->customField->name : null,
                        'settings' => $section->getSettings(),
                        'requires_assets' => $section->requiresAssetSelection(),
                    ];
                }, $template->sections),
            ];
        }

        $assetsPayload = array_map(function($asset) {
            return [
                'id' => $asset->id,
                'title' => $asset->title,
                'preview_url' => $asset->preview_url,
                'thumbnail_url' => $asset->thumbnail_url,
                'filename' => $asset->file ? $asset->file->filename : null,
            ];
        }, $imageAssets);

        return $this->render('publish', [
            'folder' => $folder,
            'page' => $page,
            'templates' => $templates,
            'templatesPayload' => $templatesPayload,
            'assetsPayload' => $assetsPayload,
        ]);
    }

    public function actionPassword($uri)
    {
        $page = $this->findPublishedPageByUri($uri);

        if (!$page->password_required) {
            return $this->redirect(['/pages/' . $uri]);
        }

        if (Yii::$app->request->isPost) {
            $password = Yii::$app->request->post('page_password');

            if ((string)$password === (string)$page->page_password) {
                Yii::$app->session->set('published_page_access_' . $page->published_page_id, true);
                return $this->redirect(['/pages/' . $uri]);
            }

            Yii::$app->session->setFlash('error', 'Invalid password.');
        }

        return $this->render('password', [
            'page' => $page,
        ]);
    }

    public function actionView($uri)
    {
        $page = $this->findPublishedPageByUri($uri);

        if ($page->password_required && !Yii::$app->session->get('published_page_access_' . $page->published_page_id)) {
            return $this->redirect(['/pages/' . $uri . '/password']);
        }

        $template = $page->template;
        if (!$template || $template->deleted) {
            throw new NotFoundHttpException('Template not found.');
        }

        $folder = Folder::findOne($page->folder_id);
        if (!$folder) {
            throw new NotFoundHttpException('Folder not found.');
        }

        $customer = Customer::findOne($page->customer_id);

        $allImageAssets = Asset::find()
            ->alias('a')
            ->joinWith('file f')
            ->where([
                'a.customer_id' => $page->customer_id,
                'a.folder_id' => $page->folder_id,
                'a.status' => 'active',
                'f.type' => File::TYPE_IMAGE,
            ])
            ->orderBy(['a.id' => SORT_ASC])
            ->all();

        $selectedRows = $page->sectionAssets;
        $selectedAssetsBySection = [];
        foreach ($selectedRows as $row) {
            if ($row->asset) {
                $selectedAssetsBySection[$row->section_id][] = $row->asset;
            }
        }

        $valuesByCustomField = [];
        foreach ($page->customFieldValues as $value) {
            $valuesByCustomField[$value->custom_field_id] = $value->value;
        }

        return $this->render('view', [
            'page' => $page,
            'template' => $template,
            'folder' => $folder,
            'customer' => $customer,
            'allImageAssets' => $allImageAssets,
            'selectedAssetsBySection' => $selectedAssetsBySection,
            'valuesByCustomField' => $valuesByCustomField,
            'theme' => $template->getThemeSettings(),
        ]);
    }

    public function actionDownloadAll($uri)
    {
        $page = $this->findPublishedPageByUri($uri);

        if ($page->password_required && !Yii::$app->session->get('published_page_access_' . $page->published_page_id)) {
            return $this->redirect(['/pages/' . $uri . '/password']);
        }

        if (!$page->allow_downloads) {
            throw new ForbiddenHttpException('Downloads are not enabled for this page.');
        }

        // TODO:
        // 1. Check whether a ZIP already exists in the download bucket.
        // 2. If not, queue/generate it on demand.
        // 3. Return a redirect to the signed download URL.
        // 4. Optionally show a “preparing download” page while the ZIP is built.

        throw new \yii\web\ServerErrorHttpException('Download All is not wired yet. This is the placeholder endpoint for the S3 ZIP integration.');
    }

    protected function findPublishedPageByUri($uri)
    {
        $page = PublishedPage::findOne([
            'uri' => $uri,
            'deleted' => null,
        ]);

        if (!$page) {
            throw new NotFoundHttpException('Published page not found.');
        }

        return $page;
    }
}