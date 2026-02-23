<?php

namespace frontend\controllers;

use yii\web\Controller;
use yii\web\Response;
use common\models\Folder;
use common\models\Asset;

class JsonController extends Controller{

    /**\
     * @param $id
     * @return array[]
     */
    public function actionFolders($id = null){
        $id = (!$id?null:$id);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        // Recursive function to build folder hierarchy
        $buildTree = function ($parentId = null, $id = null) use (&$buildTree) {
            $user = \Yii::$app->user->identity;
            $folders = Folder::find()
                ->where(['parent_id' => $parentId, 'status' => Folder::STATUS_ACTIVE, 'customer_id' => $user->customer_id])
                ->orderBy(['name' => SORT_ASC])
                ->all();

            $children = [];
            foreach ($folders as $folder) {
                $children[] = [
                    'id' => (string)$folder->id,
                    'text' => $folder->name,
                    'selected' => ($id == $folder->id?"true":"false"),
                    'icon' => 'fa fa-folder',
                    'children' => $buildTree($folder->id)
                ];
            }
            return $children;
        };

        // Root node "Home" (not from DB)
        return [
            [
                'id' => null,
                'text' => 'Home',
                'state' => [
                    'opened' => ("#"),
                    'selected' => (!$id?"true":"false"),
                ],
                'children' => $buildTree(null, $id),
                'icon' => 'fa fa-home'
            ]
        ];
    }

    public function actionFolder($id){
        $id = ($id==0?null:$id);
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $offset = (int)\Yii::$app->request->get('offset', 0);
        $limit  = (int)\Yii::$app->request->get('limit', 20);

        $root = null;
        if ($id){
            $root = Folder::findOne($id);
        }

        $query = Folder::find()
            ->select(['id', 'name', 'thumbnail_id'])
            ->where(['status' => 'active'])
            ->orderBy(['name' => SORT_ASC]);

        if ($id === null) {
            $query->andWhere(['is', 'parent_id', null]);
        } else {
            $query->andWhere(['parent_id' => $id]);
        }
        //error_log($query->createCommand()->getRawSql());
        $total = (clone $query)->count();

        if ($limit > 0) {
            $query->offset($offset)->limit($limit);
        }

        $rows = $query->asArray()->all();

        $subfolders = array_map(function ($folder) {
            return [
                'id' => $folder['id'],
                'name' => $folder['name'],
                'thumbnail' => $this->getThumbnailUrl($folder['thumbnail_id']),
            ];
        }, $rows);

        return [
            'ok' => true,
            'folder_name' => $root?$root->name:"Home",
            'subfolders' => $subfolders,
            'count' => count($subfolders),
            'total' => $total,
            'allLoaded' => ($limit === 0 || ($offset + count($subfolders)) >= $total),
        ];
    }


    public function actionAssets($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $offset = (int)\Yii::$app->request->get('offset', 0);
        $limit  = (int)\Yii::$app->request->get('limit', 25); // default 25

        $query = Asset::find()
            ->select(['id', 'title', 'thumbnail_url', 'thumbnail_state'])
            ->where(['folder_id' => $id, 'status' => 'active'])
            ->orderBy(['created' => SORT_DESC]);

        $total = (clone $query)->count();

        if ($limit > 0) {
            $query->offset($offset)->limit($limit);
        }

        $rows = $query->asArray()->all();

        $assets = array_map(function ($a) {
            return [
                'id' => $a['id'],
                'title' => $a['title'] ?: 'Untitled',
                'thumbnail_url' => $a['thumbnail_url'],
                'thumbnail_state' => $a['thumbnail_state'],
            ];
        }, $rows);

        return [
            'ok' => true,
            'assets' => $assets,
            'count' => count($assets),
            'total' => $total,
            'allLoaded' => ($limit === 0 || ($offset + count($assets)) >= $total),
        ];
    }


    private function getThumbnailUrl($thumbnailId){
        if (!$thumbnailId) {
            return null;
        }
        // Assuming your thumbnails table or folder stores file paths
        // Replace this with your actual logic later
        return '/uploads/thumbnails/' . $thumbnailId . '.jpg';
    }

    public function actionPending($assetIds){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $ids = array_filter(array_map('trim', explode(',', (string)$assetIds)));
        $ids = array_values(array_unique(array_filter($ids, function ($v) {
            return ctype_digit((string)$v);
        })));
        $ids = array_map('intval', $ids);

        error_log(print_r($ids, 1));

        if (empty($ids)) {
            return ['ok' => true, 'assets' => []];
        }

        // Assuming your model is app\models\Asset and thumbnail_url is a column
        $rows = Asset::find()
            ->select(['id', 'thumbnail_url'])
            ->where(['id' => $ids])
            ->asArray()
            ->all();

        // Return minimal payload
        $assets = array_map(function ($r) {
            return [
                'id' => (int)$r['id'],
                'thumbnail_url' => $r['thumbnail_url'] ?: null,
            ];
        }, $rows);

        return ['ok' => true, 'assets' => $assets];
    }
}
