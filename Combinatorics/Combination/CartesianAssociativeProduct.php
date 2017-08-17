<?php


/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2014, Karoly Negyesi. All rights reserved.
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

namespace Hoa\Math\Combinatorics\Combination;

/**
 * Class \Hoa\Math\Combinatorics\Combination\CartesianAssociativeProduct.
 *
 * Cartesian n-ary product iterator:
 *     [
 *       X => [1, 2],
 *       Y => [a, b],
 *     ]
 *     X × Y = [ [X => 1, Y => a], [X => 1, Y => b], [X => 2, Y => a],
 *               [X => 2, y => b] ]
 *
 * @author     Karoly Negyesi <karoly@negyesi.net>
 * @copyright  Copyright © 2014 Karoly Negyesi.
 * @license    New BSD License
 */

class CartesianAssociativeProduct extends CartesianProduct {

    /**
     * array_keys() of the input array.
     *
     * @var array
     */
    protected $_keys = [];

    /**
     * Constructor.
     *
     * @access  public
     * @param   array  $array    Associative array of arrays.
     * @return  void
     */
    public function __construct ( $array ) {

        $this->_keys = array_keys($array);
        $this->init($array);
    }

    /**
     * Prepare the current value.
     *
     * @access  protected
     * @return  void
     */
    protected function _current ( ) {

        $this->_current = [];

        foreach($this->_sets as $i => $set)
            $this->_current[$this->_keys[$i]] = $set->current();

        return;
    }

}
