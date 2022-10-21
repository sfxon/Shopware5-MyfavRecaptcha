<?php

namespace MyfavRecaptcha;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Models\Shop\Shop;

class MyfavRecaptcha extends Plugin
{
    public function install(InstallContext $installContext)
    {
        parent::install($installContext);
    }

    public function activate(ActivateContext $activateContext)
    {
        $activateContext->scheduleClearCache(
            ActivateContext::CACHE_LIST_ALL
        );
    }

    public function deactivate(DeactivateContext $deactivateContext)
    {
        $deactivateContext->scheduleClearCache(
            DeactivateContext::CACHE_LIST_ALL
        );
    }

    public function uninstall(UninstallContext $uninstallContext)
    {
        if ($uninstallContext->keepUserData()) {
            $uninstallContext->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
            return;
        }

        $uninstallContext->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }
}
