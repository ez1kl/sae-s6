export interface SearchFiltersState {
  searchTitle: string;
  searchAuthorId: number | null;
  searchCategoryIds: number[];
  searchLanguage: string;
  searchYearFrom: number | null;
  searchYearTo: number | null;
}

