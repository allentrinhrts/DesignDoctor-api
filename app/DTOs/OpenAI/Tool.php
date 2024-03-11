<?php

namespace App\DTOs\OpenAI;

class Tool
{
    /** @var string $type */
    private string $type;

    /** @var ToolFunction $function */
    private ToolFunction $function;

    function __construct(string $type, ToolFunction $function)
    {
        $this->type = $type;
        $this->function = $function;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFunction(): ToolFunction
    {
        return $this->function;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'function' => (object) $this->function->toArray(),
        ];
    }
}
