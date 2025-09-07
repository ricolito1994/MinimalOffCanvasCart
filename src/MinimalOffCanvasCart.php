<?php declare(strict_types=1);

namespace MinimalOffCanvasCart;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use MinimalOffCanvasCart\Service\CrossSellingResolver;
use MinimalOffCanvasCart\Service\CustomFieldsInstaller;
use Shopware\Core\Framework\Context;

class MinimalOffCanvasCart extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        // Do stuff such as creating a new payment method
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        // Remove or deactivate the data created by the plugin
    }

    public function activate(ActivateContext $activateContext): void
    {
        $this->customFieldsInstall($activateContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        // Deactivate entities, such as a new payment method
        // Or remove previously created entities
    }

    public function update(UpdateContext $updateContext): void
    {
        //$this->customFieldsInstall($updateContext);
    }

    public function postInstall(InstallContext $installContext): void
    {
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    protected function customFieldsInstall (ActivateContext | UpdateContext $context) : void
    {
        if ($context instanceof ActivateContext) 
            parent::activate($context);
        else 
            parent::update($context);

        $repo = $this->container->get('custom_field_set.repository');
        $installer = new CustomFieldsInstaller($repo);
        $installer->install($context->getContext());

    }
}
