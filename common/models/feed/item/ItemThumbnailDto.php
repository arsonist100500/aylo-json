<?php

namespace common\models\feed\item;

use common\models\DTObject;

class ItemThumbnailDto extends DTObject
{
    /** @var int */
    public $height;
    /** @var int */
    public $width;
    /**
     * @var string
     * @example "pc"
     */
    public $type;
    /** @var string[] */
    private array $urls = [];

    /**
     * @return string[]
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param string[] $urls
     */
    public function setUrls(array $urls): void
    {
        $this->urls = $urls;
    }

    /**
     * @return string|null
     */
    public function getFirstUrl(): ?string
    {
        if (empty($this->urls)) {
            return null;
        }
        return reset($this->urls);
    }
}
