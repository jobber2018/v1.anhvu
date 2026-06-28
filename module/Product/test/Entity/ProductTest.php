<?php

namespace ProductTest\Entity;

use PHPUnit\Framework\TestCase;
use Product\Entity\Product;

class ProductTest extends TestCase
{
    public function testValidateQtyReturnsExactBoxQuantityWhenQuantityIsMultipleOfBoxUnit()
    {
        $product = new Product();
        $product->setBoxUnit(16);
        $product->setExchangeUnit(3);
        $product->setInventory(100);

        $this->assertSame(16, $product->validateQty(16));
    }

    public function testValidateQtyRoundsRemainderBelowExchangeUnitUpToNextExchangeUnit()
    {
        $product = new Product();
        $product->setBoxUnit(16);
        $product->setExchangeUnit(3);
        $product->setInventory(100);

        $this->assertSame(19, $product->validateQty(19));
    }

    public function testValidateQtyRoundsRemainderAboveExchangeUnitUsingExchangeUnitMultiple()
    {
        $product = new Product();
        $product->setBoxUnit(16);
        $product->setExchangeUnit(3);
        $product->setInventory(100);

        $this->assertSame(32, $product->validateQty(33));
    }

    public function testValidateQtyDoesNotExceedInventory()
    {
        $product = new Product();
        $product->setBoxUnit(16);
        $product->setExchangeUnit(3);
        $product->setInventory(100);

        $this->assertSame(38, $product->validateQty(38));
    }
}
