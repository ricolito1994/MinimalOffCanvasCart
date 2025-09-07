<?php declare(strict_types=1);

namespace MinimalOffCanvasCart\Storefront\Controller;

use Shopware\Storefront\Controller\CartLineItemController as CoreCartLineItemController;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Twig\Environment;
use MinimalOffCanvasCart\Service\CrossSellingResolver;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class CartLineItemControllerDecorator
{
    private CoreCartLineItemController $inner;
    private Environment $twig;
    private CartService $cartService;
    private CrossSellingResolver $crossSellingResolver;

    public function __construct(
        CoreCartLineItemController $inner,
        Environment $twig,
        CartService $cartService,
        CrossSellingResolver $crossSellingResolver
    ) {
        $this->inner = $inner;
        $this->twig = $twig;
        $this->cartService = $cartService;
        $this->crossSellingResolver = $crossSellingResolver;
    }

    #[Route(
        path: '/checkout/line-item/add',
        name: 'frontend.checkout.line-item.add',
        options: ['seo' => false],
        methods: ['POST']
    )]
    public function addLineItems(
        Cart $cart,
        RequestDataBag $requestDataBag,
        Request $request,
        SalesChannelContext $context
    ): Response {
        $response = $this->inner->addLineItems($cart, $requestDataBag, $request, $context);

        if ($request->isXmlHttpRequest()) {
            $updatedCart = $this->cartService->getCart($cart->getToken(), $context);
            $lineItemsData = $requestDataBag->get('lineItems');
            $clickedItem = null;
            $crossSelling = null;
            $options = null;

            if ($lineItemsData) {
                // Usually only one, but we take the last in case of multiple
                $addedIds = array_keys($lineItemsData->all());
               
                $addedId = end($addedIds);

                // Now fetch the item from the updated cart
                $clickedItem = $updatedCart->getLineItems()->get($addedId);
            }

            

            if ($clickedItem && $clickedItem->getReferencedId()) {
                $crossSelling = $this->crossSellingResolver->resolveCrossSelling(
                    $clickedItem->getReferencedId(),
                    $context->getContext()
                );
                if ($clickedItem->getPayload()['options'] ?? false) {
                    $options = $clickedItem->getPayload()['options'];
                }
            }

            $html = $this->twig->render(
                '@MinimalOffCanvasCart/storefront/component/checkout/offcanvas-minimal.html.twig',
                [
                    'cart' => $cart,
                    'lineItem' => $clickedItem,
                    'crossSelling' => $crossSelling,
                    'options' => $options,
                ]
            );

            return new Response($html);
        }

        return $response;
    }

    #[Route(
        path: '/checkout/line-item/delete/{id}',
        name: 'frontend.checkout.line-item.delete',
        options: ['seo' => false],
        methods: ['POST']
    )]
    public function deleteLineItem(
        Cart $cart,
        string $id,
        Request $request,
        SalesChannelContext $context
    ): Response {
        return $this->inner->deleteLineItem($cart, $id, $request, $context);
    }
    
    #[Route(
        path: '/checkout/line-item/change-quantity/{id}',
        name: 'frontend.checkout.line-item.change-quantity',
        options: ['seo' => false],
        methods: ['POST']
    )]
    public function changeQuantity(
        Cart $cart,
        string $id,
        Request $request,
        SalesChannelContext $context
    ): Response {
        return $this->inner->changeQuantity($cart, $id, $request, $context);
    }
}
