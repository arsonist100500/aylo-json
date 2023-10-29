<?php

namespace common\models\feed;


use common\models\feed\item\ItemDto;
use Generator;
use JsonException;

class FeedParser
{
    /** @var string */
    protected string $data;

    public function setData(string $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return Generator|ItemDto[]
     * @throws JsonException
     */
    public function getItems(): Generator
    {
        $parsed = json_decode($this->data, true, 512, JSON_THROW_ON_ERROR);
        $items = $parsed['items'] ?? [];
        foreach ($items as $item) {
            yield new ItemDto($item);
        }
    }
}
