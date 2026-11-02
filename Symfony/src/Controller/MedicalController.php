<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Filter\FiltersRecords;

class MedicalController extends AbstractController
{
    /**
     * Показує головну сторінку системи.
     *
     * @return Response
     */
    #[Route('/medical', name: 'medical_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('medical/home.html.twig');
    }

       use FiltersRecords;
}