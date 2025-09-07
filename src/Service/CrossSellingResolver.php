<?php declare(strict_types=1);

namespace MinimalOffCanvasCart\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingCollection;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CrossSellingResolver
{
    private SystemConfigService $systemConfig;
    private EntityRepository $productRepository, $currencyRepository;

    public function __construct(
        SystemConfigService $systemConfig,
        EntityRepository $productRepository,
        EntityRepository $currencyRepository,
    ) {
        $this->systemConfig = $systemConfig;
        $this->productRepository = $productRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Resolve the cross-selling group to display in the off-canvas. The resolution follows these steps:
     * 1️⃣ Check if the product has a custom field 'cross_selling_index' set and use that position if available.
     * 2️⃣ If not, fall back to the plugin configuration 'globalCrossSellingIndex'.
     */
    public function resolveCrossSelling(string $productId, Context $context): ?array
    {
        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('crossSellings');
        $criteria->addAssociation('crossSellings.assignedProducts');
        $criteria->addAssociation('crossSellings.assignedProducts.product');
        $criteria->addAssociation('crossSellings.assignedProducts.product.cover.media'); // nested
        $criteria->addAssociation('crossSellings.assignedProducts.product.price');

        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search($criteria, $context)->first();

        if (!$product) {
            return null;
        }

        /** @var ProductCrossSellingCollection|null $groups */
        $groups = $product->getCrossSellings();

        if (!$groups || $groups->count() === 0) {
            return null;
        }

        $customFields = $product->getCustomFields() ?? [];

        // 1️⃣ Product-level custom field
        /*$customFieldPosition = $customFields['cross_selling_index'] ?? null;

        // 2️⃣ Plugin config fallbackss
        $configPosition = (int)$this->systemConfig->get('MinimalOffCanvasCart.config.globalCrossSellingIndex');

        // Determine which index to use
        $positionToUse = $customFieldPosition ?? $configPosition;

        // 3️⃣ Try to find the group with the specified position
        $group = $groups->filter(fn(ProductCrossSellingEntity $g) => $g->getPosition() === $positionToUse)->first();*/

        if ($groups) {
            $crossSellingProducts = [];
            $p = [];

            foreach ($groups as $g) {
                foreach ($g->getAssignedProducts() as $ap) {
                    $product = $ap->getProduct();
                    $price = $product ? $product->getPrice()->first() : null;
                    $currencyId = $price ? $price->getCurrencyId() : null;
                    $currencyEntity = $currencyId
                        ? $this->currencyRepository->search(new Criteria([$currencyId]), $context)->first()
                        : null;
                    
                    $crossSellingProducts[] = [
                        'group' => $g->getName(),
                        'productId' => $product ? $product->getId() : null,
                        'name' => $product ? $product->translated['name'] : null,
                        'coverUrl' => $product && $product->getCover() ? $product->getCover()->getMedia()->getUrl() : null,
                        'priceGross' => $product ? number_format($price->getGross(), 2, '.' ,'') : null,
                        'priceNet' => $product ? number_format($price->getNet(), 2, '.' ,'') : null,
                        'currencySymbol' => $currencyEntity ? $currencyEntity->getSymbol() : null,
                    ];
                }
            }

            return $crossSellingProducts;
        }

        // 4️⃣ Fallback: first available group (lowest position)
        // return $groups->sortByPosition()->first();
        return null;
    }
}
