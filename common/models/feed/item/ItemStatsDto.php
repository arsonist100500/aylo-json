<?php

namespace common\models\feed\item;

use common\models\DTObject;

class ItemStatsDto extends DTObject
{
    /** @var int */
    public $subscriptions;
    /** @var int */
    public $monthlySearches;
    /** @var int */
    public $views;
    /** @var int */
    public $videosCount;
    /** @var int */
    public $premiumVideosCount;
    /** @var int */
    public $whiteLabelVideoCount;
    /** @var int */
    public $rank;
    /** @var int */
    public $rankPremium;
    /** @var int */
    public $rankWl;
}
