<?php

namespace console\controllers;

use common\models\FeedSettings;
use console\jobs\FeedDownloadJob;
use yii\console\Controller;
use yii\console\ExitCode;

class DownloadController extends Controller
{
    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionFeed()
    {
        $settings = FeedSettings::instance();

        $job = new FeedDownloadJob();
        $job->url = $settings->getUrl();
        $job->path = $settings->getFilePath();

        $job->execute(null); // todo push to the queue

        return ExitCode::OK;
    }
}
