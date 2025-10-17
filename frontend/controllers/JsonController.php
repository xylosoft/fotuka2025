<?php

namespace frontend\controllers;

use yii\web\Controller;
use yii\web\Response;

class JsonController extends Controller
{
    public function actionFolders()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        // Hardcoded example data
        return [
            [
                "id" => "1",
                "text" => "Root Folder",
                "children" => [
                    [ "id" => "2", "text" => "Photos", "children" => [
                        [ "id" => "5", "text" => "Blah" ],
                    ]],
                    [ "id" => "3", "text" => "Videos" ],
                    [ "id" => "4", "text" => "Documents" ]
                ]
            ]
        ];
    }
}
