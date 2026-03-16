import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';
import { roleGuard } from './guards/role.guard';

export const routes: Routes = [
  {
    path: '',
    loadComponent: () => import('./components/public/home/home').then(m => m.HomeComponent)
  },
  {
    path: 'catalogue',
    loadComponent: () => import('./components/public/catalogue/catalogue').then(m => m.CatalogueComponent)
  },
  {
    path: 'livres/:id',
    loadComponent: () => import('./components/public/book-detail/book-detail').then(m => m.BookDetailComponent)
  },
  {
    path: 'auteurs',
    loadComponent: () => import('./components/public/authors/authors').then(m => m.AuthorsComponent)
  },
  {
    path: 'auteurs/:id',
    loadComponent: () => import('./components/public/author-detail/author-detail').then(m => m.AuthorDetailComponent)
  },
  {
    path: 'login',
    loadComponent: () => import('./components/auth/login/login').then(m => m.LoginComponent)
  },
  {
    path: 'mon-espace',
    loadComponent: () => import('./components/member/dashboard/dashboard').then(m => m.DashboardComponent),
    canActivate: [authGuard]
  },
  {
    path: 'mon-profil',
    loadComponent: () => import('./components/member/profile/profile').then(m => m.ProfileComponent),
    canActivate: [authGuard]
  },
  {
    path: '**',
    redirectTo: ''
  }
];
