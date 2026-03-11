import { Component, OnInit, signal, inject } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { BookService } from '../../../services/book.service';
import { Author } from '../../../models/models';

@Component({
  selector: 'app-author-detail',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './author-detail.html',
  styleUrl: './author-detail.scss'
})
export class AuthorDetailComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private bookService = inject(BookService);

  author = signal<Author | null>(null);
  loading = signal(true);

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.bookService.getAuthor(id).subscribe({
      next: (author) => {
        this.author.set(author);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }
}
