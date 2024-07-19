<?php
namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;

class AddSimpleProduct implements DataPatchInterface
{
    private $productFactory;
    private $productRepository;
    private $storeManager;
    private $state;

    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        State $state
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->state = $state;
    }

    public function apply()
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Area code is already set
        }

        $sku = 'oversize-tshirt-sku';
        try {
            // Try to load the product by SKU
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            // Product does not exist, create a new one
            $product = $this->productFactory->create();
            $product->setSku($sku);
        }

        // Set or update product attributes
        $product->setName('Oversize T-Shirt');
        $product->setAttributeSetId(4); // Default attribute set ID
        $product->setStatus(1); // Enabled
        $product->setWeight(10);
        $product->setVisibility(4); // Catalog, Search
        $product->setTaxClassId(0); // None
        $product->setTypeId('simple');
        $product->setPrice(100);
        $product->setStockData(
            [
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1,
            ]
        );
        $product->setWebsiteIds([$this->storeManager->getStore()->getWebsiteId()]);
        $product->setCategoryIds([3]); // Replace with your desired category ID

        // Save the product
        $this->productRepository->save($product);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
