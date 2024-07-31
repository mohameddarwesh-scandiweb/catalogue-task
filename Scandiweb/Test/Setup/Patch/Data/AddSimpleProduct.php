<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;

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
     * AddSimpleProduct constructor.
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
     * Applies the patch
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->state->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * Execute the patch
     * @return void
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function execute(): void
    {
        $sku = 'oversize-tshirt-sku';
        $product = $this->productInterfaceFactory->create();
        if($product->getIdBySku($sku)){
            return;
        }

        // Product does not exist, create a new one
        $product->setSku($sku);
    

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
     * Get the array of patches that have to be executed before this patch
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get the array of patches that have to be executed after this patch
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
