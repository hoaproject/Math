<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2018, Hoa community. All rights reserved.
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

namespace Hoa\Math\Visitor;

use Hoa\Math;
use Hoa\Visitor;

/**
 * Evaluate arithmetical expressions.
 */
class Arithmetic implements Visitor\Visit
{
    /**
     * Visitor context containing the list of supported functions, constants and variables
     */
    protected $_context = null;

    /**
     * Initializes context.
     */
    public function __construct()
    {
        $this->initializeContext();

        return;
    }

    /**
     * Set visitor's context
     */
    public function setContext(Math\Context $context): Math\Context
    {
        $old = $this->_context;

        $this->_context = $context;

        return $old;
    }

    /**
     * Get visitor's context
     */
    public function getContext(): Math\Context
    {
        return $this->_context;
    }

    /**
     * Visit an element.
     */
    public function visit(
        Visitor\Element $element,
        &$handle = null,
        $eldnah  = null
    ) {
        $type     = $element->getId();
        $children = $element->getChildren();

        if (null === $handle) {
            $handle = function ($x) {
                return $x;
            };
        }

        $acc = &$handle;

        switch ($type) {
            case '#function':
                $name      = array_shift($children)->accept($this, $_, $eldnah);
                $function  = $this->getFunction($name);
                $arguments = [];

                foreach ($children as $child) {
                    $child->accept($this, $_, $eldnah);
                    $arguments[] = $_();
                    unset($_);
                }

                $acc = function () use ($function, $arguments, $acc) {
                    return $acc($function->distributeArguments($arguments));
                };

                break;

            case '#negative':
                $children[0]->accept($this, $a, $eldnah);

                $acc = function () use ($a, $acc) {
                    return $acc(-$a());
                };

                break;

            case '#addition':
                $children[0]->accept($this, $a, $eldnah);

                $acc = function ($b) use ($a, $acc) {
                    return $acc($a() + $b);
                };

                $children[1]->accept($this, $acc, $eldnah);

                break;

            case '#substraction':
                $children[0]->accept($this, $a, $eldnah);

                $acc = function ($b) use ($a, $acc) {
                    return $acc($a()) - $b;
                };

                $children[1]->accept($this, $acc, $eldnah);

                break;

            case '#multiplication':
                $children[0]->accept($this, $a, $eldnah);

                $acc = function ($b) use ($a, $acc) {
                    return $acc($a() * $b);
                };

                $children[1]->accept($this, $acc, $eldnah);

                break;

            case '#division':
                $children[0]->accept($this, $a, $eldnah);
                $parent = $element->getParent();

                if (null === $parent ||
                    $type === $parent->getId()) {
                    $acc = function ($b) use ($a, $acc) {
                        if (0.0 === $b) {
                            throw new \RuntimeException(
                                'Division by zero is not possible.'
                            );
                        }

                        return $acc($a()) / $b;
                    };
                } else {
                    if ('#fakegroup' !== $parent->getId()) {
                        $classname = get_class($element);
                        $group     = new $classname(
                            '#fakegroup',
                            null,
                            [$element],
                            $parent
                        );
                        $element->setParent($group);

                        $this->visit($group, $acc, $eldnah);

                        break;
                    } else {
                        $acc = function ($b) use ($a, $acc) {
                            if (0.0 === $b) {
                                throw new \RuntimeException(
                                    'Division by zero is not possible.'
                                );
                            }

                            return $acc($a() / $b);
                        };
                    }
                }

                $children[1]->accept($this, $acc, $eldnah);

                break;

            case '#fakegroup':
            case '#group':
                $children[0]->accept($this, $a, $eldnah);

                $acc = function () use ($a, $acc) {
                    return $acc($a());
                };

                break;

            case '#variable':
                $out = $this->getVariable($children[0]->getValueValue());

                $acc = function () use ($out, $acc) {
                    return $acc($out);
                };

                break;

            case 'token':
                $value = $element->getValueValue();
                $out   = null;

                if ('constant' === $element->getValueToken()) {
                    if (defined($value)) {
                        $out = constant($value);
                    } else {
                        $out = $this->getConstant($value);
                    }
                } elseif ('id' === $element->getValueToken()) {
                    return $value;
                } else {
                    $out = (float) $value;
                }

                $acc = function () use ($out, $acc) {
                    return $acc($out);
                };

                break;
        }

        if (null === $element->getParent()) {
            return $acc();
        }
    }

    /**
     * Get functions.
     */
    public function getFunctions(): \ArrayObject
    {
        return $this->_context->getFunctions();
    }

    /**
     * Get a function.
     */
    public function getFunction(string $name): \Hoa\Consistency\Xcallable
    {
        return $this->_context->getFunction($name);
    }

    /**
     * Get constants.
     */
    public function getConstants(): \ArrayObject
    {
        return $this->_context->getConstants();
    }

    /**
     * Get a constant.
     */
    public function getConstant(string $name)
    {
        return $this->_context->getConstant($name);
    }

    /**
     * Get variables.
     */
    public function getVariables(): \ArrayObject
    {
        return $this->_context->getVariables();
    }

    /**
     * Get a variable.
     */
    public function getVariable(string $name)
    {
        return $this->_context->getVariable($name);
    }

    protected function initializeContext(): void
    {
        if (null === $this->_context) {
            $this->_context = new Math\Context();
        }

        return;
    }

    /**
     * Add a function.
     */
    public function addFunction(string $name, $callable = null)
    {
        return $this->_context->addFunction($name, $callable);
    }

    /**
     * Add a constant.
     */
    public function addConstant(string $name, $value)
    {
        return $this->_context->addConstant($name, $value);
    }

    /**
     * Add a variable.
     */
    public function addVariable(string $name, callable $callable)
    {
        return $this->_context->addVariable($name, $callable);
    }
}
