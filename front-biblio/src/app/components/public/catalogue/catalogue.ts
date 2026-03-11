import { Component, OnInit, signal, computed } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { BookService } from '../../../services/book.service';
import { Book, Category, SearchCriteria } from '../../../models/models';

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
  totalBooks = signal(0);
  currentPage = signal(1);
  totalPages = signal(1);
  loading = signal(false);
  limit = 12;

  // Critères de recherche
  searchTitre = '';
  searchAuteur = '';
  searchCategorie: number | null = null;
  searchLangue = '';
  searchDateMin = '';
  searchDateMax = '';

  pages = computed(() => {
    const total = this.totalPages();
    return Array.from({ length: total }, (_, i) => i + 1);
  });

  constructor(private bookService: BookService) {}

  ngOnInit(): void {
    this.loadCategories();
    this.loadBooks();
  }

  loadCategories(): void {
    this.bookService.getCategories().subscribe({
      next: (cats) => this.categories.set(cats)
    });
  }

  loadBooks(): void {
    this.loading.set(true);
    const hasSearch = this.searchTitre || this.searchAuteur || this.searchCategorie
      || this.searchLangue || this.searchDateMin || this.searchDateMax;

    if (hasSearch) {
      const criteria: SearchCriteria = {
        page: this.currentPage(),
        limit: this.limit
      };
      if (this.searchTitre) criteria.titre = this.searchTitre;
      if (this.searchAuteur) criteria.auteur = this.searchAuteur;
      if (this.searchCategorie) criteria.categorie = this.searchCategorie;
      if (this.searchLangue) criteria.langue = this.searchLangue;
      if (this.searchDateMin) criteria.dateMin = this.searchDateMin;
      if (this.searchDateMax) criteria.dateMax = this.searchDateMax;

      this.bookService.searchBooks(criteria).subscribe({
        next: (res) => {
          this.books.set(res.items);
          this.totalBooks.set(res.total);
          this.totalPages.set(res.totalPages);
          this.loading.set(false);
        },
        error: () => this.loading.set(false)
      });
    } else {
      this.bookService.getBooks(this.currentPage(), this.limit).subscribe({
        next: (res) => {
          this.books.set(res.items);
          this.totalBooks.set(res.total);
          this.totalPages.set(res.totalPages);
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
    this.searchTitre = '';
    this.searchAuteur = '';
    this.searchCategorie = null;
    this.searchLangue = '';
    this.searchDateMin = '';
    this.searchDateMax = '';
    this.currentPage.set(1);
    this.loadBooks();
  }

  goToPage(page: number): void {
    this.currentPage.set(page);
    this.loadBooks();
  }
}
