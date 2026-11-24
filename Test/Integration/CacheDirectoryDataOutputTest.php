<?php declare(strict_types=1);

namespace Yireo\DirectoryDataCache\Test\Integration;

use Magento\Checkout\CustomerData\DirectoryData;
use Magento\Framework\App\Cache\Type\Config as ConfigCacheType;
use Magento\Framework\App\Cache\Type\Translate as TranslateCacheType;
use Magento\Framework\Interception\PluginListInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\DirectoryDataCache\Cache\Type as DirectoryDataCacheType;
use Yireo\DirectoryDataCache\Plugin\CacheDirectoryDataOutput;

/**
 * @magentoAppArea frontend
 */
class CacheDirectoryDataOutputTest extends TestCase
{
    protected function setUp(): void
    {
        $this->getCacheType()->clean();
    }

    protected function tearDown(): void
    {
        $this->getCacheType()->clean();
    }

    public function testPluginIsRegistered(): void
    {
        $pluginList = Bootstrap::getObjectManager()->get(PluginListInterface::class);
        $pluginList->getNext(DirectoryData::class, 'getSectionData');
        $plugin = $pluginList->getPlugin(DirectoryData::class, 'Yireo_DirectoryDataCache::saveCache');

        $this->assertInstanceOf(CacheDirectoryDataOutput::class, $plugin);
    }

    /**
     * @magentoCache directory_data enabled
     */
    public function testSectionDataIsCachedPerStoreAndLocale(): void
    {
        $data = $this->getSectionData();
        $this->assertNotEmpty($data);

        $cached = $this->getCacheType()->load($this->getExpectedCacheKey());
        $this->assertIsString($cached);
        $this->assertSame($data, $this->getSerializer()->unserialize($cached));
        $this->assertFalse($this->getCacheType()->load(CacheDirectoryDataOutput::CACHE_KEY));
    }

    /**
     * @magentoCache directory_data enabled
     */
    public function testDifferentLocaleUsesDifferentCacheEntry(): void
    {
        $this->getSectionData();
        $defaultKey = $this->getExpectedCacheKey();

        $localeResolver = Bootstrap::getObjectManager()->get(ResolverInterface::class);
        $originalLocale = $localeResolver->getLocale();
        $localeResolver->setLocale('nl_NL');

        try {
            $this->getSectionData();
            $otherKey = $this->getExpectedCacheKey();
        } finally {
            $localeResolver->setLocale($originalLocale);
        }

        $this->assertNotSame($defaultKey, $otherKey);
        $this->assertIsString($this->getCacheType()->load($defaultKey));
        $this->assertIsString($this->getCacheType()->load($otherKey));
    }

    /**
     * @magentoCache directory_data enabled
     * @dataProvider getCleaningCacheTypes
     */
    public function testCacheEntryIsRemovedWhenCleaningCacheType(string $cacheTypeClass): void
    {
        $this->getSectionData();
        $this->assertIsString($this->getCacheType()->load($this->getExpectedCacheKey()));

        Bootstrap::getObjectManager()->get($cacheTypeClass)->clean();

        $this->assertFalse($this->getCacheType()->load($this->getExpectedCacheKey()));
    }

    public static function getCleaningCacheTypes(): array
    {
        return [
            [ConfigCacheType::class],
            [TranslateCacheType::class],
        ];
    }

    /**
     * @magentoCache directory_data enabled
     */
    public function testCorruptCacheEntryFallsBackToOriginalData(): void
    {
        $cacheKey = $this->getExpectedCacheKey();
        $this->getCacheType()->save('{corrupt', $cacheKey);

        $data = $this->getSectionData();
        $this->assertNotEmpty($data);

        $cached = $this->getCacheType()->load($cacheKey);
        $this->assertIsString($cached);
        $this->assertSame($data, $this->getSerializer()->unserialize($cached));
    }

    /**
     * @magentoCache directory_data disabled
     */
    public function testNothingIsCachedWhenCacheTypeIsDisabled(): void
    {
        $this->assertNotEmpty($this->getSectionData());
        $this->assertFalse($this->getCacheType()->load($this->getExpectedCacheKey()));
    }

    private function getSectionData(): array
    {
        return Bootstrap::getObjectManager()->create(DirectoryData::class)->getSectionData();
    }

    private function getExpectedCacheKey(): string
    {
        $objectManager = Bootstrap::getObjectManager();
        $storeId = (int)$objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $locale = $objectManager->get(ResolverInterface::class)->getLocale();

        return CacheDirectoryDataOutput::CACHE_KEY . '_' . $storeId . '_' . $locale;
    }

    private function getCacheType(): DirectoryDataCacheType
    {
        return Bootstrap::getObjectManager()->get(DirectoryDataCacheType::class);
    }

    private function getSerializer(): SerializerInterface
    {
        return Bootstrap::getObjectManager()->get(SerializerInterface::class);
    }
}
