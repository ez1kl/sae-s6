import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const roleGuard: CanActivateFn = (route) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (!authService.isLoggedIn()) {
    router.navigate(['/login']);
    return false;
  }

  const requiredRoles = route.data?.['roles'] as string[] | undefined;
  if (!requiredRoles || requiredRoles.length === 0) {
    return true;
  }

  const userRoles = authService.userRoles();
  const hasRole = requiredRoles.some(role => userRoles.includes(role));

  if (!hasRole) {
    router.navigate(['/']);
    return false;
  }

  return true;
};
