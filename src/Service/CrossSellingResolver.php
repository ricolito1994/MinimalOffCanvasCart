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

    public function resolveCrossSelling(string $productId, Context $context): ?array
    {
        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('crossSellings');
        $criteria->addAssociation('crossSellings.assignedProducts');
        $criteria->addAssociation('crossSellings.assignedProducts.product');
        $criteria->addAssociation('crossSellings.assignedProducts.product.cover.media');
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
        $customFieldIndex = $customFields['cross_selling_index'] ?? null;

        $configPosition = (int) $this->systemConfig->get('MinimalOffCanvasCart.config.globalCrossSellingIndex');

        $positionToUse = $customFieldIndex ?? $configPosition;

        $group = $groups->filter(
            fn(ProductCrossSellingEntity $g) => $g->getPosition() === (int) $positionToUse
        )->first();

        if (!$group) {
            $group = $groups->sortByPosition()->first();
        }

        if (!$group) {
            return null;
        }

        $crossSellingProducts = [];

        foreach ($group->getAssignedProducts() as $ap) {
            $assigned = $ap->getProduct();

            if (!$assigned) {
                continue;
            }

            $price = $assigned->getPrice()->first();
            $currencyId = $price ? $price->getCurrencyId() : null;
            $currencyEntity = $currencyId
                ? $this->currencyRepository->search(new Criteria([$currencyId]), $context)->first()
                : null;

            $crossSellingProducts[] = [
                'group' => $group->getName(),
                'productId' => $assigned->getId(),
                'name' => $assigned->translated['name'] ?? null,
                'coverUrl' => $assigned->getCover()?->getMedia()?->getUrl(),
                'priceGross' => $price ? number_format($price->getGross(), 2, '.', '') : null,
                'priceNet' => $price ? number_format($price->getNet(), 2, '.', '') : null,
                'currencySymbol' => $currencyEntity?->getSymbol(),
                'customFields' => $assigned->getCustomFields() ?? [],
            ];
        }

        return $crossSellingProducts;
    }

}
