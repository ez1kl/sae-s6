import { Component, OnInit, signal, computed } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { BookService } from '../../../services/book.service';
import { Author } from '../../../models/models';

@Component({
  selector: 'app-authors',
  standalone: true,
  imports: [RouterLink, FormsModule],
  templateUrl: './authors.html',
  styleUrl: './authors.scss'
})
export class AuthorsComponent implements OnInit {
  authors = signal<Author[]>([]);
  loading = signal(true);
  searchQuery = signal('');

  filteredAuthors = computed(() => {
    const q = this.searchQuery().toLowerCase().trim();
    if (!q) return this.authors();
    return this.authors().filter(a =>
      a.firstName.toLowerCase().includes(q) || a.lastName.toLowerCase().includes(q)
    );
  });

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
