<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use common\models\Folder;

class FolderController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * POST /folder/add
     * Expected POST fields at minimum: parent_id, name
     * (Optionally: customer_id, user_id — or set them from the session/identity)
     */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Folder();

        // If you prefer to set tenant/user from session/identity, do it here:
        // $model->customer_id = Yii::$app->user->identity->customer_id;
        // $model->user_id = Yii::$app->user->id;

        // Load raw POST (no form name)
        $model->load(Yii::$app->request->post(), '');
        $model->customer_id = 1;
        $model->user_id = 1;

        // Minimal required defaults (if your DB doesn’t set them):
        if ($model->status === null) {
            $model->status = Folder::STATUS_ACTIVE;
        }

        $parentId = Yii::$app->request->post('parent_id');
        if (empty($parentId) || str_starts_with($parentId, 'j1_')) {
            $parentId = null;
        }

        $model->parent_id = $parentId;

        error_log("PARENT: -" . $model->parent_id . "-");

        if ($model->save()) {
            return [
                'ok'   => true,
                'node' => [
                    'id'     => (string)$model->id,
                    'text'   => $model->name,
                    'parent' => $model->parent_id ? (string)$model->parent_id : '#',
                    'icon'   => 'fa fa-folder',
                ],
                'message' => 'Folder created.',
            ];
        }

        error_log(print_r($model->errors, true));

        // Validation failed
        Yii::$app->response->statusCode = 422;
        return [
            'ok'     => false,
            'errors' => $model->getErrors(),
        ];
    }
}
