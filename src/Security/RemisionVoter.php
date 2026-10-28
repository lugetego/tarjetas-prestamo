<?php

namespace App\Security;

use App\Entity\Remision;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RemisionVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'ACCESS' === $attribute && $subject instanceof Remision;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Remision $remision */
        $remision = $subject;

        return $remision->getBiblioteca()->getId() === $user->getBiblioteca()->getId();
    }
}
