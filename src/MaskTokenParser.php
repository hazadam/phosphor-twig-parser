<?php

declare(strict_types=1);

namespace Hazadam\Phosphor\TwigParser;

use Hazadam\Phosphor\TwigParser\Node\MaskNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class MaskTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $mask = $this->parser->subparse([$this, 'decideThen'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $then = $this->parser->subparse([$this, 'decideMaskEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $attributes = ['mask' => $mask, 'then' => $then, 'nesting' => ComponentTokenParser::nesting()];

        return new MaskNode([], $attributes, $lineno, $this->getTag());
    }

    public function getTag(): string
    {
        return 'mask';
    }

    public function decideThen(Token $token): bool
    {
        return $token->test('then');
    }

    public function decideMaskEnd(Token $token): bool
    {
        return $token->test('endmask');
    }
}
