<?php

declare(strict_types=1);

namespace Hazadam\Phosphor\TwigParser;

use Generator;
use Hazadam\Phosphor\TwigParser\Node\MaskNode;
use Hazadam\Phosphor\TwigParser\Node\PropsNode;
use Hazadam\Phosphor\TwigParser\Node\VueComponentNode;
use Twig\Node\BlockReferenceNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\TempNameExpression;
use Twig\Node\Node;
use Twig\Node\SetNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class ComponentTokenParser extends AbstractTokenParser
{
    private static int $nesting = 0;

    public function parse(Token $token): Node
    {
        self::$nesting++;
        $lineno = $token->getLine();
        $bodyVariable = $this->parser->getVarName();
        $escapedBodyVariable = $this->parser->getVarName();
        $componentToken = $this->parser->getStream()->expect(Token::NAME_TYPE);
        $componentName = $this->normalizeName((string) $componentToken->getValue());
        $bodyRef = new TempNameExpression($bodyVariable, $lineno);
        $bodyRef->setAttribute('always_defined', true);
        $escapedBodyRef = new TempNameExpression($escapedBodyVariable, $lineno);
        $escapedBodyRef->setAttribute('always_defined', true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $props = [];
        $subNodes = $this->parser->subparse([$this, 'decideVueEnd'], true);
        [$shadowBody, $originalBody] = $this->parentPair($subNodes);

        foreach ($this->recursiveIterator($subNodes) as $set) {
            [$parentNode, $name, $node] = $set;
            [$shadowParentNode, $parentNode] = $this->parentPair($parentNode);

            if ($this->isPropsNode($node) && $node->getAttribute('nesting') === self::$nesting) {
                $props = array_merge($props, $node->getAttribute('sets'));
                $shadowParentNode->removeNode($name);
            } elseif ($this->isMaskNode($node) && $node->getAttribute('nesting') === self::$nesting) {
                $shadowParentNode->setNode($name, $node->getAttribute('mask'));
                $parentNode->setNode($name, $node->getAttribute('then'));
            } elseif ($this->isBlockJavascriptNode($node)) {
                $shadowParentNode->removeNode($name);
                $parentNode->removeNode($name);
            } else {
                $shadowParentNode->setNode($name, $node);
                $parentNode->setNode($name, $node);
            }
        }

        if (!$componentName) {
            throw new \RuntimeException('Component name could not have been resolved');
        }

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        $escapedBody = new FilterExpression(
            new TempNameExpression($bodyVariable, $lineno),
            new ConstantExpression('escape', $lineno),
            new Node([new ConstantExpression('js', $lineno)]),
            $lineno,
            $this->getTag()
        );

        self::$nesting--;

        return new Node(
            [
                new SetNode(true, $bodyRef, $originalBody, $lineno, $this->getTag()),
                new SetNode(false, $escapedBodyRef, $escapedBody, $lineno, $this->getTag()),
                new VueComponentNode([$shadowBody], [
                    'name' => $componentName,
                    'props' => $this->extractPropNames($props),
                    'template' => $escapedBodyVariable,
                ])
            ]
        );
    }

    public function decideVueEnd(Token $token): bool
    {
        return $token->test('endvue');
    }

    public function getTag(): string
    {
        return 'vue';
    }

    private function isPropsNode(Node $node): bool
    {
        return is_a($node, PropsNode::class);
    }

    private function isMaskNode(Node $node): bool
    {
        return is_a($node, MaskNode::class);
    }

    private function isBlockJavascriptNode(Node $node): bool
    {
        return is_a($node, BlockReferenceNode::class) && $node->getAttribute('name') === 'javascript';
    }

    public static function normalizeName(string $name): string
    {
        $componentName = strtolower((string) preg_replace("#([A-Z])#", '-$1', $name));
        if ($componentName[0] === '-') {
            $componentName = substr($componentName, 1);
        }
        return $componentName;
    }

    /**
     * @param SetNode[] $props
     * @return array<int, array{string, string}>
     */
    private function extractPropNames(array $props): array
    {
        return array_map(
            function (SetNode $set) {
                $nameNode = $set->getNode('names')->getNode('0');
                return [
                    (string) $nameNode->getAttribute('name'),
                    (string) $nameNode->getAttribute('original_name'),
                ];
            },
            $props
        );
    }

    /**
     * @param Node $node
     * @return Generator<int, array{Node, string, Node}>
     */
    private function recursiveIterator(Node $node): Generator
    {
        /**
         * @var string|int $name
         * @var Node $subNode
         */
        foreach ($node as $name => $subNode) {
            yield [$node, (string) $name, $subNode];
            if ($this->isMaskNode($subNode) ||
                $this->isPropsNode($subNode) ||
                ($subNode->hasAttribute('skip') && $subNode->getAttribute('skip'))
            ) {
                continue;
            }
            foreach ($this->recursiveIterator($subNode) as $set) {
                yield $set;
            }
        }
    }

    public static function nesting(): int
    {
        return self::$nesting;
    }

    /**
     * @param Node $parentNode
     * @return array{Node, Node}
     */
    private function parentPair(Node $parentNode): array
    {
        /** @var array<int, array{Node, Node}> $cache */
        static $cache = [];
        $nodeId = spl_object_id($parentNode);

        if (!isset($cache[$nodeId])) {
            $cache[$nodeId] = [clone $parentNode, $parentNode];
        }

        return $cache[$nodeId];
    }
}
