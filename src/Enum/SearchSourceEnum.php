<?php

namespace ATernovtsii\SearchBundle\Enum;

enum SearchSourceEnum: int
{
    case DOCTRINE = 1;
    case ELASTIC = 2;
}