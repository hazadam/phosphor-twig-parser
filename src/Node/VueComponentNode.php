<?php

declare(strict_types=1);

namespace Hazadam\Phosphor\TwigParser\Node;

use Hazadam\Phosphor\TwigParser\ComponentTokenParser;
use Twig\Compiler;
use Twig\Node\Node;

final class VueComponentNode extends Node
{
    /**
     * @param Node[] $nodes
     * @param mixed[] $attributes
     * @param int $lineno
     * @param string|null $tag
     */
    public function __construct(array $nodes = [], array $attributes = [], int $lineno = 0, string $tag = null)
    {
        parent::__construct($nodes, $attributes, $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $componentName = $this->getAttribute('name');
        $template = '$_' . $this->getAttribute('template') . '_';
        $props = $this->normalizeProps($this->getAttribute('props'));
        $compiler->raw("echo \"");
        $compiler->raw(
            <<<COMPONENT
<$componentName $props :template=\"'$template'\">
COMPONENT
        );
        $compiler->raw("\";\n");
        foreach ($this->nodes as $node) {
            $compiler->subcompile($node);
        }
        $compiler->raw('echo ');
        $compiler->string("</{$this->getAttribute('name')}>");
        $compiler->raw(";\n");
    }

    /**
     * @param array<array{string, string}> $props
     * @return string
     */
    private function normalizeProps(array $props): string
    {
        $props = array_map(
            function (array $prop): string {
                [$variableName, $originalName] = $prop;
                $contextRef = '{$context[\'' . $variableName . '\']}';
                $attr = ':' . ComponentTokenParser::normalizeName($originalName);
                return "$attr=\\\"$contextRef\\\"";
            },
            $props
        );

        return implode(' ', $props);
    }
}
