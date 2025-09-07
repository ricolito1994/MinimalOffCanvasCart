<?php declare(strict_types=1);

namespace MinimalOffCanvasCart\Core\Content\MinimalOffCanvasCartEntity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(MinimalOffCanvasCartEntityEntity $entity)
 * @method void set(string $key, MinimalOffCanvasCartEntityEntity $entity)
 * @method MinimalOffCanvasCartEntityEntity[] getIterator()
 * @method MinimalOffCanvasCartEntityEntity[] getElements()
 * @method MinimalOffCanvasCartEntityEntity|null get(string $key)
 * @method MinimalOffCanvasCartEntityEntity|null first()
 * @method MinimalOffCanvasCartEntityEntity|null last()
 */
class MinimalOffCanvasCartEntityCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return MinimalOffCanvasCartEntityEntity::class;
    }
}
