<?php

namespace common\models\feed;

use common\models\feed\item\ItemDto;
use Generator;
use JsonStreamingParser\Listener\RegexListener;
use JsonStreamingParser\Parser;

class FeedParser
{
    /** @var string */
    protected string $data;
    /** @var resource */
    protected $stream;
    /** @var array */
    protected array $items = [];

    /**
     * @param resource $stream
     * @return $this
     */
    public function setStream($stream): static
    {
        $this->stream = $stream;
        return $this;
    }


    protected int $processedItemsCount = 0;
    protected int $skipped = 0;

    /**
     * @return Generator|ItemDto[]
     */
    public function getItems(int $batchSize = 100)
    {
        rewind($this->stream);
        $listener = new RegexListener();
        $parser = new Parser($this->stream, $listener);

        $this->items = [];
        $skipCount = $this->processedItemsCount;
        $this->skipped = 0;
        $listener->setMatch([
            '(/items/\d+)' => function ($data, $path) use ($batchSize, $parser, $skipCount) {
                if ($this->skipped < $skipCount) {
                    ++$this->skipped;
                    return;
                }
                if (count($this->items) >= $batchSize) {
                    $parser->stop();
                    return;
                }

                $dto = new ItemDto($data);
                $this->items[] = $dto;
                ++$this->processedItemsCount;
            },
        ]);

        $parser->parse();

        yield from $this->items;
    }
}
