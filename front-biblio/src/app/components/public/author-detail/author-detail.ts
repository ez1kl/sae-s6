import { Component, OnInit, signal, inject } from '@angular/core';
import { DatePipe } from '@angular/common';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { BookService } from '../../../services/book.service';
import { Author, Book } from '../../../models/models';

@Component({
  selector: 'app-author-detail',
  standalone: true,
  imports: [RouterLink, DatePipe],
  templateUrl: './author-detail.html',
  styleUrl: './author-detail.scss'
})
export class AuthorDetailComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private bookService = inject(BookService);

  author = signal<Author | null>(null);
  books = signal<Book[]>([]);
  loading = signal(true);

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.bookService.getAuthor(id).subscribe({
      next: (author) => {
        this.author.set(author);
        this.bookService.searchBooks({ author: id, limit: 100 }).subscribe({
          next: (res) => this.books.set(res.data)
        });
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }
}
