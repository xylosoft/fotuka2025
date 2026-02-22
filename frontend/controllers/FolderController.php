<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use common\models\Folder;
use common\models\Asset;

class FolderController extends Controller{

    public $enableCsrfValidation = false;

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
    public function actionFolders($id = null){
        $this->layout = "folder";
        $this->view->params['id'] = $id;
        $folder = null;
        if ($id){
            $folder = Folder::findOne($id);
        }

        return $this->render('folder', [
            'id' => $id,'folder' => $folder
        ]);
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
                'message' => 'Folder not found1.',
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
        return [
            'ok' => false,
            'message' => 'A folder with this name already exists in this location.',
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
            return ['ok' => false, 'message' => 'Folder not found2'];
        }

        $folder->name = $name;
        if ($folder->save()) {
            return ['ok' => true];
        }

        return [
            'ok' => false,
            'message' => 'A folder with this name already exists in this location.',
            'errors' => $folder->getErrors(),
        ];
    }

    /**
     * Deletes a folder and all its subfolders/assets recursively
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionDelete(){
        error_log("Deleting Folder");
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');

        error_log("Folder to delete: $id");
        $folder = Folder::findOne($id);
        if (!$folder) {
            error_log("Folder not found...");
            return ['ok' => false, 'message' => 'Folder not found.'];
        }

        $user = Yii::$app->user->identity;

        // recursively mark deleted
        try {
            $this->markFolderAndChildrenDeleted($folder, $user->id);
            return ['ok' => true];
        } catch (\Throwable $e) {
            error_log("Exception");
            error_log('Failed recursive delete: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Failed to delete folder tree.'];
        }
    }


    /**
     * Recursively mark folders (and their assets) as deleted.
     *
     * @param int $folderId
     * @param int $userId
     * @return void
     * @throws \yii\db\Exception
     */
    private function markFolderAndChildrenDeleted($folder, $userId)
    {
        $now = date('Y-m-d H:i:s');
        
        $folder->status = Folder::STATUS_DELETED;
        $folder->deleted = $now;
        $folder->deleted_by_user_id = $userId;
        $folder->save(false);

        // Mark all assets under this folder as deleted
        Asset::updateAll(
            [
                'status' => Asset::STATUS_DELETED,
                'deleted' => $now,
                'deleted_by_user_id' => $userId,
            ],
            ['folder_id' => $folder->id]
        );

        // Find and recursively mark all subfolders
        $children = Folder::find()
            ->select('id')
            ->where(['parent_id' => $folder->id])
            ->andWhere(['!=', 'status', Folder::STATUS_DELETED])
            ->all();

        foreach ($children as $child) {
            error_log("Also deleting: folder:" . $child->id);
            $this->markFolderAndChildrenDeleted($child, $userId);
        }
    }
}
