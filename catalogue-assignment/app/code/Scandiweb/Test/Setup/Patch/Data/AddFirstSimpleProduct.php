<?PHP
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
    use Magento\InventoryApi\Api\Data\SourceItemInterface;
    use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
    use Magento\InventoryApi\Api\SourceItemsSaveInterface;
    use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;


    class AddFirstSimpleProduct
        implements DataPatchInterface
    {
        protected ModuleDataSetupInterface $setup;
        protected ProductInterfaceFactory $productInterfaceFactory;
        protected ProductRepositoryInterface $productRepository;
        protected State $appState;
        protected EavSetup $eavSetup;
        protected StoreManagerInterface $storeManager;
        protected SourceItemInterfaceFactory $sourceItemFactory;
        protected SourceItemsSaveInterface $sourceItemsSaveInterface;
        protected CategoryLinkManagementInterface $categoryLink;
        protected array $sourceItems = [];
        protected CategoryCollectionFactory $categoryCollectionFactory;

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
            ) 
        {
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
        public function apply() {
            $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
        }

        public function execute()
        {
            $product = $this->productInterfaceFactory->create();
                    
            if ($product->getIdBySku('TST001')) {
                return;
            }

            $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
            $product->setTypeId(Type::TYPE_SIMPLE)
                ->setAttributeSetId($attributeSetId)
                ->setName('Test Product 1')
                ->setSku('TST001')
                ->setUrlKey('testproduct1')
                ->setPrice(29.99)
                ->setVisibility(Visibility::VISIBILITY_BOTH)
                ->setStatus(Status::STATUS_ENABLED);

            $product = $this->productRepository->save($product);

            $categoryTitles = ['Men','Default Category'];
            $categoryIds = $this->categoryCollectionFactory->create()
                ->addAttributeToFilter('name', ['in' => $categoryTitles])
                ->getAllIds();
            $this->categoryLink->assignProductToCategories($product->getSku(), $categoryIds);
            
        }

        /**
         * {@inheritdoc}
         */
        public static function getDependencies()
        {
            return [
            ];
        }
        public function revert() {}

        /**
         * {@inheritdoc}
         */
        public function getAliases()
        {
            return [];
        }
				public static function getVersion()
		   {
			     return '2.0.0';
		   }
    }