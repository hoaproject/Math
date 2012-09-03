<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2012, Ivan Enderlin. All rights reserved.
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
 * \Hoa\Visitor\Visit
 */
-> import('Visitor.Visit')

/**
 * \Hoa\Math\Exception\UnknownFunction
 */
-> import('Math.Exception.UnknownFunction')

/**
 * \Hoa\Math\Exception\UnknownConstant
 */
-> import('Math.Exception.UnknownConstant')

/**
 * \Hoa\Math\Exception\DivisionByZero
 */
-> import('Math.Exception.DivisionByZero');

}

namespace Hoa\Math\Visitor {

/**
 * Class \Hoa\Math\Visitor\Arithmetic.
 *
 * Evaluate arithmetical expressions
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @author     Stéphane Py <py.stephane1@gmail.com>
 * @author     Sébastien Houze <s@verylastroom.com>
 * @copyright  Copyright © 2007-2012 Ivan Enderlin, Stéphane Py, Sébastien Houze.
 * @license    New BSD License
 */

class Arithmetic implements \Hoa\Visitor\Visit {

    /**
     * List of supported functions: identifier => values as callable
     *
     * @var \ArrayObject object
     */
    protected $_functions = null;

    /**
     * List of constants supported
     *
     * @var \ArrayObject object
     */
    protected $_constants = null;

    /**
     * Visit an element.
     *
     * @access  public
     * @param   \Hoa\Visitor\Element  $element    Element to visit.
     * @param   mixed                 &$handle    Handle (reference).
     * @param   mixed                 $eldnah     Handle (not reference).
     * @return  float
     */
    public function visit ( \Hoa\Visitor\Element $element,
                            &$handle = null, $eldnah = null ) {

        $type = $element->getId();

        if('token' === $type) {

            // replace spaces coz (float) '+ 1'; = 0
            $value = str_replace(' ', '', $element->getValueValue());

            if('constant' === $element->getValueToken()) {

                if(defined($value))
                    return constant($value);
                else {

                    if(null === $this->_constants)
                        $this->initializeConstants();

                    return $this->getConstant($value);
                }
            }

           return (float) $value;
        }

        $children = $element->getChildren();

        // In #function token, first elements is the name of function to execute.
        if('#function' === $type)
            $functionChildren = array_shift($children);

        // Evaluate children.
        foreach($children as &$child)
            $child = $child->accept($this, $handle, $eldnah);

        switch($type) {

            case '#function':
                if(null === $this->_functions)
                    $this->initializeFunctions();

                $callable = $this->getFunction($functionChildren->getValueValue());

                return $callable->distributesArguments($children);
              break;

            case '#negative':
                return $children[0] * -1;
              break;

            case '#addition':
            case '#substraction':
                if ('#substraction' === $type) {

                    $parent = $element->getParent();
                    if(null !== $parent && '#substraction' === $parent->getId() && $parent->getChild(1) === $element)
                        $type = ('#addition' === $type) ? '#substraction' : '#addition';
                }

                return '#addition' === $type ? $children[0] + $children[1] : $children[0] - $children[1];
              break;

            case '#power':
                return pow($children[0], $children[1]);
              break;

            case '#modulo':
                return ($children[0] % $children[1]);
              break;

            case '#multiplication':
                return ($children[0] * $children[1]);
              break;

            case '#division':
                if(0 == $children[1])
                    throw new \Hoa\Math\Exception\DivisionByZero('Division by zero', 0);
                return ($children[0] / $children[1]);
              break;

            default:
                throw new \Hoa\Core\Exception('Type %s is not supported', 1, $type);
              break;

        }
    }

    /**
     * Get a function on mapping list
     *
     * @access  public
     * @param string $id Ident of function.
     * @return xcallable
     */
    public function getFunction ( $id ) {
        if(!$this->_functions->offsetExists($id))
            throw new \Hoa\Math\Exception\UnknownFunction('Function "%s" is not yet implemented', 2, $id);

        return $this->_functions[$id];
    }

    /**
     * Get a constant on mapping list
     *
     * @access  public
     * @param string $ident Ident of constant.
     * @return mixed
     */
    public function getConstant ( $ident ) {
        if(!$this->_constants->offsetExists($ident))
            throw new \Hoa\Math\Exception\UnknownConstant('Constant "%s" is not yet implemented', 3, $ident);

        return $this->_constants[$ident];
    }

    /**
     * Initialize functions mapping
     *
     * @access protected
     */
    protected function initializeFunctions () {
        $average = function () {

            $arguments = func_get_args();

            return array_sum($arguments) / count($arguments);
        };

        $this->_functions = new \ArrayObject(array(
            'abs'     => xcallable('abs'),
            'acos'    => xcallable(function( $value ) { return acos(deg2rad($value)); }),
            'asin'    => xcallable(function( $value ) { return asin(deg2rad($value)); }),
            'atan'    => xcallable(function( $value ) { return atan(deg2rad($value)); }),
            'average' => xcallable($average),
            'avg'     => xcallable($average),
            'ceil'    => xcallable('ceil'),
            'cos'     => xcallable(function( $value ) { return cos(deg2rad($value)); }),
            'count'   => xcallable(function() { return count(func_get_args()); }),
            'deg2rad' => xcallable('deg2rad'),
            'exp'     => xcallable('exp'),
            'floor'   => xcallable('floor'),
            'ln'      => xcallable('log'),
            'log'     => xcallable(function( $value, $base = 10 ) { return log($value, $base); }),
            'log10'   => xcallable('log10'),
            'max'     => xcallable('max'),
            'min'     => xcallable('min'),
            'pow'     => xcallable('pow'),
            'rad2deg' => xcallable('rad2deg'),
            'sin'     => xcallable(function( $value ) { return sin(deg2rad($value)); }),
            'sqrt'    => xcallable('sqrt'),
            'sum'     => xcallable(function() { return array_sum(func_get_args()); }),
            'tan'     => xcallable(function( $value ) { return tan(deg2rad($value)); }),
        ));
    }

    /**
     * Initialize constants mapping
     *
     * @access protected
     */
    protected function initializeConstants () {
        $this->_constants = new \ArrayObject(array(
            'PI'      => M_PI,
            'π'       => M_PI,
            'PI_2'    => M_PI_2,
            'PI_4'    => M_PI_4,
            'E'       => M_E,
            'SQRT_PI' => M_SQRTPI,
            'SQRT_2'  => M_SQRT2,
            'SQRT_3'  => M_SQRT3,
            'LN_PI'   => M_LNPI,
        ));
    }

    /**
     * Add a function to the mapping
     *
     * @param string $ident    ident of function
     * @param mixed  $callable callback
     */
    public function addFunction ( $ident, $callable = null ) {
        if(null === $callable) {

            if(false === function_exists($ident))
                throw new \Hoa\Core\Exception('Function %s does not exists', 4, $ident);

            $callable = $ident;
        }

        $this->_functions[$ident] = xcallable($callable);
    }

    /**
     * Add a constant to the mapping
     *
     * @param string $ident ident of constant
     * @param mixed  $value value of constant
     */
    public function addConstant ( $ident, $value ) {
        $this->_constants[$ident] = $value;
    }
}

}
