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

-> import('Math.Visitor.Arithmetic')

-> import('Compiler.Llk.~')

-> import('Compiler.Llk.Sampler.BoundedExhaustive')

-> import('Regex.Visotor.Isotropic')

-> import('Math.Sampler.Random')

-> import('File.Read');
}

namespace Hoa\Math\Test\Unit\Visitor {

/**
 * Class \Hoa\Math\Test\Unit\Visitor\Arithmetic.
 *
 * Test suite of the hoa://Library/Math/Arithmetic.pp grammar and the
 * Hoa\Math\Visitor\Arithmetic class.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2014 Ivan Enderlin.
 * @license    New BSD License
 */

class Arithmetic extends \Hoa\Test\Unit\Suite {

    public function case_visitor_exhaustively ( ) {

        $sampler  = new \Hoa\Compiler\Llk\Sampler\BoundedExhaustive(
            \Hoa\Compiler\Llk\Llk::load(
                new \Hoa\File\Read('hoa://Library/Math/Test/Unit/Arithmetic.pp')
            ),
            new \Hoa\Regex\Visitor\Isotropic(
                new \Hoa\Math\Sampler\Random()
            ),
            10
        );
        $compiler = \Hoa\Compiler\Llk\Llk::load(
            new \Hoa\File\Read('hoa://Library/Math/Arithmetic.pp')
        );
        $visitor  = new \Hoa\Math\Visitor\Arithmetic();

        $this->executeOnFailure(function ( ) use ( &$expression ) {

            echo 'Failed expression: ', $expression, '.', "\n";
        });

        foreach($sampler as $expression) {

            $dump = $expression;

            try {

                $x = (float) $visitor->visit($compiler->parse($expression));
            }
            catch ( \Exception $e ) {

                continue;
            }

            eval('$y = (float) ' . $expression . ';');

            if(is_nan($x) || is_nan($y)) {

                $this->boolean(true);

                continue;
            }

            $this
                ->float($x)
                    ->isNearlyEqualTo($y);
        }
    }
}

}
