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
  coverImage?: string | null;
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
  email?: string;
  suspended?: boolean;
}

export interface StatsOverview {
  totalBooks: number;
  activeMembers: number;
  currentLoans: number;
  overdueCount: number;
}

export interface MonthlyLoanStat {
  month: string;
  count: number;
}

export interface CategoryLoanStat {
  category: string;
  count: number;
}

export interface OverdueLoan {
  id: number;
  book: { id: number; title: string };
  member: { id: number; firstName: string; lastName: string };
  loanDate: string;
  dueDate: string;
  daysOverdue: number;
}

export interface MemberProfile360 {
  member: Member;
  activeLoans: ActiveLoanDetail[];
  loanHistory: LoanHistoryDetail[];
  reservations: ReservationDetail[];
}

export interface ActiveLoanDetail {
  id: number;
  book: { id: number; title: string };
  loanDate: string;
  dueDate: string;
  isOverdue: boolean;
  daysOverdue: number;
}

export interface LoanHistoryDetail {
  id: number;
  book: { id: number; title: string };
  loanDate: string;
  dueDate: string;
  returnDate: string;
}

export interface ReservationDetail {
  id: number;
  book: { id: number; title: string };
  createdAt: string;
}

export interface LoanRequest {
  memberId: number;
  bookId: number;
  force?: boolean;
}

export interface LoanResult {
  success: boolean;
  loan?: {
    id: number;
    book: { id: number; title: string };
    member: { id: number; firstName: string; lastName: string };
    loanDate: string;
    dueDate: string;
  };
  error?: string;
  warning?: string;
  requireConfirmation?: boolean;
}

export interface LibrarianMemberProfile {
  member: Member & { activeLoansCount: number; maxLoans: number };
  activeLoans: ActiveLoanDetail[];
  reservations: ReservationDetail[];
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
  category?: number; // On pourra le supprimer plus tard
  categories?: number[];
  language?: string;
  yearFrom?: number;
  yearTo?: number;
  page?: number;
  limit?: number;
}
