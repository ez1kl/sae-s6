export interface Author {
  id: number;
  lastName: string;
  firstName: string;
  nationality: string;
  birthDate?: string | null;
  deathDate?: string | null;
}

export interface Book {
  id: number;
  title: string;
  releaseYear: number;
  language: string;
  author?: Author;
  categories?: Category[];
}

export interface ReservationStatusResponse {
  reservable: boolean;
}

export interface Category {
  id: number;
  name: string;
  description?: string | null;
}

export interface Member {
  id: number;
  firstName: string;
  lastName: string;
  membershipDate: string;
  birthDate: string | null;
  phoneNumber: string | null;
  address: string | null;
}

export interface Loan {
  id: number;
  loanDate: string;
  dueDate: string;
  returnDate: string | null;
  book?: Book;
}

export interface Reservation {
  id: number;
  createdAt: string;
  book?: Book;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
}

export interface SearchCriteria {
  title?: string;
  author?: number;
  category?: number;
  language?: string;
  yearFrom?: number;
  yearTo?: number;
  page?: number;
  limit?: number;
}
