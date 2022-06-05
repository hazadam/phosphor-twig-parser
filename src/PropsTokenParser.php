<?php

declare(strict_types=1);

namespace Hazadam\Phosphor\TwigParser;

use Hazadam\Phosphor\TwigParser\Node\PropsNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use Twig\Node\SetNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class PropsTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decidePropsEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $sets = [];
        $nodes = [];
        /** @var Node $node */
        foreach ($body as $node) {
            if (is_a($node, SetNode::class)) {
                $nameNode = $node->getNode('names')->getNode('0');
                $originalName = $nameNode->getAttribute('name');
                $uniqueName = ComponentTokenParser::nesting() . '_' . $originalName;
                $nameNode->setAttribute('name', $uniqueName);
                $nameNode->setAttribute('original_name', $originalName);
                $jsonEncodeFilter = $this->jsonEncodeFilter($node->getNode('values'), $lineno);
                $htmlFilter = $this->htmlFilter($jsonEncodeFilter, $lineno);
                $node->setNode('values', $htmlFilter);
                $sets[] = $node;
            }
            $nodes[] = $node;
        }

        $attributes = [
            'sets' => $sets,
            'nesting' => ComponentTokenParser::nesting(),
        ];

        return new PropsNode([new Node($nodes)], $attributes, $lineno, $this->getTag());
    }

    public function getTag(): string
    {
        return 'props';
    }

    public function decidePropsEnd(Token $token): bool
    {
        return $token->test('endprops');
    }

    private function filter(string $name, Node $node, int $lineno): FilterExpression
    {
        return new FilterExpression(
            $node,
            new ConstantExpression($name, $lineno),
            new Node([]),
            $lineno,
            $this->getTag()
        );
    }

    private function htmlFilter(Node $node, int $lineno): FilterExpression
    {
        return $this->filter('e', $node, $lineno);
    }

    private function jsonEncodeFilter(Node $node, int $lineno): FilterExpression
    {
        return $this->filter('json_encode', $node, $lineno);
    }
}
