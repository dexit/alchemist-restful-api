<?php

namespace Nvmcommunity\Alchemist\RestfulApi\ResourcePaginations\OffsetPaginator\Handlers;

use Nvmcommunity\Alchemist\RestfulApi\ResourcePaginations\OffsetPaginator\Notifications\ResourceOffsetPaginationErrorBag;
use Nvmcommunity\Alchemist\RestfulApi\ResourcePaginations\OffsetPaginator\Objects\OffsetPaginateObject;

class ResourceOffsetPaginator
{
    /**
     * @var mixed
     */
    private $originalInput;

    /**
     * @var int
     */
    private int $defaultLimit = 0;
    /**
     * @var int
     */
    private int $maxLimit = 0;
    /**
     * @var int
     */
    private int $limit;
    /**
     * @var int
     */
    private int $offset;
    /**
     * @var string
     */
    private string $limitParam;
    /**
     * @var string
     */
    private string $offsetParam;

    /**
     * @param string $limitParam
     * @param string $offsetParam
     * @param array $input
     */
    public function __construct(string $limitParam, string $offsetParam, array $input)
    {
        $this->limitParam = $limitParam;
        $this->offsetParam = $offsetParam;
        $this->originalInput = $input;

        $this->limit = is_int($input[$limitParam]) ? $input[$limitParam] : 0;
        $this->offset = is_int($input[$offsetParam]) ? $input[$offsetParam] : 0;
    }

    /**
     * @param int $defaultLimit
     * @return ResourceOffsetPaginator
     */
    public function defineDefaultLimit(int $defaultLimit): self
    {
        $this->defaultLimit = $defaultLimit;

        return $this;
    }

    /**
     * @param int $maxLimit
     * @return ResourceOffsetPaginator
     */
    public function defineMaxLimit(int $maxLimit): self
    {
        $this->maxLimit = $maxLimit;

        return $this;
    }

    /**
     * @return OffsetPaginateObject
     */
    public function offsetPaginate(): OffsetPaginateObject
    {
        return new OffsetPaginateObject($this->limit ?: $this->defaultLimit ?: $this->maxLimit, $this->offset, $this->maxLimit);
    }

    /**
     * @param $notification
     * @return ResourceOffsetPaginationErrorBag
     */
    public function validate(&$notification = null): ResourceOffsetPaginationErrorBag
    {
        $passes = true;
        $isNegativeOffset = false;
        $maxLimitReached = false;
        $invalidInputTypes = [];

        if (! is_null($this->originalInput[$this->limitParam]) && ! is_int($this->originalInput[$this->limitParam])) {
            $invalidInputTypes[] = $this->limitParam;
        }

        if (! is_null($this->originalInput[$this->offsetParam]) && ! is_int($this->originalInput[$this->offsetParam])) {
            $invalidInputTypes[] = $this->offsetParam;
        }

        if (! empty($invalidInputTypes)) {
            $passes = false;
        }

        if ($this->maxLimit > 0 && $this->maxLimit < $this->limit) {
            $passes = false;
            $maxLimitReached = true;
        }

        if ($this->offset < 0) {
            $passes = false;
            $isNegativeOffset = true;
        }

        return $notification = new ResourceOffsetPaginationErrorBag($passes, $maxLimitReached, $isNegativeOffset, $invalidInputTypes);
    }
}