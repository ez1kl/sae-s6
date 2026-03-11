import { Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { jwtDecode } from 'jwt-decode';
import { environment } from '../environments/environment';
import { LoginRequest, LoginResponse, Member } from '../models/models';

interface JwtPayload {
  exp: number;
  iat: number;
  roles: string[];
  username: string;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly TOKEN_KEY = 'jwt_token';
  private readonly apiUrl = environment.apiUrl;

  currentUser = signal<Member | null>(null);
  isLoggedIn = computed(() => !!this.getToken() && !this.isTokenExpired());
  userRoles = computed(() => this.getDecodedToken()?.roles ?? []);
  userEmail = computed(() => this.getDecodedToken()?.username ?? '');

  constructor(private http: HttpClient, private router: Router) {
    if (this.isLoggedIn()) {
      this.loadProfile();
    }
  }

  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/login`, credentials).pipe(
      tap(response => {
        localStorage.setItem(this.TOKEN_KEY, response.token);
        this.loadProfile();
      })
    );
  }

  logout(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    this.currentUser.set(null);
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  private getDecodedToken(): JwtPayload | null {
    const token = this.getToken();
    if (!token) return null;
    try {
      return jwtDecode<JwtPayload>(token);
    } catch {
      return null;
    }
  }

  private isTokenExpired(): boolean {
    const decoded = this.getDecodedToken();
    if (!decoded) return true;
    return decoded.exp * 1000 < Date.now();
  }

  private loadProfile(): void {
    this.http.get<Member>(`${this.apiUrl}/me/profile`).subscribe({
      next: (member) => this.currentUser.set(member),
      error: () => this.logout()
    });
  }
}
