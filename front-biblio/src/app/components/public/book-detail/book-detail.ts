import { Component, OnInit, signal, inject } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { BookService } from '../../../services/book.service';
import { MemberService } from '../../../services/member.service';
import { AuthService } from '../../../services/auth.service';
import { Book } from '../../../models/models';

@Component({
  selector: 'app-book-detail',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './book-detail.html',
  styleUrl: './book-detail.scss'
})
export class BookDetailComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private bookService = inject(BookService);
  private memberService = inject(MemberService);
  authService = inject(AuthService);

  book = signal<Book | null>(null);
  loading = signal(true);
  reservable = signal<boolean | null>(null);
  reservationBlockReason = signal<'reserved' | 'loaned' | null>(null);
  reservationMessage = signal('');
  reservationError = signal(false);

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.bookService.getBook(id).subscribe({
      next: (book) => {
        this.book.set(book);
        this.loading.set(false);
        this.bookService.getReservationStatus(id).subscribe({
          next: (status) => {
            this.reservable.set(status.reservable);
            this.reservationBlockReason.set(status.reason ?? null);
          },
          error: () => {
            this.reservable.set(null);
            this.reservationBlockReason.set(null);
          }
        });
      },
      error: () => this.loading.set(false)
    });
  }

  reserve(): void {
    const book = this.book();
    if (!book) return;

    this.memberService.reserveBook(book.id).subscribe({
      next: () => {
        this.reservationMessage.set('Réservation effectuée avec succès !');
        this.reservationError.set(false);
        this.reservable.set(false);
        this.reservationBlockReason.set('reserved');
      },
      error: (err) => {
        const message = err.error?.error || 'Impossible de réserver ce livre.';
        this.reservationMessage.set(message);
        this.reservationError.set(true);
      }
    });
  }

  /** Permet de vérifier si le livre est encore réservable */
  canReserve(): boolean {
    return this.reservable() === true;
  }
}
