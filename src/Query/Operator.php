<?php

namespace ATernovtsii\SearchBundle\Query;

abstract class Operator
{
    public const EQ = '=';
    public const GT = '>';
    public const GTE = '>=';
    public const LT = '<';
    public const LTE = '<=';
    public const IN = 'in';
    public const BETWEEN = 'between';
    public const LIKE = 'like';
}
