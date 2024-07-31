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
    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productInterfaceFactory;
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;
    /**
     * @var State
     */
    protected State $state;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param State $state
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        State $state
    )
    {
        $this->productInterfaceFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->state = $state;
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->state->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    public function execute(): void
    {
        $sku = 'oversize-tshirt-sku';
        try {
            $product = $this->productInterfaceFactory->getIdBySku($sku);

        } catch (NoSuchEntityException $e) {
            // Product does not exist, create a new one
            $product = $this->productInterfaceFactory->create();
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
        $product->setCategoryIds([3]); // 3 for Men
        $product->setQuantity(100);

        // Save the product
        $this->productRepository->save($product);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
