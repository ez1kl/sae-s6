import { Component, OnInit, signal, computed } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { BookService } from '../../../services/book.service';
import { Book, Author, Category, SearchCriteria } from '../../../models/models';
import { MemberService } from '../../../services/member.service';

@Component({
  selector: 'app-catalogue',
  standalone: true,
  imports: [RouterLink, FormsModule],
  templateUrl: './catalogue.html',
  styleUrl: './catalogue.scss'
})
export class CatalogueComponent implements OnInit {
  books = signal<Book[]>([]);
  categories = signal<Category[]>([]);
  authors = signal<Author[]>([]);
  totalBooks = signal(0);
  currentPage = signal(1);
  totalPages = signal(1);
  loading = signal(false);
  limit = 12;

  // Critères de recherche
  searchTitle = '';
  searchAuthorId: number | null = null;
  searchCategory: number | null = null;
  searchLanguage = '';
  searchYearFrom: number | null = null;
  searchYearTo: number | null = null;

  pages = computed(() => {
    const total = this.totalPages();
    return Array.from({ length: total }, (_, i) => i + 1);
  });

  constructor(private bookService: BookService) {}

  ngOnInit(): void {
    this.loadCategories();
    this.loadAuthors();
    this.loadBooks();
  }

  loadCategories(): void {
    this.bookService.getCategories().subscribe({
      next: (cats) => this.categories.set(cats)
    });
  }

  loadAuthors(): void {
    this.bookService.getAuthors().subscribe({
      next: (authors) => this.authors.set(authors)
    });
  }

  loadBooks(): void {
    this.loading.set(true);
    const hasSearch = this.searchTitle || this.searchAuthorId
      || this.searchCategory || this.searchLanguage || this.searchYearFrom || this.searchYearTo;

    if (hasSearch) {
      const criteria: SearchCriteria = {
        page: this.currentPage(),
        limit: this.limit
      };
      if (this.searchTitle) criteria.title = this.searchTitle;
      if (this.searchAuthorId) criteria.author = this.searchAuthorId;
      if (this.searchCategory) criteria.category = this.searchCategory;
      if (this.searchLanguage) criteria.language = this.searchLanguage;
      if (this.searchYearFrom) criteria.yearFrom = this.searchYearFrom;
      if (this.searchYearTo) criteria.yearTo = this.searchYearTo;

      this.bookService.searchBooks(criteria).subscribe({
        next: (res) => {
          this.books.set(res.data);
          this.totalBooks.set(res.meta.total);
          this.totalPages.set(res.meta.totalPages);
          this.loading.set(false);
        },
        error: () => this.loading.set(false)
      });
    } else {
      this.bookService.getBooks(this.currentPage(), this.limit).subscribe({
        next: (res) => {
          this.books.set(res.data);
          this.totalBooks.set(res.meta.total);
          this.totalPages.set(res.meta.totalPages);
          this.loading.set(false);
        },
        error: () => this.loading.set(false)
      });
    }
  }

  search(): void {
    this.currentPage.set(1);
    this.loadBooks();
  }

  resetSearch(): void {
    this.searchTitle = '';
    this.searchAuthorId = null;
    this.searchCategory = null;
    this.searchLanguage = '';
    this.searchYearFrom = null;
    this.searchYearTo = null;
    this.currentPage.set(1);
    this.loadBooks();
  }

  goToPage(page: number): void {
    this.currentPage.set(page);
    this.loadBooks();
  }
}
