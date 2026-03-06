<?php
namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use common\models\Template;
use common\models\TemplateSection;
use common\models\TemplateCustomField;

class TemplateController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'editor', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = "folder";
        $user = Yii::$app->user->identity;

        $templates = Template::find()
            ->where([
                'customer_id' => $user->customer_id,
                'deleted' => null,
            ])
            ->orderBy(['template_id' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'templates' => $templates,
        ]);
    }

    public function actionEditor($id = null)
    {
        $this->layout = "folder";
        $user = Yii::$app->user->identity;

        if ($id) {
            $template = Template::findOne([
                'template_id' => $id,
                'customer_id' => $user->customer_id,
                'deleted' => null,
            ]);

            if (!$template) {
                throw new NotFoundHttpException('Template not found.');
            }
        } else {
            $template = new Template();
            $template->customer_id = $user->customer_id;
            $template->user_id = $user->id;
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            $template->load($post);
            $template->customer_id = $user->customer_id;
            $template->user_id = $user->id;
            $template->theme_json = $post['theme_json'] ?? '{}';

            if ($template->save()) {
                $sections = json_decode($post['sections_json'] ?? '[]', true);
                $customFields = json_decode($post['custom_fields_json'] ?? '[]', true);

                $this->syncCustomFields($template, $customFields, $user);
                $this->syncSections($template, $sections, $user);

                Yii::$app->session->setFlash('success', 'Template saved successfully.');
                return $this->redirect(['template/editor', 'id' => $template->template_id]);
            }
        }

        $sectionsPayload = [];
        foreach ($template->sections as $section) {
            $sectionsPayload[] = [
                'section_id' => $section->section_id,
                'section_key' => $section->section_key,
                'type' => $section->type,
                'label' => $section->label,
                'row_no' => $section->row_no,
                'width' => $section->width,
                'height' => $section->height,
                'sort_order' => $section->sort_order,
                'is_locked' => (int)$section->is_locked,
                'text' => $section->text,
                'custom_field_id' => $section->custom_field_id,
                'background_color' => $section->background_color,
                'text_color' => $section->text_color,
                'image_count' => $section->image_count,
                'settings' => $section->getSettings(),
            ];
        }

        $customFieldsPayload = [];
        foreach ($template->customFields as $field) {
            $customFieldsPayload[] = [
                'custom_field_id' => $field->custom_field_id,
                'field_key' => $field->field_key,
                'name' => $field->name,
                'slug' => $field->slug,
                'text_color' => $field->text_color,
                'font_size' => $field->font_size,
                'font_weight' => $field->font_weight,
                'font_style' => $field->font_style,
                'text_align' => $field->text_align,
                'sort_order' => $field->sort_order,
            ];
        }

        return $this->render('editor', [
            'template' => $template,
            'theme' => $template->getThemeSettings(),
            'sectionsPayload' => $sectionsPayload,
            'customFieldsPayload' => $customFieldsPayload,
        ]);
    }

    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $template = Template::findOne([
            'template_id' => $id,
            'customer_id' => $user->customer_id,
            'deleted' => null,
        ]);

        if (!$template) {
            throw new NotFoundHttpException('Template not found.');
        }

        $template->deleted = date('Y-m-d H:i:s');
        $template->deleted_by_user_id = $user->id;
        $template->save(false, ['deleted', 'deleted_by_user_id', 'updated_at', 'updated_by_user_id']);

        Yii::$app->session->setFlash('success', 'Template deleted successfully.');
        return $this->redirect(['template/index']);
    }

    protected function syncCustomFields(Template $template, array $rows, $user)
    {
        TemplateCustomField::deleteAll(['template_id' => $template->template_id]);

        $sort = 1;
        foreach ($rows as $row) {
            if (empty($row['name'])) {
                continue;
            }

            $field = new TemplateCustomField();
            $field->template_id = $template->template_id;
            $field->customer_id = $template->customer_id;
            $field->user_id = $user->id;
            $field->field_key = $row['field_key'] ?: Yii::$app->security->generateRandomString(32);
            $field->name = trim($row['name']);
            $field->slug = trim($row['slug'] ?: $this->slugify($row['name']));
            $field->text_color = $row['text_color'] ?? '#111827';
            $field->font_size = (int)($row['font_size'] ?? 16);
            $field->font_weight = $row['font_weight'] ?? '400';
            $field->font_style = $row['font_style'] ?? 'normal';
            $field->text_align = $row['text_align'] ?? 'left';
            $field->sort_order = $sort++;
            $field->save();
        }
    }

    protected function syncSections(Template $template, array $rows, $user)
    {
        TemplateSection::deleteAll(['template_id' => $template->template_id]);

        $fieldMap = [];
        foreach ($template->customFields as $field) {
            $fieldMap[$field->field_key] = $field->custom_field_id;
        }

        $sort = 1;
        foreach ($rows as $row) {
            if (empty($row['type'])) {
                continue;
            }

            $settings = $row['settings'] ?? [];
            $customFieldId = null;
            if (!empty($row['custom_field_key']) && isset($fieldMap[$row['custom_field_key']])) {
                $customFieldId = $fieldMap[$row['custom_field_key']];
            } elseif (!empty($row['custom_field_id'])) {
                $customFieldId = (int)$row['custom_field_id'];
            }

            $section = new TemplateSection();
            $section->template_id = $template->template_id;
            $section->customer_id = $template->customer_id;
            $section->user_id = $user->id;
            $section->section_key = $row['section_key'] ?: Yii::$app->security->generateRandomString(32);
            $section->type = $row['type'];
            $section->label = $row['label'] ?? ucfirst(str_replace('_', ' ', $row['type']));
            $section->row_no = (int)($row['row_no'] ?? 1);
            $section->width = (int)($row['width'] ?? 12);
            $section->height = $row['height'] !== '' ? (int)$row['height'] : null;
            $section->sort_order = $sort++;
            $section->is_locked = !empty($row['is_locked']) ? 1 : 0;
            $section->text = $row['text'] ?? null;
            $section->custom_field_id = $customFieldId;
            $section->background_color = $row['background_color'] ?? null;
            $section->text_color = $row['text_color'] ?? null;
            $section->image_count = (int)($row['image_count'] ?? 0);
            $section->settings_json = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $section->save();
        }
    }

    protected function slugify($value)
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }
}