<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class CustomerPerPage extends Enum
{
    const OptionOne =   20;
    const OptionTwo =   50;
    const OptionThree = 100;
    const OptionFour = 200;
}
