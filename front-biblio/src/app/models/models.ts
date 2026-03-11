export interface Author {
  id: number;
  nom: string;
  prenom: string;
  dateNaissance: string | null;
  dateDeces: string | null;
  nationalite: string | null;
  photo: string | null;
  description: string | null;
  livres?: Book[];
}

export interface Book {
  id: number;
  titre: string;
  dateSortie: string | null;
  langue: string;
  photoCouverture: string | null;
  auteurs?: Author[];
  categories?: Category[];
  disponible?: boolean;
}

export interface Category {
  id: number;
  nom: string;
  description: string | null;
}

export interface Member {
  id: number;
  nom: string;
  prenom: string;
  dateAdhesion: string;
  dateNaissance: string | null;
  email: string;
  adressePostale: string | null;
  numTel: string | null;
  photo: string | null;
}

export interface Loan {
  id: number;
  dateEmprunt: string;
  dateRetour: string | null;
  livre?: Book;
  adherent?: Member;
  enRetard?: boolean;
}

export interface Reservation {
  id: number;
  dateReservation: string;
  livre?: Book;
  adherent?: Member;
}

export interface PaginatedResponse<T> {
  items: T[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
}

export interface SearchCriteria {
  titre?: string;
  auteur?: string;
  categorie?: number;
  langue?: string;
  dateMin?: string;
  dateMax?: string;
  page?: number;
  limit?: number;
}
