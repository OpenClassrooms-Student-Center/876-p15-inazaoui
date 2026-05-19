<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Noyau de l'application Symfony.
 *
 * Point d'entrée du framework, configure le chargement des bundles,
 * des routes et des services via le trait MicroKernelTrait.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
