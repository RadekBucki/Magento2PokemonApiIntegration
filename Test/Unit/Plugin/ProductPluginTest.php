<?php
declare(strict_types=1);

namespace Cepdtech\Test\Unit\Pokemon\Plugin;

use Cepdtech\Pokemon\Config\ConfigInterface;
use Cepdtech\Pokemon\Plugin\ProductPlugin;
use Closure;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images;
use Magento\Framework\Api\AttributeInterface;
use PHPUnit\Framework\TestCase;

class ProductPluginTest extends TestCase
{
    private const string CALLABLE_VALUE = 'callable value';
    private const string ATTRIBUTE_VALUE = 'attribute value';

    private const string ATTRIBUTE_DOESNT_EXIST = 'attribute does not exist';
    private const string ATTRIBUTE_NULL_VALUE = 'attribute is null';
    private const string ATTRIBUTE_EXISTING_VALUE = 'attribute has value';

    private ProductPlugin $productPlugin;

    private ConfigInterface $config;
    private Closure $calleable;
    private Product $subject;
    private AttributeInterface $attribute;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->productPlugin = new ProductPlugin($this->config);

        $this->calleable = function () {
            return self::CALLABLE_VALUE;
        };
        $this->subject = $this->createMock(Product::class);
        $this->attribute = $this->createMock(AttributeInterface::class);
    }

    public function createProviderBasedDataMocks(bool $isEnabled, string $string): void
    {
        $this->config->method('isEnabled')
            ->willReturn($isEnabled);
        $this->subject->method('getCustomAttribute')
            ->willReturnCallback(function () use ($string) {
                switch ($string) {
                    case self::ATTRIBUTE_DOESNT_EXIST:
                        return null;
                    case self::ATTRIBUTE_NULL_VALUE:
                        return $this->attribute;
                    case self::ATTRIBUTE_EXISTING_VALUE:
                        $this->attribute->method('getValue')
                            ->willReturn(self::ATTRIBUTE_VALUE);
                        return $this->attribute;
                }
            });
    }

    public function dataProvider(): array
    {
        return [
            'not enabled, attribute does not exist' => [
                'isEnabled' => false,
                'string' => self::ATTRIBUTE_DOESNT_EXIST,
                'expected' => self::CALLABLE_VALUE
            ],
            'not enabled, attribute is null' => [
                'isEnabled' => false,
                'string' => self::ATTRIBUTE_NULL_VALUE,
                'expected' => self::CALLABLE_VALUE
            ],
            'not enabled, attribute has value' => [
                'isEnabled' => false,
                'string' => self::ATTRIBUTE_EXISTING_VALUE,
                'expected' => self::CALLABLE_VALUE
            ],
            'enabled, attribute does not exist' => [
                'isEnabled' => true,
                'string' => self::ATTRIBUTE_DOESNT_EXIST,
                'expected' => self::CALLABLE_VALUE
            ],
            'enabled, attribute is null' => [
                'isEnabled' => true,
                'string' => self::ATTRIBUTE_NULL_VALUE,
                'expected' => self::CALLABLE_VALUE
            ],
            'enabled, attribute has value' => [
                'isEnabled' => true,
                'string' => self::ATTRIBUTE_EXISTING_VALUE,
                'expected' => self::ATTRIBUTE_VALUE
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundGetName(bool $isEnabled, string $string, string $expected): void
    {
        $this->createProviderBasedDataMocks($isEnabled, $string);

        $result = $this->productPlugin->aroundGetName($this->subject, $this->calleable);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundGetImage(bool $isEnabled, string $string, string $expected): void
    {
        $this->createProviderBasedDataMocks($isEnabled, $string);

        $result = $this->productPlugin->aroundGetImage($this->subject, $this->calleable);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundGetDataForSmallImage(
        bool $isEnabled,
        string $string,
        string $expected
    ): void {
        $this->createProviderBasedDataMocks($isEnabled, $string);

        $result = $this->productPlugin->aroundGetData($this->subject, $this->calleable, Images::CODE_SMALL_IMAGE);
        $this->assertEquals($expected, $result);
    }

    public function testAroundGetDataForNonSmallImage(): void
    {
        $result = $this->productPlugin->aroundGetData($this->subject, $this->calleable, 'non-small-image');
        $this->assertEquals(self::CALLABLE_VALUE, $result);
    }
}
