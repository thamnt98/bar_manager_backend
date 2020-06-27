<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class OrderHistoryType extends Enum
{
    const OptionOne =   'free';
    const OptionTwo =   'shimei';
    const OptionThree = 'honnaishimei';
    const OptionFour = 'douhan';
}
