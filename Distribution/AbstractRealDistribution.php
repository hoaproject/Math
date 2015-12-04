<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2015, Hoa community. All rights reserved.
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

namespace Hoa\Math\Distribution;

use Hoa\Math\Sampler\Random;
use Hoa\Math\Analysis\Solvers\UnivariateFunction;
use Hoa\Math\Analysis\UnivariateSolverUtil;

/**
 * Base class for probability distributions on the reals.
 * Default implementations are provided for some of the methods
 * that do not vary from distribution to distribution.
 *
 * This class has been ported from Java: 
 *
 *    package org.apache.commons.math3.distribution.AbstractRealDistribution
 */
abstract class AbstractRealDistribution implements RealDistribution, Serializable
{
    /** 
     * Default accuracy.
     * @var int
     */
    const SOLVER_DEFAULT_ABSOLUTE_ACCURACY = 0.000001;

    /**
     * Random instance used to generate samples from the distribution.
     *
     * @var Random
     */
    protected $random;

    /** 
     * Solver absolute accuracy for inverse cumulative computation 
     *
     * @var int
     */
    private $solverAbsoluteAccuracy = SOLVER_DEFAULT_ABSOLUTE_ACCURACY;

    /**
     * @param rng Random number generator.
     * @since 3.1
     */
    public function __construct(Random $random)
    {
        $this->random = $random ? : new Random();
    }

    /**
     * For a random variable {@code X} whose values are distributed according
     * to this distribution, this method returns {@code P(x0 < X <= x1)}.
     *
     * @param x0 Lower bound (excluded).
     * @param x1 Upper bound (included).
     * @return the probability that a random variable with this distribution
     * takes a value between {@code x0} and {@code x1}, excluding the lower
     * and including the upper endpoint.
     * @throws NumberIsTooLargeException if {@code x0 > x1}.
     *
     * The default implementation uses the identity
     * {@code P(x0 < X <= x1) = P(X <= x1) - P(X <= x0)}
     */
    public function probability($x0, $x1)
    {
        if ($x0 > $x1) {
            throw new OutOfBoundsException(sprintf(
                'Lower bound "%s" cannot be higher than upper bound "%s"',
                $x0, $x1
            ));
        }
        return $this->cumulativeProbability($x1) - $this->cumulativeProbability($x0);
    }

    /**
     * {@inheritDoc}
     *
     * The default implementation returns
     * <ul>
     * <li>{@link #getSupportLowerBound()} for {@code p = 0},</li>
     * <li>{@link #getSupportUpperBound()} for {@code p = 1}.</li>
     * </ul>
     */
    public function inverseCumulativeProbability($p)
    {
        /**
         * IMPLEMENTATION NOTES
         * --------------------
         * Where applicable, use is made of the one-sided Chebyshev inequality
         * to bracket the root. This inequality states that
         * P(X - mu >= k * sig) <= 1 / (1 + k^2),
         * mu: mean, sig: standard deviation. Equivalently
         * 1 - P(X < mu + k * sig) <= 1 / (1 + k^2),
         * F(mu + k * sig) >= k^2 / (1 + k^2).
         *
         * For k = sqrt(p / (1 - p)), we find
         * F(mu + k * sig) >= p,
         * and (mu + k * sig) is an upper-bound for the root.
         *
         * Then, introducing Y = -X, mean(Y) = -mu, sd(Y) = sig, and
         * P(Y >= -mu + k * sig) <= 1 / (1 + k^2),
         * P(-X >= -mu + k * sig) <= 1 / (1 + k^2),
         * P(X <= mu - k * sig) <= 1 / (1 + k^2),
         * F(mu - k * sig) <= 1 / (1 + k^2).
         *
         * For k = sqrt((1 - p) / p), we find
         * F(mu - k * sig) <= p,
         * and (mu - k * sig) is a lower-bound for the root.
         *
         * In cases where the Chebyshev inequality does not apply, geometric
         * progressions 1, 2, 4, ... and -1, -2, -4, ... are used to bracket
         * the root.
         */
        if ($p < 0.0 || $p > 1.0) {
            throw new OutOfRangeException($p, 0, 1);
        }

        $lowerBound = $this->getSupportLowerBound();
        if ($p == 0.0) {
            return $lowerBound;
        }

        $upperBound = $this->getSupportUpperBound();
        if ($p == 1.0) {
            return $upperBound;
        }

        $mu = $this->getNumericalMean();
        $sig = sqrt($this->getNumericalVariance());
        $chebyshevApplies = !(Double.isInfinite(mu) || Double.isNaN(mu) ||
                             Double.isInfinite(sig) || Double.isNaN(sig));

        if ($lowerBound == Double.NEGATIVE_INFINITY) {
            if ($chebyshevApplies) {
                $lowerBound = $mu - $sig * sqrt((1. - $p) / $p);
            } else {
                $lowerBound = -1.0;
                while ($this->cumulativeProbability($lowerBound) >= $p) {
                    $lowerBound *= 2.0;
                }
            }
        }

        if ($upperBound == Double.POSITIVE_INFINITY) {
            if ($chebyshevApplies) {
                $upperBound = $mu + $sig * sqrt($p / (1. - $p));
            } else {
                $upperBound = 1.0;
                while ($this->cumulativeProbability($upperBound) < $p) {
                    $upperBound *= 2.0;
                }
            }
        }

        $toSolve = new UnivariateFunction(function ($x) use ($p) {
            return $this->cumulativeProbability($x) - $p;
        });

        UnivariateSolverUtils::solve($toSolve, $lowerBound, $upperBound, $this->getSolverAbsoluteAccuracy());

        if (!$this->isSupportConnected()) {
            /* Test for plateau. */
            $dx = $this->getSolverAbsoluteAccuracy();
            if ($x - $dx >= $this->getSupportLowerBound()) {
                $px = $this->cumulativeProbability($x);
                if ($this->cumulativeProbability($x - $dx) == $px) {
                    $upperBound = $x;
                    while ($upperBound - $lowerBound > $dx) {
                        $midPoint = 0.5 * ($lowerBound + $upperBound);
                        if ($this->cumulativeProbability($midPoint) < $px) {
                            $lowerBound = $midPoint;
                        } else {
                            $upperBound = $midPoint;
                        }
                    }
                    return $upperBound;
                }
            }
        }
        return $x;
    }

    /**
     * Returns the solver absolute accuracy for inverse cumulative computation.
     * You can override this method in order to use a Brent solver with an
     * absolute accuracy different from the default.
     *
     * @return the maximum absolute error in inverse cumulative probability estimates
     */
    protected function getSolverAbsoluteAccuracy()
    {
        return $this->solverAbsoluteAccuracy;
    }

    /** 
     * {@inheritDoc}
     * TODO: The Random sampler does not support reseed.
     */
    public function reseedRandomGenerator($seed)
    {
        $this->random->setSeed($seed);
    }

    /**
     * {@inheritDoc}
     *
     * The default implementation uses the
     * <a href="http://en.wikipedia.org/wiki/Inverse_transform_sampling">
     * inversion method.
     * </a>
     */
    public function sample($sampleSize = 1)
    {
        for ($i = 0; $i < $sampleSize; $i++) {
            // should we use the YIELD keyword here?
            yield $this->inverseCumulativeProbability(
                $this->random->getFloat()
            );
        }
    }

    /**
     * Returns the natural logarithm of the probability density function (PDF) of this distribution
     * evaluated at the specified point {@code x}. In general, the PDF is the derivative of the
     * {@link #cumulativeProbability(double) CDF}. If the derivative does not exist at {@code x},
     * then an appropriate replacement should be returned, e.g. {@code Double.POSITIVE_INFINITY},
     * {@code Double.NaN}, or the limit inferior or limit superior of the difference quotient. Note
     * that due to the floating point precision and under/overflow issues, this method will for some
     * distributions be more precise and faster than computing the logarithm of
     * {@link #density(double)}. The default implementation simply computes the logarithm of
     * {@code density(x)}.
     *
     * @param x the point at which the PDF is evaluated
     * @return the logarithm of the value of the probability density function at point {@code x}
     */
    abstract public function logDensity($x);
}
