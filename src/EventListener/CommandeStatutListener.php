<?php

namespace App\EventListener;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Envoie un email au client quand le statut de sa commande change.
 * Fonctionne quel que soit l'endroit où le statut est modifié (EasyAdmin, etc.).
 */
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Commande::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Commande::class)]
class CommandeStatutListener
{
    // Les commandes dont le statut vient de changer (en attente d'envoi d'email)
    private array $aNotifier = [];

    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    // 1. JUSTE AVANT l'enregistrement : on regarde si le statut a changé
    public function preUpdate(Commande $commande, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('statut')) {
            $this->aNotifier[spl_object_id($commande)] = true;
        }
    }

    // 2. JUSTE APRÈS l'enregistrement : on envoie l'email
    public function postUpdate(Commande $commande, PostUpdateEventArgs $args): void
    {
        $cle = spl_object_id($commande);
        if (!isset($this->aNotifier[$cle])) {
            return;
        }
        unset($this->aNotifier[$cle]);

        $email = (new TemplatedEmail())
            ->from(new Address('commandes@innovshop.fr', 'InnovShop'))
            ->to($commande->getUtilisateur()->getEmail())
            ->subject('Votre commande ' . $commande->getNumero() . ' : nouveau statut')
            ->htmlTemplate('emails/changement_statut.html.twig')
            ->context(['commande' => $commande]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            // Si l'email échoue, le changement de statut reste enregistré
        }
    }
}
