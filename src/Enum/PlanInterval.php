<?php

namespace Coderstm\Enum;

enum PlanInterval: string
{
    case MONTH = 'month';
    case YEAR = 'year';
    case WEEK = 'week';
    case DAY = 'day';
}
