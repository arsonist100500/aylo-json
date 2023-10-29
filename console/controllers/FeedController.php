<?php

namespace console\controllers;

use common\models\feed\FeedSettings;
use console\jobs\FeedDownloadJob;
use console\jobs\FeedImportJob;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\di\Instance;
use yii\queue\Queue;

class FeedController extends Controller
{
    /** @var string|array|Queue */
    public $queueDownload = 'queueDownload';
    /** @var string|array|Queue */
    public $queueImport = 'queueImport';

    /**
     * @return int
     * @throws InvalidConfigException
     */
    public function actionDownload(): int
    {
        $settings = FeedSettings::instance();

        $job = new FeedDownloadJob();
        $job->url = $settings->getUrl();
        $job->path = $settings->getFilePath();

        $this->getDownloadQueue()->push($job);

        return ExitCode::OK;
    }

    /**
     * @return int
     * @throws InvalidConfigException
     */
    public function actionImport(): int
    {
        $settings = FeedSettings::instance();

        $job = new FeedImportJob();
        $job->path = $settings->getFilePath();

        $this->getImportQueue()->push($job);

        return ExitCode::OK;
    }

    /**
     * @return Queue
     * @throws InvalidConfigException
     */
    public function getDownloadQueue(): Queue
    {
        if ($this->queueDownload instanceof Queue === false) {
            $this->queueDownload = Instance::ensure($this->queueDownload, Queue::class);
        }
        return $this->queueDownload;
    }

    /**
     * @return Queue
     * @throws InvalidConfigException
     */
    public function getImportQueue(): Queue
    {
        if ($this->queueImport instanceof Queue === false) {
            $this->queueImport = Instance::ensure($this->queueImport, Queue::class);
        }
        return $this->queueImport;
    }
}
