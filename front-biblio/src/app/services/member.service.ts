import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';
import { Member, Loan, Reservation } from '../models/models';

@Injectable({ providedIn: 'root' })
export class MemberService {
  private readonly apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getProfile(): Observable<Member> {
    return this.http.get<Member>(`${this.apiUrl}/me/profile`);
  }

  updateProfile(data: Partial<Member>): Observable<Member> {
    return this.http.put<Member>(`${this.apiUrl}/me/profile`, data);
  }

  getMyLoans(): Observable<Loan[]> {
    return this.http.get<Loan[]>(`${this.apiUrl}/me/loans`);
  }

  getMyReservations(): Observable<Reservation[]> {
    return this.http.get<Reservation[]>(`${this.apiUrl}/me/reservations`);
  }

  reserveBook(bookId: number): Observable<Reservation> {
    return this.http.post<Reservation>(`${this.apiUrl}/me/reservations`, { bookId });
  }

  cancelReservation(reservationId: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/me/reservations/${reservationId}`);
  }
}
