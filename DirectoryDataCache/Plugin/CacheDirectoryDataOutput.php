<?php declare(strict_types=1);

namespace Yireo\DirectoryDataCache\Plugin;

use Closure;
use Magento\Checkout\CustomerData\DirectoryData;
use Magento\Framework\Serialize\SerializerInterface;
use Yireo\DirectoryDataCache\Cache\Type as DirectoryDataCacheType;
use Magento\Framework\App\Cache\Manager as CacheManager;

class CacheDirectoryDataOutput
{
    const CACHE_KEY = 'directory_data';

    /**
     * @var DirectoryDataCacheType
     */
    private $directoryDataCacheType;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * CacheDirectoryDataOutput constructor.
     * @param DirectoryDataCacheType $directoryDataCacheType
     * @param SerializerInterface $serializer
     * @param CacheManager $cacheManager
     */
    public function __construct(
        DirectoryDataCacheType $directoryDataCacheType,
        SerializerInterface $serializer,
        CacheManager $cacheManager
    ) {
        $this->directoryDataCacheType = $directoryDataCacheType;
        $this->serializer = $serializer;
        $this->cacheManager = $cacheManager;
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

        $data = (string)$this->directoryDataCacheType->load(self::CACHE_KEY);
        if ($data) {
            $data = $this->serializer->unserialize($data);
            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        $data = $proceed();
        $this->directoryDataCacheType->save($this->serializer->serialize($data), self::CACHE_KEY);

        return $data;
    }

    /**
     * @return bool
     */
    public function enabled(): bool
    {
        $status = $this->cacheManager->getStatus();
        if (!isset($status[self::CACHE_KEY])) {
            return false;
        }

        return (bool)$status[self::CACHE_KEY];
    }
}
