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
            // Admin/librarian users without a member profile
            return $this->json([
                'id' => null,
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
                'isMember' => false,
            ], 200);
        }

        return $this->json($member, 200, [], ['groups' => 'member:read']);
    }

    #[Route('/profile', name: 'api_me_profile_update', methods: ['PUT'])]
    public function updateProfile(
        Request $request,
        MemberRepository $memberRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $member = $memberRepository->findOneBy(['user' => $user]);

        if (!$member) {
            return $this->json(['error' => 'Profil adhérent introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Corps JSON invalide.'], 400);
        }

        if (array_key_exists('phoneNumber', $data)) {
            $member->setPhoneNumber($data['phoneNumber']);
        }

        if (array_key_exists('address', $data)) {
            $address = $data['address'];

            if ($address === null) {
                $address = '';
            }

            $member->setAddress($address);
        }

        $em->flush();

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
        LoanRepository $loanRepository,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $member = $memberRepository->findOneBy(['user' => $user]);

        if (!$member) {
            return $this->json(['error' => 'Profil adhérent introuvable.'], 404);
        }

        if ($member->isSuspended()) {
            return $this->json(['error' => 'Votre compte est suspendu. Impossible de réserver.'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['bookId'])) {
            return $this->json(['error' => 'Le champ bookId est requis.'], 400);
        }

        $memberReservationCount = $reservationRepository->countByMember($member);
        if ($memberReservationCount >= 3) {
            return $this->json(['error' => 'Vous ne pouvez pas reserver plus de 3 livres.'], 409);
        }

        $book = $bookRepository->find($data['bookId']);
        if (!$book) {
            return $this->json(['error' => 'Livre introuvable.'], 404);
        }

        $activeLoan = $loanRepository->findActiveByBookId($book->getId());
        if ($activeLoan) {
            return $this->json(['error' => 'Ce livre est actuellement emprunte et ne peut pas etre reserve.'], 409);
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
