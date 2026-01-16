<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:images:optimize',
    description: 'Optimise les images dans un dossier (défaut : public/uploads)'
)]
class OptimizeImagesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'directory',
                InputArgument::OPTIONAL,
                'Dossier contenant les images à optimiser'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupération du projectDir via le container
        $projectDir = $this->getApplication()->getKernel()->getProjectDir();

        $defaultDirectory = $projectDir.'/public/uploads';
        $directory = $input->getArgument('directory') ?? $defaultDirectory;

        if (!is_dir($directory)) {
            $io->error("Le dossier source n'existe pas : $directory");

            return Command::FAILURE;
        }

        $io->title("Optimisation des images dans : $directory");

        $images = glob($directory.'/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

        if (empty($images)) {
            $io->warning('Aucune image trouvée.');

            return Command::SUCCESS;
        }

        $io->progressStart(count($images));

        foreach ($images as $image) {
            $this->optimize($image);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Optimisation des images terminée !');

        return Command::SUCCESS;
    }

    private function optimize(string $file): void
    {
        $info = getimagesize($file);
        if (!$info) {
            return;
        }

        switch ($info['mime']) {
            case 'image/jpeg':
                $img = @imagecreatefromjpeg($file);
                if ($img) {
                    imagejpeg($img, $file, 75);
                }
                break;

            case 'image/png':
                $img = @imagecreatefrompng($file);
                if ($img) {
                    imagepng($img, $file, 7);
                }
                break;

            case 'image/webp':
                $img = @imagecreatefromwebp($file);
                if ($img) {
                    imagewebp($img, $file, 75);
                }
                break;
        }
    }
}
