<?php

namespace MageSuite\ImageResize\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_IMAGE_PLACEHOLDER = 'catalog/placeholder/image_placeholder';
    const DEFAULT_PLACEHOLDER = 'Magento_Catalog::images/product/placeholder/image.jpg';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $mediaBaseUrl;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->scopeConfig = $scopeConfigInterface;
        $this->assetRepository = $assetRepository;
        $this->storeManager = $storeManager;
    }

    public function getPlaceholderPathFromConfig()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_IMAGE_PLACEHOLDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultPlaceholderUrl()
    {
        $this->assetRepository->getUrl(self::DEFAULT_PLACEHOLDER);
    }

    public function getMediaBaseUrl()
    {
        if (!$this->mediaBaseUrl) {
            $this->mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        }

        return $this->mediaBaseUrl;
    }
}
