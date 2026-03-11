import { Component, OnInit, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { BookService } from '../../../services/book.service';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './home.html',
  styleUrl: './home.scss'
})
export class HomeComponent implements OnInit {
  totalBooks = signal(0);
  totalAuthors = signal(0);
  totalCategories = signal(0);

  constructor(private bookService: BookService) {}

  ngOnInit(): void {
    this.bookService.getBooks(1, 1).subscribe({
      next: (res) => this.totalBooks.set(res.total)
    });
    this.bookService.getAuthors().subscribe({
      next: (authors) => this.totalAuthors.set(authors.length)
    });
    this.bookService.getCategories().subscribe({
      next: (cats) => this.totalCategories.set(cats.length)
    });
  }
}
