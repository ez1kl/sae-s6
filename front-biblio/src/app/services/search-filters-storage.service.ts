import { Injectable } from '@angular/core';
import { SearchFiltersState } from '../models/search-filters.model';

const LOCAL_STORAGE_SEARCH_FILTERS_KEY = 'biblio_search_filters';

@Injectable({ providedIn: 'root' })
export class SearchFiltersStorageService {
  save(filters: SearchFiltersState): void {
    localStorage.setItem(LOCAL_STORAGE_SEARCH_FILTERS_KEY, JSON.stringify(filters));
  }

  load(): SearchFiltersState | null {
    const raw = localStorage.getItem(LOCAL_STORAGE_SEARCH_FILTERS_KEY);
    if (!raw) return null;
    return JSON.parse(raw) as SearchFiltersState;
  }

  clear(): void {
    localStorage.removeItem(LOCAL_STORAGE_SEARCH_FILTERS_KEY);
  }
}

