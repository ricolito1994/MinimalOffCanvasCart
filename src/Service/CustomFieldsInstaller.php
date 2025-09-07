<?php declare(strict_types=1);

namespace MinimalOffCanvasCart\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class CustomFieldsInstaller
{
    private EntityRepository $customFieldSetRepository;

    public function __construct(EntityRepository $customFieldSetRepository)
    {
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    public function install(Context $context): void
    {
        // Check if the set already exists
        $criteria = (new Criteria())->addFilter(new EqualsFilter('name', 'minimal_offcanvas_cross_selling'));
        $existing = $this->customFieldSetRepository->search($criteria, $context)->first();

        if ($existing) {
            return; // already installed
        }

        $this->customFieldSetRepository->create([
            [
                'name' => 'minimal_offcanvas_cross_selling',
                'global' => false,
                'config' => [
                    'label' => [
                        'en-GB' => 'Minimal Offcanvas Cross-Selling',
                        'de-DE' => 'Minimal Offcanvas Cross-Selling',
                    ],
                ],
                'customFields' => [
                    [
                        'name' => 'cross_selling_group',
                        'type' => 'entity',
                        'config' => [
                            'label' => [
                                'en-GB' => 'Cross-Selling Group (optional override)',
                                'de-DE' => 'Cross-Selling Gruppe (optionale Überschreibung)',
                            ],
                            'entity' => 'product_cross_selling',
                        ],
                    ],
                ],
                'relations' => [
                    ['entityName' => 'product'],
                ],
            ],
        ], $context);
    }
}
