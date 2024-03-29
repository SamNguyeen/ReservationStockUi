<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_SS extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_SS
 * @noinspection DuplicatedCode
 */

namespace Magenest\ReservationStockUi\Helper;

use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Helper extends AbstractHelper
{
    const KEY_ENABLE_LOGGING = 'cataloginventory/reservation/enabled_log';
    const KEY_LOG_KEEP_DAY = 'cataloginventory/reservation/log_interval';

    protected $serializer;

    protected $_coreRegistry;

    protected $storeManager;

    /**
     * Helper constructor.
     *
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    public function registry($key)
    {
        return $this->_coreRegistry->registry($key);
    }

    public function register($key, $value)
    {
        try {
            $this->_coreRegistry->register($key, $value);
        } catch (\RuntimeException $e) {
            $this->debug($e);

            return false;
        }

        return true;
    }

    /**
     * @param \Throwable|string $e
     */
    public function debug($e)
    {
        if ($e instanceof \Throwable) {
            $this->_logger->critical($e->getMessage());
        } else {
            $this->_logger->critical($e);
        }
    }

    public function getStoreConfig($path, $scope = null, $scopeId = null)
    {
        if ($scope && in_array($scope, [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES, ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_WEBSITES], true)) {
            try {
                if (empty($scopeId)) {
                    if ($scope == ScopeInterface::SCOPE_WEBSITE || $scope == ScopeInterface::SCOPE_WEBSITES) {
                        $website = $this->storeManager->getWebsite();
                        $scopeId = $website->getId();
                    } else {
                        $store = $this->storeManager->getStore();
                        $scopeId = $store->getId();
                    }
                }
                if ($value = $this->scopeConfig->getValue($path, $scopeId, $scope)) {
                    return $value;
                }
            } catch (NoSuchEntityException $e) {
                $this->debug($e);
            }
        }

        return $this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    public function unserialize($string)
    {
        if (!$this->isJson($string)) {

            return is_array($string) ?: [$string];
        }

        return $this->serializer->unserialize($string);
    }

    public function isJson($string)
    {
        if (!empty($string) && !is_array($string)) {
            json_decode($string);

            return (json_last_error() == JSON_ERROR_NONE);
        }

        return false;
    }

    public function serialize($string)
    {
        if ($this->isJson($string)) {
            return $string;
        }

        return $this->serializer->serialize($string);
    }

    public function isEnableLog()
    {
        return $this->getStoreConfig(self::KEY_ENABLE_LOGGING);
    }

    public function getClearPeriod()
    {
        $day = $this->getStoreConfig(self::KEY_LOG_KEEP_DAY);
        $now = new \DateTime();
        if ((int)$day > 0) {
            $now = $now->sub(new \DateInterval("P{$day}D"));
        }

        return $now->format('Y-m-d H:i:s');
    }
}
