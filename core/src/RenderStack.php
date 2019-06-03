<?php

class RenderStack
{
    /** @var \RenderStack */
    public $parent;

    /** @var array */
    public $stack = [];

    public function __construct(RenderStack $parent = null)
    {
        $this->parent = $parent;
        if ($parent && isset($parent->name))
        {
            $this->parentName = $parent->name;
        }
    }

    public function appendString(string $item = null, string $key = null)
    {
        // Save entry event empty if key is found,
        // it set a placeholder for future block replacement.
        if ($item || $key)
        {
            $key !== null ?
                $this->stack[$key] = $item :
                $this->stack[] = $item;
        }
    }

    public function __toString(): string
    {
        return implode($this->stack);
    }
}