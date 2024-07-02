<?php declare(strict_types=1);

namespace App\Doctrine\ORM\Query\Functions;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\SimpleArithmeticExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class Rand extends FunctionNode
{
    /**
     * @var SimpleArithmeticExpression
     */
    private SimpleArithmeticExpression $expression;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        if (! empty($this->expression)) {
            return 'RAND(' . $this->expression->dispatch($sqlWalker) . ')';
        }

        return 'RAND()';
    }

    /**
     * @param Parser $parser
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $lexer = $parser->getLexer();
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        if (Lexer::T_CLOSE_PARENTHESIS !== $lexer->lookahead['type']) {
            $this->expression = $parser->SimpleArithmeticExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
