<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCommand extends Command
{
// the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:csv';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        // 3. Update the value of the private entityManager variable through injection
        $this->entityManager = $entityManager;

        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setDescription('export csv.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $products = $this->entityManager
            ->getRepository(Product::class)
            ->findby(array(),array('name' => 'asc'));

        $fsObject = new Filesystem();

        $current_dir_path = getcwd();

            $new_dir_path = $current_dir_path . "/Export";

            if (!$fsObject->exists($new_dir_path))
            {
                $old = umask(0);
                $fsObject->mkdir($new_dir_path, 0775);
                $fsObject->chown($new_dir_path, "www-data");
                $fsObject->chgrp($new_dir_path, "www-data");
                umask($old);
            }

        try {
            $new_file_path = $current_dir_path . "/Export/export.csv";

            if (!$fsObject->exists($new_file_path))
            {
                $fsObject->touch($new_file_path);
                $fsObject->chmod($new_file_path, 0777);
                $fsObject->dumpFile($new_file_path, "name;description;price\n");
                foreach ($products as $product){
                    $fsObject->appendToFile($new_file_path, $product->getName().';'.$product->getDescription().';'.$product->getPrice()."\n");
                }
                echo "Fichier d'export créer avec succès au chemin suivant :". $new_file_path;
            }
        } catch (IOExceptionInterface $exception) {
            echo "Error creating file at". $exception->getPath();
        }
   return 0;
    }
}
