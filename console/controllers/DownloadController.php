<?php

namespace console\controllers;

use yii\console\Controller;
use yii\console\ExitCode;

class DownloadController extends Controller
{
    public function actionFeed(string $url = 'http://localhost:8000/feed/feed100.json')
    {
        echo $url . "\n";
        return ExitCode::OK;
    }
}
