import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';
import { Book, Author, Category, PaginatedResponse, SearchCriteria } from '../models/models';

@Injectable({ providedIn: 'root' })
export class BookService {
  private readonly apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getBooks(page: number = 1, limit: number = 12): Observable<PaginatedResponse<Book>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());
    return this.http.get<PaginatedResponse<Book>>(`${this.apiUrl}/livres`, { params });
  }

  getBook(id: number): Observable<Book> {
    return this.http.get<Book>(`${this.apiUrl}/livres/${id}`);
  }

  searchBooks(criteria: SearchCriteria): Observable<PaginatedResponse<Book>> {
    let params = new HttpParams();
    if (criteria.titre) params = params.set('titre', criteria.titre);
    if (criteria.auteur) params = params.set('auteur', criteria.auteur);
    if (criteria.categorie) params = params.set('categorie', criteria.categorie.toString());
    if (criteria.langue) params = params.set('langue', criteria.langue);
    if (criteria.dateMin) params = params.set('dateMin', criteria.dateMin);
    if (criteria.dateMax) params = params.set('dateMax', criteria.dateMax);
    if (criteria.page) params = params.set('page', criteria.page.toString());
    if (criteria.limit) params = params.set('limit', criteria.limit.toString());
    return this.http.get<PaginatedResponse<Book>>(`${this.apiUrl}/livres/recherche`, { params });
  }

  getAuthors(): Observable<Author[]> {
    return this.http.get<Author[]>(`${this.apiUrl}/auteurs`);
  }

  getAuthor(id: number): Observable<Author> {
    return this.http.get<Author>(`${this.apiUrl}/auteurs/${id}`);
  }

  getCategories(): Observable<Category[]> {
    return this.http.get<Category[]>(`${this.apiUrl}/categories`);
  }
}
