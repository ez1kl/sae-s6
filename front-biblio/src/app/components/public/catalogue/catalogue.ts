import { Component, OnInit, signal, computed } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { BookService } from '../../../services/book.service';
import { Book, Author, Category, SearchCriteria } from '../../../models/models';
import { SearchFiltersStorageService } from '../../../services/search-filters-storage.service';
import { SearchFiltersState } from '../../../models/search-filters.model';

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
  readonly maxCategories = 3;

  readonly languageOptions: { code: string; label: string }[] = [
    { code: 'fr', label: 'Français' },
    { code: 'en', label: 'Anglais' },
    { code: 'es', label: 'Espagnol' },
    { code: 'ja', label: 'Japonais' },
  ];

  // Critères de recherche (signals pour la réactivité)
  searchTitle = '';
  searchAuthorText = signal('');
  searchCategoryIds: number[] = [];
  searchLanguage = '';
  searchYearFromDate = '';
  searchYearToDate = '';

  pages = computed(() => {
    const total = this.totalPages();
    return Array.from({ length: total }, (_, i) => i + 1);
  });

  showAuthorDropdown = signal(false);

  filteredAuthors = computed(() => {
    const q = this.searchAuthorText().toLowerCase().trim();
    if (!q) return this.authors();
    return this.authors().filter(a =>
      a.firstName.toLowerCase().includes(q) || a.lastName.toLowerCase().includes(q)
    );
  });

  constructor(
    private bookService: BookService,
    private searchFiltersStorage: SearchFiltersStorageService
  ) {}

  ngOnInit(): void {
    this.restoreFilters();
    this.loadCategories();
    this.loadAuthors();
    this.loadBooks();
  }

  selectAuthor(author: Author | null): void {
    if (author) {
      this.searchAuthorText.set(author.firstName + ' ' + author.lastName);
    } else {
      this.searchAuthorText.set('');
    }
    this.showAuthorDropdown.set(false);
  }

  onAuthorBlur(): void {
    setTimeout(() => this.showAuthorDropdown.set(false), 150);
  }

  private getAuthorIdFromText(): number | null {
    const q = this.searchAuthorText().trim().toLowerCase();
    if (!q) return null;
    const match = this.authors().find(a =>
      `${a.firstName} ${a.lastName}`.toLowerCase() === q ||
      `${a.lastName} ${a.firstName}`.toLowerCase() === q
    );
    return match ? match.id : null;
  }

  private getYearFromDate(dateStr: string): number | null {
    if (!dateStr) return null;
    const year = parseInt(dateStr.substring(0, 4), 10);
    return isNaN(year) ? null : year;
  }

  private buildFiltersState(): SearchFiltersState {
    return {
      searchTitle: this.searchTitle,
      searchAuthorText: this.searchAuthorText(),
      searchCategoryIds: this.searchCategoryIds,
      searchLanguage: this.searchLanguage,
      searchYearFromDate: this.searchYearFromDate,
      searchYearToDate: this.searchYearToDate
    };
  }

  private restoreFilters(): void {
    const saved = this.searchFiltersStorage.load();
    if (!saved) return;
    this.searchTitle = saved.searchTitle;
    this.searchAuthorText.set(saved.searchAuthorText ?? '');
    this.searchCategoryIds = saved.searchCategoryIds ?? [];
    this.searchLanguage = saved.searchLanguage;
    this.searchYearFromDate = saved.searchYearFromDate ?? '';
    this.searchYearToDate = saved.searchYearToDate ?? '';
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
    const yearFrom = this.getYearFromDate(this.searchYearFromDate);
    const yearTo = this.getYearFromDate(this.searchYearToDate);
    const authorId = this.getAuthorIdFromText();
    const hasSearch = this.searchTitle || authorId
      || this.searchCategoryIds.length > 0 || this.searchLanguage || yearFrom || yearTo;

    if (hasSearch) {
      const criteria: SearchCriteria = {
        page: this.currentPage(),
        limit: this.limit
      };
      if (this.searchTitle) criteria.title = this.searchTitle;
      if (authorId) criteria.author = authorId;
      if (this.searchCategoryIds.length > 0) criteria.categories = this.searchCategoryIds;
      if (this.searchLanguage) criteria.language = this.searchLanguage;
      if (yearFrom) criteria.yearFrom = yearFrom;
      if (yearTo) criteria.yearTo = yearTo;

      this.searchFiltersStorage.save(this.buildFiltersState());

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
    this.searchAuthorText.set('');
    this.searchCategoryIds = [];
    this.searchLanguage = '';
    this.searchYearFromDate = '';
    this.searchYearToDate = '';
    this.currentPage.set(1);
    this.searchFiltersStorage.clear();
    this.loadBooks();
  }

  goToPage(page: number): void {
    this.currentPage.set(page);
    this.loadBooks();
  }

  isCategorySelected(id: number): boolean {
    return this.searchCategoryIds.includes(id);
  }

  categoryDisabled(catId: number): boolean {
    return (
      this.searchCategoryIds.length >= this.maxCategories &&
      !this.isCategorySelected(catId)
    );
  }

  toggleCategory(id: number, checked: boolean): void {
    if (checked) {
      if (this.searchCategoryIds.length < this.maxCategories) {
        this.searchCategoryIds = [...this.searchCategoryIds, id];
      }
    } else {
      this.searchCategoryIds = this.searchCategoryIds.filter((x) => x !== id);
    }
  }

  getLanguageLabel(code?: string | null): string {
    if (!code) return '';
    const option = this.languageOptions.find((lang) => lang.code === code);
    return option?.label ?? code.toUpperCase();
  }
}
