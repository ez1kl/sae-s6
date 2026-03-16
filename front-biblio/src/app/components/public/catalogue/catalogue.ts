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

  // Mapping des codes vers libellé propres (certains ne sont pas utilisés)
  readonly languageOptions: { code: string; label: string }[] = [
    { code: 'fr', label: 'Français' },
    { code: 'en', label: 'Anglais' },
    // { code: 'de', label: 'Allemand' },
    { code: 'es', label: 'Espagnol' },
    // { code: 'it', label: 'Italien' },
    // { code: 'pt', label: 'Portugais' },
    // { code: 'nl', label: 'Néerlandais' },
    // { code: 'pl', label: 'Polonais' },
    // { code: 'ru', label: 'Russe' },
    { code: 'ja', label: 'Japonais' },
    // { code: 'zh', label: 'Chinois' },
    // { code: 'ar', label: 'Arabe' }
  ];

  // Critères de recherche
  searchTitle = '';
  searchAuthorId: number | null = null;
  searchCategoryIds: number[] = [];
  searchLanguage = '';
  searchYearFrom: number | null = null;
  searchYearTo: number | null = null;

  pages = computed(() => {
    const total = this.totalPages();
    return Array.from({ length: total }, (_, i) => i + 1);
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

  private buildFiltersState(): SearchFiltersState {
    return {
      searchTitle: this.searchTitle,
      searchAuthorId: this.searchAuthorId,
      searchCategoryIds: this.searchCategoryIds,
      searchLanguage: this.searchLanguage,
      searchYearFrom: this.searchYearFrom,
      searchYearTo: this.searchYearTo
    };
  }

  private restoreFilters(): void {
    const saved = this.searchFiltersStorage.load();
    if (!saved) {
      return;
    }

    this.searchTitle = saved.searchTitle;
    this.searchAuthorId = saved.searchAuthorId;
    this.searchCategoryIds = saved.searchCategoryIds ?? [];
    this.searchLanguage = saved.searchLanguage;
    this.searchYearFrom = saved.searchYearFrom;
    this.searchYearTo = saved.searchYearTo;
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
      || this.searchCategoryIds.length > 0 || this.searchLanguage || this.searchYearFrom || this.searchYearTo;

    if (hasSearch) {
      const criteria: SearchCriteria = {
        page: this.currentPage(),
        limit: this.limit
      };
      if (this.searchTitle) criteria.title = this.searchTitle;
      if (this.searchAuthorId) criteria.author = this.searchAuthorId;
      if (this.searchCategoryIds.length > 0) {
        criteria.categories = this.searchCategoryIds;
      }
      if (this.searchLanguage) criteria.language = this.searchLanguage;
      if (this.searchYearFrom) criteria.yearFrom = this.searchYearFrom;
      if (this.searchYearTo) criteria.yearTo = this.searchYearTo;

      // Sauvegarder les filtres utilisés pour la recherche
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
    this.searchAuthorId = null;
    this.searchCategoryIds = [];
    this.searchLanguage = '';
    this.searchYearFrom = null;
    this.searchYearTo = null;
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
}
