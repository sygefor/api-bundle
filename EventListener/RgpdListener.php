<?php

namespace Sygefor\Bundle\ApiBundle\EventListener;

use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RgpdListener
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * LastLoginListener constructor.
     *
     * @param Router               $router
     * @param TokenStorage         $tokenStorage
     * @param RequestStack         $requestStack
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(Router $router, TokenStorage $tokenStorage, RequestStack $requestStack, AuthorizationChecker $authorizationChecker)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Redirect to CGU page if CGU are not validated for User Organization.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user && $user instanceof AbstractTrainee && (!$user->getCgu() || !$user->getConsent())) {
                $request = $this->requestStack->getCurrentRequest();
                $route = $request->get('_route');
                if (0 !== strpos($route, 'api.account.rgpd') && $this->authorizationChecker->isGranted('ROLE_USER', $user)) {
                    $redirectUrl = $this->router->generate('api.account.rgpd');
                    $response = new RedirectResponse($redirectUrl);
                    $event->setResponse($response);
                }
            }
        }
    }
}
