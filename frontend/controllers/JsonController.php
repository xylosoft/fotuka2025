<?php

namespace frontend\controllers;

use yii\web\Controller;
use yii\web\Response;
use common\models\Folder;

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
}
