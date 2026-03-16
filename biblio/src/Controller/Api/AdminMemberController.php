<?php

namespace App\Controller\Api;

use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/members')]
#[IsGranted('ROLE_RESPONSABLE')]
class AdminMemberController extends AbstractController
{
    #[Route('', name: 'api_admin_members_list', methods: ['GET'])]
    public function list(Request $request, MemberRepository $memberRepository): JsonResponse
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $page = $request->query->getInt('page', 1);
        $limit = min($request->query->getInt('limit', 20), 100);

        $members = $memberRepository->findFiltered($search, $status, $page, $limit);
        $total = $memberRepository->countFiltered($search, $status);

        $data = array_map(fn($m) => [
            'id' => $m->getId(),
            'firstName' => $m->getFirstName(),
            'lastName' => $m->getLastName(),
            'email' => $m->getUser()?->getEmail(),
            'membershipDate' => $m->getMembershipDate()?->format('Y-m-d'),
            'phoneNumber' => $m->getPhoneNumber(),
            'suspended' => $m->isSuspended(),
        ], $members);

        return $this->json([
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / $limit),
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_admin_members_show', methods: ['GET'])]
    public function show(
        int $id,
        MemberRepository $memberRepository,
        LoanRepository $loanRepository,
        ReservationRepository $reservationRepository,
    ): JsonResponse {
        $member = $memberRepository->find($id);
        if (!$member) {
            return $this->json(['error' => 'Adhérent introuvable.'], 404);
        }

        $activeLoans = $loanRepository->findActiveLoansByMember($member);
        $loanHistory = $loanRepository->findCompletedLoansByMember($member);
        $reservations = $reservationRepository->findByMemberId($id);

        $now = new \DateTime();

        return $this->json([
            'member' => [
                'id' => $member->getId(),
                'firstName' => $member->getFirstName(),
                'lastName' => $member->getLastName(),
                'email' => $member->getUser()?->getEmail(),
                'membershipDate' => $member->getMembershipDate()?->format('Y-m-d'),
                'birthDate' => $member->getBirthDate()?->format('Y-m-d'),
                'phoneNumber' => $member->getPhoneNumber(),
                'address' => $member->getAddress(),
                'suspended' => $member->isSuspended(),
            ],
            'activeLoans' => array_map(fn($l) => [
                'id' => $l->getId(),
                'book' => ['id' => $l->getBook()->getId(), 'title' => $l->getBook()->getTitle()],
                'loanDate' => $l->getLoanDate()->format('Y-m-d'),
                'dueDate' => $l->getDueDate()->format('Y-m-d'),
                'isOverdue' => $l->getDueDate() < $now,
                'daysOverdue' => $l->getDueDate() < $now ? $l->getDueDate()->diff($now)->days : 0,
            ], $activeLoans),
            'loanHistory' => array_map(fn($l) => [
                'id' => $l->getId(),
                'book' => ['id' => $l->getBook()->getId(), 'title' => $l->getBook()->getTitle()],
                'loanDate' => $l->getLoanDate()->format('Y-m-d'),
                'dueDate' => $l->getDueDate()->format('Y-m-d'),
                'returnDate' => $l->getReturnDate()?->format('Y-m-d'),
            ], $loanHistory),
            'reservations' => array_map(fn($r) => [
                'id' => $r->getId(),
                'book' => ['id' => $r->getBook()->getId(), 'title' => $r->getBook()->getTitle()],
                'createdAt' => $r->getCreatedAt()->format('Y-m-d H:i'),
            ], $reservations),
        ]);
    }

    #[Route('/{id}/suspend', name: 'api_admin_members_suspend', methods: ['PUT'])]
    public function toggleSuspend(
        int $id,
        MemberRepository $memberRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        $member = $memberRepository->find($id);
        if (!$member) {
            return $this->json(['error' => 'Adhérent introuvable.'], 404);
        }

        $member->setSuspended(!$member->isSuspended());
        $em->flush();

        return $this->json([
            'id' => $member->getId(),
            'suspended' => $member->isSuspended(),
            'message' => $member->isSuspended() ? 'Adhérent suspendu.' : 'Adhérent réactivé.',
        ]);
    }

    #[Route('/{memberId}/reservations/{resId}', name: 'api_admin_members_cancel_reservation', methods: ['DELETE'])]
    public function cancelReservation(
        int $memberId,
        int $resId,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        $reservation = $reservationRepository->find($resId);
        if (!$reservation || $reservation->getMember()->getId() !== $memberId) {
            return $this->json(['error' => 'Réservation introuvable.'], 404);
        }

        $em->remove($reservation);
        $em->flush();

        return $this->json(null, 204);
    }
}
