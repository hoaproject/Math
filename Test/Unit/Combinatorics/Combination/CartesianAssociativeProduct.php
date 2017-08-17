<?php

namespace Hoa\Math\Test\Unit\Sampler\Combinatorics\Combination;

use Hoa\Math\Combinatorics\Combination\CartesianAssociativeProduct as CAT;
use Hoa\Test;

/**
 * Class \Hoa\Math\Test\Unit\Sampler\Combinatorics\Combination\CartesianAssociativeProduct.
 *
 * Test suite of the cartesian product.
 *
 * @author     Karoly Negyesi <karoly@negyesi.net>
 * @copyright  Copyright © 2014 Karoly Negyesi.
 * @license    New BSD License
 */

class CartesianAssociativeProduct extends Test\Unit\Suite {

    public function case_empty ( ) {

        $this
            ->given($iterator = new CAT([]))
            ->when($result = iterator_to_array($iterator))
            ->then
                ->array($result)
                    ->isEqualTo([[null]]);
    }

    public function case_X ( ) {

        $this
            ->given($iterator = new CAT([[1], [2], [3]]))
            ->when($result = iterator_to_array($iterator))
            ->then
                ->array($result)
                    ->isEqualTo([
                        [1],
                        [2],
                        [3]
                    ]);
    }

    public function case_X_Y ( ) {

        $this
            ->given($iterator = new CAT(['X' => [1, 2, 3], 'Y' => [4, 5, 6]]))
            ->when($result = iterator_to_array($iterator))
            ->then
                ->array($result)
                    ->isEqualTo([
                        ['X' => 1, 'Y' => 4],
                        ['X' => 2, 'Y' => 4],
                        ['X' => 3, 'Y' => 4],

                        ['X' => 1, 'Y' => 5],
                        ['X' => 2, 'Y' => 5],
                        ['X' => 3, 'Y' => 5],

                        ['X' => 1, 'Y' => 6],
                        ['X' => 2, 'Y' => 6],
                        ['X' => 3, 'Y' => 6]
                    ]);
    }

    public function case_X_Y_Z ( ) {

        $this
            ->given($iterator = new CAT(['X' => [1, 2, 3], 'Y' => [4, 5, 6], 'Z' => [7, 8, 9]]))
            ->when($result = iterator_to_array($iterator))
            ->then
                ->array($result)
                    ->isEqualTo([
                        ['X' => 1, 'Y' => 4, 'Z' => 7],
                        ['X' => 2, 'Y' => 4, 'Z' => 7],
                        ['X' => 3, 'Y' => 4, 'Z' => 7],
                        ['X' => 1, 'Y' => 5, 'Z' => 7],
                        ['X' => 2, 'Y' => 5, 'Z' => 7],
                        ['X' => 3, 'Y' => 5, 'Z' => 7],
                        ['X' => 1, 'Y' => 6, 'Z' => 7],
                        ['X' => 2, 'Y' => 6, 'Z' => 7],
                        ['X' => 3, 'Y' => 6, 'Z' => 7],

                        ['X' => 1, 'Y' => 4, 'Z' => 8],
                        ['X' => 2, 'Y' => 4, 'Z' => 8],
                        ['X' => 3, 'Y' => 4, 'Z' => 8],
                        ['X' => 1, 'Y' => 5, 'Z' => 8],
                        ['X' => 2, 'Y' => 5, 'Z' => 8],
                        ['X' => 3, 'Y' => 5, 'Z' => 8],
                        ['X' => 1, 'Y' => 6, 'Z' => 8],
                        ['X' => 2, 'Y' => 6, 'Z' => 8],
                        ['X' => 3, 'Y' => 6, 'Z' => 8],

                        ['X' => 1, 'Y' => 4, 'Z' => 9],
                        ['X' => 2, 'Y' => 4, 'Z' => 9],
                        ['X' => 3, 'Y' => 4, 'Z' => 9],
                        ['X' => 1, 'Y' => 5, 'Z' => 9],
                        ['X' => 2, 'Y' => 5, 'Z' => 9],
                        ['X' => 3, 'Y' => 5, 'Z' => 9],
                        ['X' => 1, 'Y' => 6, 'Z' => 9],
                        ['X' => 2, 'Y' => 6, 'Z' => 9],
                        ['X' => 3, 'Y' => 6, 'Z' => 9]
                    ]);
    }

}
