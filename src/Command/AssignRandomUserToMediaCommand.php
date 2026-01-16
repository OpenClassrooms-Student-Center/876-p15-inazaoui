<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:media:assign-random-user',
    description: 'Assigne aléatoirement un user_id (entre 2 et 101) à tous les médias existants.'
)]
class AssignRandomUserToMediaCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Charger uniquement les users 2 → 101
        $users = $this->em->getRepository(User::class)->findBy([
            'id' => range(2, 101),
        ]);

        if (empty($users)) {
            $io->error('Aucun utilisateur entre ID 2 et 101 trouvé.');

            return Command::FAILURE;
        }

        $io->success(count($users).' utilisateurs disponibles.');

        // Récupérer tous les médias existants
        $medias = $this->em->getRepository(Media::class)->findAll();
        $total = count($medias);

        $io->section("Médias trouvés : $total");

        if (0 === $total) {
            $io->warning('Aucun média trouvé.');

            return Command::SUCCESS;
        }

        $io->progressStart($total);

        foreach ($medias as $media) {
            // User random parmi les 100 invités
            $randomUser = $users[array_rand($users)];
            $media->setUser($randomUser);

            // ON NE TOUCHE PAS AUX ALBUMS → B validé
            // $media->setAlbum(null); // on laisse l'existant

            $this->em->persist($media);
            $io->progressAdvance();
        }

        $this->em->flush();
        $io->progressFinish();

        $io->success('Tous les médias ont reçu un user_id aléatoire (entre 2 et 101).');

        return Command::SUCCESS;
    }
}
