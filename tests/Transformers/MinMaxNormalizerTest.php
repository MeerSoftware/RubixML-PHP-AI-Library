<?php

namespace Rubix\ML\Tests\Transformers;

use Rubix\ML\Transformers\Elastic;
use Rubix\ML\Transformers\Stateful;
use Rubix\ML\Transformers\Transformer;
use Rubix\ML\Datasets\Generators\Blob;
use Rubix\ML\Transformers\MinMaxNormalizer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MinMaxNormalizerTest extends TestCase
{
    /**
     * @var \Rubix\ML\Datasets\Generators\Generator
     */
    protected $generator;
    
    /**
     * @var \Rubix\ML\Transformers\MinMaxNormalizer
     */
    protected $transformer;

    public function setUp() : void
    {
        $this->generator = new Blob([0., 3000., -6.], [1., 30., 0.001]);

        $this->transformer = new MinMaxNormalizer(0., 1.);
    }

    public function test_build_transformer() : void
    {
        $this->assertInstanceOf(MinMaxNormalizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
        $this->assertInstanceOf(Elastic::class, $this->transformer);
    }

    public function test_fit_update_transform() : void
    {
        $this->transformer->fit($this->generator->generate(30));

        $this->transformer->update($this->generator->generate(30));

        $this->assertTrue($this->transformer->fitted());

        $sample = $this->generator->generate(1)
            ->apply($this->transformer)
            ->sample(0);

        $this->assertCount(3, $sample);
        
        $this->assertEqualsWithDelta(0.5, $sample[0], 1);
        $this->assertEqualsWithDelta(0.5, $sample[1], 1);
        $this->assertEqualsWithDelta(0.5, $sample[2], 1);
    }

    public function test_transform_unfitted() : void
    {
        $this->expectException(RuntimeException::class);

        $samples = $this->generator->generate(1)->samples();

        $this->transformer->transform($samples);
    }
}
