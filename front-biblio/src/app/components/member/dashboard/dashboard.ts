import { Component, OnInit, signal, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { DatePipe } from '@angular/common';
import { MemberService } from '../../../services/member.service';
import { AuthService } from '../../../services/auth.service';
import { Loan, Reservation } from '../../../models/models';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [RouterLink, DatePipe],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.scss'
})
export class DashboardComponent implements OnInit {
  authService = inject(AuthService);
  private memberService = inject(MemberService);

  loans = signal<Loan[]>([]);
  reservations = signal<Reservation[]>([]);
  loading = signal(true);
  cancelMessage = signal('');
  cancelError = signal(false);

  ngOnInit(): void {
    this.loadData();
  }

  loadData(): void {
    this.loading.set(true);
    this.memberService.getMyLoans().subscribe({
      next: (loans) => this.loans.set(loans)
    });
    this.memberService.getMyReservations().subscribe({
      next: (reservations) => {
        this.reservations.set(reservations);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }

  cancelReservation(id: number): void {
    this.memberService.cancelReservation(id).subscribe({
      next: () => {
        this.cancelMessage.set('Réservation annulée.');
        this.cancelError.set(false);
        this.reservations.update(list => list.filter(r => r.id !== id));
      },
      error: (err) => {
        this.cancelMessage.set(err.error?.message || 'Erreur lors de l\'annulation.');
        this.cancelError.set(true);
      }
    });
  }
}
