<?php

namespace App\Security\Voter;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Décide qui a le droit de voir une commande (détail + facture).
 * Règle : le client propriétaire de la commande, ou un administrateur.
 */
class CommandeVoter extends Voter
{
    public const VOIR = 'COMMANDE_VOIR';

    public function __construct(
        private Security $security,
    ) {
    }

    // 1. Est-ce que ce Voter est concerné par la question ?
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VOIR && $subject instanceof Commande;
    }

    // 2. La réponse : oui (true) ou non (false)
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        // Pas connecté : refusé
        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        // Un administrateur peut tout voir
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Sinon : seulement le propriétaire de la commande
        /** @var Commande $subject */
        return $subject->getUtilisateur()?->getId() === $utilisateur->getId();
    }
}
