<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class KeepBottleDay extends Enum
{
    const Month =   30;
    const TwoMonth =   60;
    const ThreeMonths = 90;
    const SixMonths = 180;
    const Year = 365;
    const TwoYears = 730;
    const ThreeYears = 1095;
}
