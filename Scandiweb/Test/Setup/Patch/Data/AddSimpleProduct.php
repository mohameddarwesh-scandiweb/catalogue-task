<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Exception;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

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
     * @var SourceItemsSaveInterface
     */
    protected SourceItemsSaveInterface $sourceItemsSave;

    /**
     * @var SourceItemInterfaceFactory
     */
    protected SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * AddSimpleProduct constructor.
     *
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemInterfaceFactory $sourceItemFactory
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        State $state,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory
    ) {
        $this->productInterfaceFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
    }

    /**
     * Applies the patch
     *
     * @return void
     * @throws Exception
     */
    public function apply(): void
    {
        $this->state->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * Execute the patch
     *
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
        $product->setSku($sku)
            ->setName('Oversize T-Shirt')
            ->setAttributeSetId(4) // Default attribute set ID
            ->setStatus(1) // Enabled
            ->setWeight(10)
            ->setVisibility(4) // Catalog, Search
            ->setTaxClassId(0) // None
            ->setTypeId('simple')
            ->setPrice(100)
            ->setStockData(
                [
                    'qty' => 100,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1,
                ]
            )
            ->setWebsiteIds([$this->storeManager->getStore()->getWebsiteId()])
            ->setCategoryIds([3]); // 3 for Men

        // Save the product
        $this->productRepository->save($product);

        // Set source item quantity
        $this->setSourceItemQuantity($sku, 'default', 100);
    }

    private function setSourceItemQuantity(string $sku, string $sourceCode, float $quantity): void
    {

        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSku($sku)
            ->setSourceCode($sourceCode)
            ->setQuantity($quantity)
            ->setStatus(Status::STATUS_ENABLED);

        $this->sourceItemsSave->execute([$sourceItem]);
    }

    /**
     * Get the array of patches that have to be executed before this patch
     *
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get the array of patches that have to be executed after this patch
     *
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
