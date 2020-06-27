<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class OrderName extends Enum
{
    const OptionOne =   'name';
    const OptionTwo =   'bottle_name';
    const OptionThree = 'keep_bottle_day_limit';
    const OptionFour = 'company_name';
    const OptionFive = 'in_charge_cast_id';
    const OptionSix = 'last_arrival_time';
}
