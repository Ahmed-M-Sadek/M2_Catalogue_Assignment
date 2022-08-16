<?php

declare(strict_types=1);
namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class AddFirstSimpleProduct
 */
class AddFirstSimpleProduct implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $setup
     */
    protected ModuleDataSetupInterface $setup;

    /**
     * @var ProductInterfaceFactory $productInterfaceFactory
     */
    protected ProductInterfaceFactory $productInterfaceFactory;

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var State $appState
     */
    protected State $appState;

    /**
     * @var EavSetup $eavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var SourceItemInterfaceFactory $sourceItemFactory
     */
    protected SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * @var SourceItemsSaveInterface $sourceItemsSaveInterface
     */
    protected SourceItemsSaveInterface $sourceItemsSaveInterface;

    /**
     * @var CategoryLinkManagementInterface $categoryLink
     */
    protected CategoryLinkManagementInterface $categoryLink;

    /**
     * @var CategoryCollectionFactory $categoryCollectionFactory
     */
    protected CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * @var array $sourceItems
     */
    protected array $sourceItems = [];
    
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param State $appState
     * @param StoreManagerInterface $storeManager
     * @param EavSetup $eavSetup
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param CategoryLinkManagementInterface $categoryLink
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        CategoryLinkManagementInterface $categoryLink,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->setup = $setup;
        $this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->categoryLink = $categoryLink;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply() : void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }
    
    /**
     * execute
     *
     * @return void
     */
    public function execute() : void
    {
        $product = $this->productInterfaceFactory->create();
        $productSKU = 'TST001';

        if ($product->getIdBySku($productSKU)) {
            return;
        }

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setName('Test Product 1')
            ->setSku($productSKU)
            ->setUrlKey('testproduct1')
            ->setPrice(29.99)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(
                [
                    'manage_stock' => 1,
                    'is_in_stock' => 1,
                    'qty' => 1000
                ]
            );

        $product = $this->productRepository->save($product);

        $categoryTitles = ['Men', 'Default Category'];
        $categoryIds = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', ['in' => $categoryTitles])
            ->getAllIds();
        $this->categoryLink->assignProductToCategories($product->getSku(), $categoryIds);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies() : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases() : array
    {
        return [];
    }
}
