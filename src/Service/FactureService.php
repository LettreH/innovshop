<?php

namespace App\Service;

use App\Entity\Commande;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

/**
 * Génère la facture PDF d'une commande.
 */
class FactureService
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function genererPdf(Commande $commande): string
    {
        // 1. On fabrique le HTML de la facture avec Twig
        $html = $this->twig->render('facture/facture.html.twig', [
            'commande' => $commande,
        ]);

        // 2. On configure Dompdf (police qui gère les accents et le symbole €)
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');

        // 3. On transforme le HTML en PDF au format A4
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 4. On renvoie le contenu du fichier PDF
        return $dompdf->output();
    }
}
