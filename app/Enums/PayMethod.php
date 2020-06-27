<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static Cash()
 * @method static Card()
 * @method static Debit()
 */
final class PayMethod extends Enum
{
    const Cash = 'cash';
    const Card = 'card';
    const Debit = 'debit';
}
