<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('ROLE_BIBLIOTHECAIRE')) {
            return new RedirectResponse('/librarian');
        }

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}

