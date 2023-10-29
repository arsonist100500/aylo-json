<?php

namespace console\controllers;

use common\models\feed\FeedSettings;
use console\jobs\FeedDownloadJob;
use console\jobs\FeedImportJob;
use yii\console\Controller;
use yii\console\ExitCode;

class FeedController extends Controller
{
    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionDownload()
    {
        $settings = FeedSettings::instance();

        $job = new FeedDownloadJob();
        $job->url = $settings->getUrl();
        $job->path = $settings->getFilePath();

        $job->execute(null); // todo push to the queue

        return ExitCode::OK;
    }

    public function actionImport()
    {
        $settings = FeedSettings::instance();

        $job = new FeedImportJob();
        $job->path = $settings->getFilePath();

        $job->execute(null); // todo push to the queue

        return ExitCode::OK;
    }
}
