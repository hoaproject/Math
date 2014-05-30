<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2014, Ivan Enderlin. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace {

from('Hoa')

/**
 * \Hoa\Math\Sampler\Random
 */
-> import('Math.Sampler.Random');
}

namespace Hoa\Math\Test\Unit\Sampler {

/**
 * Class \Hoa\Math\Test\Unit\Sampler\Random.
 *
 * Test suite of the random sampler.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2014 Ivan Enderlin.
 * @license    New BSD License
 */

class Random extends \Hoa\Test\Unit\Suite {

    public function case integer ( ) {

        $this
            ->if($sampler = new \Hoa\Math\Sampler\Random())
            ->then
                ->integer($sampler->getInteger());
    }

    public function case bounded integer ( ) {

        $this
            ->if($sampler = new \Hoa\Math\Sampler\Random())
            ->then
                ->integer($sampler->getInteger(-5, 5))
                    ->isGreaterThanOrEqualTo(-5)
                    ->isLessThanOrEqualTo(5)

                ->integer($sampler->getInteger(42, 42))
                    ->isEqualTo(42)
        ;
    }

    public function case optional bounds integer ( ) {

        $this
            ->if($sampler = new \Hoa\Math\Sampler\Random(array(
                'integer.min' => 42,
                'integer.max' => 42
            )))
            ->then
                ->integer($sampler->getInteger())
                    ->isEqualTo(42)
        ;
    }

    public function case excluded integers ( ) {

        $this
            ->if($sampler = new \Hoa\Math\Sampler\Random(),
                 $exclude = array())
            ->then
                ->integer($sampler->getInteger(0, 2, $exclude))
                    ->isGreaterThanOrEqualTo(0)
                    ->isLessThanOrEqualTo(2)

            ->if($exclude[] = 2)
            ->then
                ->integer($sampler->getInteger(0, 2, $exclude))
                    ->isGreaterThanOrEqualTo(0)
                    ->isLessThanOrEqualTo(1)

            ->if($exclude[] = 0)
            ->then
                ->integer($sampler->getInteger(0, 2, $exclude))
                    ->isEqualTo(1)
        ;
    }

    public function case uniformity integer ( ) {

        $max     = $this->sample($this->realdom()->boundinteger(1 << 18, 1 << 20));
        $sum     = 0;
        $upper   = 1 << 10;
        $sampler = new \Hoa\Math\Sampler\Random(array(
            'integer.min' => -$upper,
            'integer.max' =>  $upper
        ));

        for($i = 0; $i  < $max; ++$i)
            $sum += $sampler->getInteger();

        $this
            ->float($sum / $max)
                ->isGreaterThanOrEqualTo(-1.0)
                ->isLessThanOrEqualTo(1.0);
    }

    public function case float ( ) {

        $this
            ->if($sampler = new \Hoa\Math\Sampler\Random())
            ->then
                ->float($sampler->getFloat());
    }

    public function case bounded float ( ) {

        $this
            ->if($sampler = new \Hoa\Math\Sampler\Random())
            ->then
                ->float($sampler->getFloat(-5.5, 5.5))
                    ->isGreaterThanOrEqualTo(-5.5)
                    ->isLessThanOrEqualTo(5.5)

                ->float($sampler->getFloat(4.2, 4.2))
                    ->isEqualTo(4.2)
        ;
    }

    public function case optional bounds float ( ) {

        $this
            ->if($sampler = new \Hoa\Math\Sampler\Random(array(
                'float.min' => 4.2,
                'float.max' => 4.2
            )))
            ->then
                ->float($sampler->getFloat())
                    ->isEqualTo(4.2)
        ;
    }

    public function case uniformity float ( ) {

        $max     = $this->sample($this->realdom()->boundinteger(1 << 18, 1 << 20));
        $sum     = 0;
        $upper   = 1 << 10;
        $sampler = new \Hoa\Math\Sampler\Random(array(
            'float.min' => -$upper,
            'float.max' =>  $upper
        ));

        for($i = 0; $i  < $max; ++$i)
            $sum += $sampler->getFloat();

        $this
            ->float($sum / $max)
                ->isGreaterThanOrEqualTo(-1.0)
                ->isLessThanOrEqualTo(1.0);
    }
}

}
