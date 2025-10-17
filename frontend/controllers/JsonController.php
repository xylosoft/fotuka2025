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
            $folders = Folder::find()
                ->where(['parent_id' => $parentId])
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
                    'opened' => ("#") // 👈 auto-open folder 24
                ],
                'children' => $buildTree(null),
                'icon' => 'fa fa-home'
            ]
        ];
    }
}
