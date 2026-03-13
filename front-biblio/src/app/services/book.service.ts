import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';
import { Book, Author, Category, PaginatedResponse, SearchCriteria, ReservationStatusResponse } from '../models/models';

@Injectable({ providedIn: 'root' })
export class BookService {
  private readonly apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getBooks(page: number = 1, limit: number = 12): Observable<PaginatedResponse<Book>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());
    return this.http.get<PaginatedResponse<Book>>(`${this.apiUrl}/books`, { params });
  }

  getBook(id: number): Observable<Book> {
    return this.http.get<Book>(`${this.apiUrl}/books/${id}`);
  }

  getReservationStatus(bookId: number): Observable<ReservationStatusResponse> {
    return this.http.get<ReservationStatusResponse>(
      `${this.apiUrl}/books/${bookId}/reservation-status`
    );
  }

  searchBooks(criteria: SearchCriteria): Observable<PaginatedResponse<Book>> {
    let params = new HttpParams();
    if (criteria.title) params = params.set('title', criteria.title);
    if (criteria.author) params = params.set('author', criteria.author.toString());
    if (criteria.categories?.length) {
      params = params.set(
        'categories',
        criteria.categories.slice(0, 3).join(',') // On construit un string avec les catégories séparées par une virugle
      );
    }
    if (criteria.language) params = params.set('language', criteria.language);
    if (criteria.yearFrom) params = params.set('yearFrom', criteria.yearFrom.toString());
    if (criteria.yearTo) params = params.set('yearTo', criteria.yearTo.toString());
    if (criteria.page) params = params.set('page', criteria.page.toString());
    if (criteria.limit) params = params.set('limit', criteria.limit.toString());
    return this.http.get<PaginatedResponse<Book>>(`${this.apiUrl}/books/search`, { params });
  }

  getAuthors(): Observable<Author[]> {
    return this.http.get<Author[]>(`${this.apiUrl}/authors`);
  }

  getAuthor(id: number): Observable<Author> {
    return this.http.get<Author>(`${this.apiUrl}/authors/${id}`);
  }

  getCategories(): Observable<Category[]> {
    return this.http.get<Category[]>(`${this.apiUrl}/categories`);
  }
}
