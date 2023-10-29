<?php

namespace common\models\feed\item;

use common\models\DTObject;

class ItemAttributesDto extends DTObject
{
    /**
     * @var string
     * @example "Blonde"
     */
    public $hairColor;
    /**
     * @var string
     * @example "White"
     */
    public $ethnicity;
    /** @var bool */
    public $tattoos;
    /** @var bool */
    public $piercings;
    /** @var int */
    public $breastSize;
    /**
     * @var string
     * @example "E"
     */
    public $breastType;
    /**
     * @var string
     * @example "female"
     */
    public $gender;
    /**
     * @var string
     * @example "straight"
     */
    public $orientation;
    /** @var int */
    public $age;
    /** @var ItemStatsDto|null */
    private ?ItemStatsDto $stats = null;

    /**
     * @return ItemStatsDto|null
     */
    public function getStats(): ?ItemStatsDto
    {
        return $this->stats;
    }

    /**
     * @param array $stats
     */
    public function setStats(array $stats): void
    {
        $this->stats = new ItemStatsDto($stats);
    }
}
