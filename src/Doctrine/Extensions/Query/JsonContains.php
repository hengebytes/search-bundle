<?php

namespace ATSearchBundle\Doctrine\Extensions\Query;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

class JsonContains extends FunctionNode
{
    private ArithmeticExpression $expr1;
    private ArithmeticExpression $expr2;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expr1 = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->expr2 = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'JSON_CONTAINS('
            . $sqlWalker->walkArithmeticPrimary($this->expr1) . ', '
            . $sqlWalker->walkArithmeticPrimary($this->expr2) .
            ')';
    }
}