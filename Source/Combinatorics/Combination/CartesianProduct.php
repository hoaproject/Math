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

namespace Hoa\Math\Combinatorics\Combination;

use Hoa\Iterator;

/**
 * Cartesian n-ary product iterator:
 *     X = {1, 2}
 *     Y = {a, b}
 *     Z = {A, B, C}
 *     X × Y × Z = { (1, a, A), (2, a, A), (1, b, A), (2, b, A)
 *                   (1, a, B), (2, a, B), (1, b, B), (2, b, B)
 *                   (1, a, C), (2, a, C), (1, b, C), (2, b, C) }
 */
class CartesianProduct implements Iterator
{
    /**
     * All sets.
     */
    protected $_sets    = [];

    /**
     * Number of sets.
     */
    protected $_max     = 0;

    /**
     * Key.
     */
    protected $_key     = 0;

    /**
     * Current (contains the current t-uple).
     */
    protected $_current = null;

    /**
     * Whether the iterator has reached the end or not.
     */
    protected $_break   = true;



    /**
     * Constructor.
     */
    public function __construct(array $set)
    {
        foreach (func_get_args() as $s) {
            if (is_array($s)) {
                $s = new Iterator\Map($s);
            } else {
                $s = new Iterator\IteratorIterator($s);
            }

            $this->_sets[] = $s;
        }

        $this->_max   = count($this->_sets) - 1;
        $this->_break = empty($this->_sets);

        return;
    }

    /**
     * Get the current value.
     */
    public function current(): array
    {
        return $this->_current;
    }

    /**
     * Prepare the current value.
     */
    protected function _current(): void
    {
        $this->_current = [];

        foreach ($this->_sets as $set) {
            $this->_current[] = $set->current();
        }

        return;
    }

    /**
     * Get the current key.
     */
    public function key(): int
    {
        return $this->_key;
    }

    /**
     * Advance the internal collection pointer, and return the current value.
     */
    public function next(): array
    {
        for ($i = 0; $i <= $this->_max; ++$i) {
            $this->_sets[$i]->next();

            if (false !== $this->_sets[$i]->valid()) {
                break;
            }

            $this->_sets[$i]->rewind();

            if ($i === $this->_max) {
                $this->_break = true;

                break;
            }
        }

        ++$this->_key;
        $this->_current();

        return $this->current();
    }

    /**
     * Rewind the internal collection pointer, and return the first collection.
     */
    public function rewind(): array
    {
        $this->_break = empty($this->_sets);
        $this->_key   = 0;

        foreach ($this->_sets as $set) {
            $set->rewind();
        }

        $this->_current();

        return $this->current();
    }

    /**
     * Check if there is a current element after calls to the rewind() or the
     * next() methods.
     */
    public function valid(): bool
    {
        return false === $this->_break;
    }
}
