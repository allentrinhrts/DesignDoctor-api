<?php

namespace App\DTOs\OpenAI;

class ToolFunction
{
    /** @var string $name */
    private string $name;

    /** @var string $description */
    private string $description;

    /** @var Parameters $parameters */
    private Parameters $parameters;

    function __construct(string $name, string $description, Parameters $parameters)
    {
        $this->name = $name;
        $this->description = $description;
        $this->parameters = $parameters;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'parameters' => (object) $this->parameters->toArray(),
        ];
    }
}
