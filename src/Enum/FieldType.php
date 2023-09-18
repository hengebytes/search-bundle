<?php

namespace ATSearchBundle\Enum;

enum FieldType: string
{
    case INTEGER = 'int';
    case MULTI_INTEGER = 'mint';
    case ID = 'id';
    case MULTI_ID = 'mid';
    case STRING = 'string';
    case MULTI_STRING = 'mstring';
    case LONG = 'long';
    case MULTI_LONG = 'mlong';
    case TEXT = 'text';
    case BOOLEAN = 'bool';
    case MULTI_BOOLEAN = 'mbool';
    case DATE = 'date';
    case FLOAT = 'float';
    case DOUBLE = 'double';
    case GEO_POINT = 'geo_point';
}
