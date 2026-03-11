<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use App\Repository\ReservationRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
#[IsGranted('ROLE_USER')]
class MemberController extends AbstractController
{
    #[Route('/profile', name: 'api_me_profile', methods: ['GET'])]
    public function profile(MemberRepository $memberRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $member = $memberRepository->findOneBy(['user' => $user]);

        if (!$member) {
            return $this->json(['error' => 'Profil adhérent introuvable.'], 404);
        }

        return $this->json($member, 200, [], ['groups' => 'member:read']);
    }

    #[Route('/loans', name: 'api_me_loans', methods: ['GET'])]
    public function loans(MemberRepository $memberRepository, LoanRepository $loanRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $member = $memberRepository->findOneBy(['user' => $user]);

        if (!$member) {
            return $this->json(['error' => 'Profil adhérent introuvable.'], 404);
        }

        $loans = $loanRepository->findByMember($member);

        return $this->json($loans, 200, [], ['groups' => 'loan:read']);
    }

    #[Route('/reservations', name: 'api_me_reservations', methods: ['GET'])]
    public function reservations(MemberRepository $memberRepository, ReservationRepository $reservationRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $member = $memberRepository->findOneBy(['user' => $user]);

        if (!$member) {
            return $this->json(['error' => 'Profil adhérent introuvable.'], 404);
        }

        $reservations = $reservationRepository->findByMember($member);

        return $this->json($reservations, 200, [], ['groups' => 'reservation:read']);
    }

    #[Route('/reservations', name: 'api_me_reservations_create', methods: ['POST'])]
    public function createReservation(
        Request $request,
        MemberRepository $memberRepository,
        BookRepository $bookRepository,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $member = $memberRepository->findOneBy(['user' => $user]);

        if (!$member) {
            return $this->json(['error' => 'Profil adhérent introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['bookId'])) {
            return $this->json(['error' => 'Le champ bookId est requis.'], 400);
        }

        $book = $bookRepository->find($data['bookId']);
        if (!$book) {
            return $this->json(['error' => 'Livre introuvable.'], 404);
        }

        $existing = $reservationRepository->findOneByBookId($book->getId());
        if ($existing) {
            return $this->json(['error' => 'Ce livre est déjà réservé.'], 409);
        }

        $reservation = new \App\Entity\Reservation();
        $reservation->setBook($book);
        $reservation->setMember($member);
        $reservation->setCreatedAt(new \DateTime());

        $em->persist($reservation);
        $em->flush();

        return $this->json($reservation, 201, [], ['groups' => 'reservation:read']);
    }

    #[Route('/reservations/{id}', name: 'api_me_reservations_delete', methods: ['DELETE'])]
    public function cancelReservation(
        int $id,
        MemberRepository $memberRepository,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $member = $memberRepository->findOneBy(['user' => $user]);

        if (!$member) {
            return $this->json(['error' => 'Profil adhérent introuvable.'], 404);
        }

        $reservation = $reservationRepository->find($id);
        if (!$reservation) {
            return $this->json(['error' => 'Réservation introuvable.'], 404);
        }

        if ($reservation->getMember()->getId() !== $member->getId()) {
            return $this->json(['error' => 'Vous ne pouvez annuler que vos propres réservations.'], 403);
        }

        $em->remove($reservation);
        $em->flush();

        return $this->json(null, 204);
    }
}
