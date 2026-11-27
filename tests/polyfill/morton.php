<?php

/**
 * Test-only stand-in for ext-morton's morton2d_encode(), used by Item::getStateId() (and so Item::equals()).
 * Interleaves the low 32 bits of both coordinates, which is what the extension does.
 */

declare(strict_types=1);

function morton2d_encode(int $x, int $y) : int{
    $result = 0;
    for($i = 0; $i < 32; ++$i){
        $result |= (($x >> $i) & 1) << (2 * $i);
        $result |= (($y >> $i) & 1) << (2 * $i + 1);
    }
    return $result;
}
