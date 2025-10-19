<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use common\models\Folder;

class FolderController extends Controller{

    public function behaviors(){
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
     * Displays the folder tree with assets on the right panel
     * @return void
     */
    public function actionFolders(){
        $this->layout = "folder";
        return $this->render('folder');
    }


    /**
     * Adds a folder
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionAdd(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Folder();

        // If you prefer to set tenant/user from session/identity, do it here:
        // $model->customer_id = Yii::$app->user->identity->customer_id;
        // $model->user_id = Yii::$app->user->id;

        // Load raw POST (no form name)
        $user = \Yii::$app->user->identity;
        $model->load(Yii::$app->request->post(), '');
        $model->customer_id = $user->customer_id;
        $model->user_id = $user->id;

        // Minimal required defaults (if your DB doesn’t set them):
        if ($model->status === null) {
            $model->status = Folder::STATUS_ACTIVE;
        }

        $parentId = Yii::$app->request->post('parent_id');
        if (empty($parentId) || str_starts_with($parentId, 'j1_')) {
            $parentId = null;
        }

        $model->parent_id = $parentId;

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

        // Validation failed
        Yii::$app->response->statusCode = 422;
        error_log(print_r($model->getErrors(), 1));
        return [
            'ok'     => false,
            'errors' => $model->getErrors(),
        ];
    }

    /**
     * Moves a folder from one parent folder to a new one.
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionMove(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $id = $request->post('id');
        $parentId = $request->post('parent_id');

        // Normalize parent_id: empty string or non-numeric means NULL
        if ($parentId === '' || $parentId === null || !is_numeric($parentId)) {
            $parentId = null;
        }

        /** @var Folder $folder */
        $folder = Folder::findOne($id);
        if (!$folder) {
            return [
                'ok' => false,
                'message' => 'Folder not found.',
            ];
        }

        $folder->parent_id = $parentId;

        if ($folder->save()) {
            return [
                'ok' => true,
                'message' => 'Folder moved successfully.',
                'node' => [
                    'id' => (string)$folder->id,
                    'parent' => $folder->parent_id ? (string)$folder->parent_id : null,
                    'text' => $folder->name,
                ]
            ];
        }

        error_log(print_r($folder->getErrors(),1));

        return [
            'ok' => false,
            'message' => 'Failed to move folder.',
            'errors' => $folder->getErrors(),
        ];
    }

    /**
     * Renames a folder
     * @return array|bool[]
     * @throws \yii\db\Exception
     */
    public function actionRename(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $name = Yii::$app->request->post('name');

        $folder = Folder::findOne($id);
        if (!$folder) {
            return ['ok' => false, 'message' => 'Folder not found'];
        }

        $folder->name = $name;
        if ($folder->save()) {
            return ['ok' => true];
        }

        return [
            'ok' => false,
            'message' => 'Failed to rename folder',
            'errors' => $folder->getErrors(),
        ];
    }

    /**
     * Deletes a folder
     * @return array|bool[]
     * @throws \yii\db\Exception\
     */
    public function actionDelete(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $folder = Folder::findOne($id);

        if (!$folder) {
            return ['ok' => false, 'message' => 'Folder not found.'];
        }

        // logged in user
        $user = \Yii::$app->user->identity;

        $folder->status = Folder::STATUS_DELETED;
        $folder->deleted = date('Y-m-d H:i:s');
        $folder->deleted_by_user_id = $user->id;


        if ($folder->save(false)) {
            return ['ok' => true];
        }

        return ['ok' => false, 'message' => 'Failed to delete folder.'];
    }
}
