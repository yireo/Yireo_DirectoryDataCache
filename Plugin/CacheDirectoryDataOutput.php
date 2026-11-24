<?php declare(strict_types=1);

namespace Yireo\DirectoryDataCache\Plugin;

use Closure;
use InvalidArgumentException;
use Magento\Checkout\CustomerData\DirectoryData;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\Config as ConfigCacheType;
use Magento\Framework\App\Cache\Type\Translate as TranslateCacheType;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\DirectoryDataCache\Cache\Type as DirectoryDataCacheType;

class CacheDirectoryDataOutput
{
    const CACHE_KEY = 'directory_data';

    public function __construct(
        private DirectoryDataCacheType $directoryDataCacheType,
        private SerializerInterface $serializer,
        private StateInterface $cacheState,
        private StoreManagerInterface $storeManager,
        private ResolverInterface $localeResolver
    ) {
    }

    /**
     * @param DirectoryData $directoryData
     * @param Closure $proceed
     * @return array
     */
    public function aroundGetSectionData(DirectoryData $directoryData, Closure $proceed): array
    {
        if (!$this->enabled()) {
            return $proceed();
        }

        $cacheKey = $this->getCacheKey();
        $data = (string)$this->directoryDataCacheType->load($cacheKey);
        if ($data) {
            try {
                $data = $this->serializer->unserialize($data);
            } catch (InvalidArgumentException $exception) {
                $data = null;
                $this->directoryDataCacheType->remove($cacheKey);
            }

            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        $data = $proceed();
        $this->directoryDataCacheType->save(
            (string)$this->serializer->serialize($data),
            $cacheKey,
            [ConfigCacheType::CACHE_TAG, TranslateCacheType::CACHE_TAG]
        );

        return $data;
    }

    private function getCacheKey(): string
    {
        return self::CACHE_KEY
            . '_' . (int)$this->storeManager->getStore()->getId()
            . '_' . $this->localeResolver->getLocale();
    }

    private function enabled(): bool
    {
        return $this->cacheState->isEnabled(DirectoryDataCacheType::TYPE_IDENTIFIER);
    }
}
