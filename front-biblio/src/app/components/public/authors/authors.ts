import { Component, OnInit, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { BookService } from '../../../services/book.service';
import { Author } from '../../../models/models';

@Component({
  selector: 'app-authors',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './authors.html',
  styleUrl: './authors.scss'
})
export class AuthorsComponent implements OnInit {
  authors = signal<Author[]>([]);
  loading = signal(true);

  constructor(private bookService: BookService) {}

  ngOnInit(): void {
    this.bookService.getAuthors().subscribe({
      next: (authors) => {
        this.authors.set(authors);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }
}
