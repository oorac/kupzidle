<?php declare(strict_types=1);

namespace App\Doctrine\ORM\Query\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class PureSql extends FunctionNode
{
    /**
     * @var string
     */
    private string $statement = '';

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return $this->statement;
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

        $level = 0;
        $cursor = 0;
        do {
            if ($lexer->token['value'] === '(') {
                $level++;
            }

            if ($lexer->token['value'] === ')') {
                $level--;
            }

            if ($cursor && $cursor < $lexer->token['position']) {
                $this->statement .= ' ';
            }

            $this->statement .= $lexer->token['value'];
            $cursor = $lexer->token['position'] + strlen($lexer->token['value']);

            if ($level) {
                $lexer->moveNext();
            }
        } while ($level !== 0);
    }
}
