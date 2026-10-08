<?php

namespace App\Security\Voter;

use App\Entity\Adresse;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Décide qui a le droit d'utiliser / modifier / supprimer une adresse.
 * Règle : uniquement le client à qui elle appartient.
 */
class AdresseVoter extends Voter
{
    public const GERER = 'ADRESSE_GERER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::GERER && $subject instanceof Adresse;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        /** @var Adresse $subject */
        return $subject->getUtilisateur()?->getId() === $utilisateur->getId();
    }
}
