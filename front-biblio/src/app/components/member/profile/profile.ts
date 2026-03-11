import { Component, OnInit, signal, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { DatePipe } from '@angular/common';
import { MemberService } from '../../../services/member.service';
import { AuthService } from '../../../services/auth.service';
import { Member } from '../../../models/models';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [FormsModule, DatePipe],
  templateUrl: './profile.html',
  styleUrl: './profile.scss'
})
export class ProfileComponent implements OnInit {
  private memberService = inject(MemberService);
  authService = inject(AuthService);

  member = signal<Member | null>(null);
  loading = signal(true);
  saving = signal(false);
  message = signal('');
  messageError = signal(false);

  phoneNumber = '';
  address = '';

  ngOnInit(): void {
    this.memberService.getProfile().subscribe({
      next: (member) => {
        this.member.set(member);
        this.phoneNumber = member.phoneNumber || '';
        this.address = member.address || '';
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }

  save(): void {
    this.saving.set(true);
    this.message.set('');

    this.memberService.updateProfile({
      phoneNumber: this.phoneNumber || null,
      address: this.address || null
    }).subscribe({
      next: (updated) => {
        this.member.set(updated);
        this.message.set('Profil mis à jour avec succès.');
        this.messageError.set(false);
        this.saving.set(false);
      },
      error: (err) => {
        this.message.set(err.error?.message || 'Erreur lors de la mise à jour.');
        this.messageError.set(true);
        this.saving.set(false);
      }
    });
  }
}
