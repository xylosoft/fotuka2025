<?php

namespace frontend\controllers;

use yii\web\Controller;
use yii\web\Response;
use common\models\Folder;
use common\models\Asset;

class JsonController extends Controller
{
    public function actionFolders()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        // Recursive function to build folder hierarchy
        $buildTree = function ($parentId = null) use (&$buildTree) {
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
                    'children' => $buildTree($folder->id),
                    'icon' => 'fa fa-folder'
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
                    'selected' => true,
                ],
                'children' => $buildTree(null),
                'icon' => 'fa fa-home'
            ]
        ];
    }

    public function actionFolder($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $offset = (int)\Yii::$app->request->get('offset', 0);
        $limit  = (int)\Yii::$app->request->get('limit', 20);

        $root = Folder::findOne($id);
        $query = Folder::find()
            ->select(['id', 'name', 'thumbnail_id'])
            ->where(['parent_id' => $id, 'status' => 'active'])
            ->orderBy(['name' => SORT_ASC]);

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
            'folder_name' => $root->name,
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
            ->select(['id', 'title', 'thumbnail_url'])
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
                'thumbnail_url' => $a['thumbnail_url'] ?: '/images/no-thumbnail.png',
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
}
