import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { environment } from '../environments/environment';
import { Member, Loan, Reservation } from '../models/models';

@Injectable({ providedIn: 'root' })
export class MemberService {
  private readonly apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getProfile(): Observable<Member> {
    // TODO: utiliser l'endpoint de profil membre quand il sera disponible côté API
    return this.http.get<Member>(`${this.apiUrl}/user/me`);
  }

  updateProfile(data: Partial<Member>): Observable<Member> {
    // TODO: utiliser l'endpoint de mise à jour de profil membre quand il sera disponible côté API
    return this.http.put<Member>(`${this.apiUrl}/user/me`, data);
  }

  getMyLoans(): Observable<Loan[]> {
    // TODO: brancher l'endpoint /me/loans quand il sera implémenté côté API
    // En attendant, on renvoie une liste vide pour éviter les erreurs console.
    return of([]);
  }

  getMyReservations(): Observable<Reservation[]> {
    // TODO: brancher l'endpoint /me/reservations quand il sera implémenté côté API
    // En attendant, on renvoie une liste vide pour éviter les erreurs console.
    return of([]);
  }

  reserveBook(bookId: number): Observable<Reservation> {
    // TODO: implémenter la réservation côté API avant d'activer cet appel
    // Pour l'instant on renvoie une erreur observable pour signaler l'absence de fonctionnalité.
    return this.http.post<Reservation>(`${this.apiUrl}/me/reservations`, { bookId });
  }

  cancelReservation(reservationId: number): Observable<void> {
    // TODO: implémenter l'annulation de réservation côté API avant d'activer cet appel
    // Pour l'instant on ne fait rien afin d'éviter des erreurs non gérées.
    return of(void 0);
  }
}
