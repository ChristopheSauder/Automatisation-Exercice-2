<?php

namespace App\Console;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Support\Facades\Schema;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

class PopulateDatabaseCommand extends Command
{
    private App $app;

    public function __construct(App $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    protected function configure(): void
    {
        $this->setName('db:populate');
        $this->setDescription('Populate database');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $faker = FakerFactory::create();
        $db = $this->app->getContainer()->get('db');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");

        $companies = [];
        for ($i = 1; $i <= 4; $i++) {
            $companies[] = [
                'id' => $i,
                'name' => $faker->company,
                'phone' => $faker->phoneNumber,
                'email' => $faker->companyEmail,
                'website' => $faker->url,
                'logo' => "https://picsum.photos/800/300", // Lien différent de faker car faker ne retourne pas une image valide car placeholder ne marche plus
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'head_office_id' => null
            ];
        }

        foreach ($companies as $company) {
            $db->getConnection()->statement("INSERT INTO `companies` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array_values($company));
        }

        $offices = [];
        foreach ($companies as $company) {
            for ($j = 1; $j <= 3; $j++) {
                $offices[] = [
                    'id' => count($offices) + 1,
                    'name' => 'Bureau de ' . $faker->city,
                    'address' => $faker->address,
                    'city' => $faker->city,
                    'postal_code' => $faker->postcode,
                    'country' => $faker->country,
                    'email' => $faker->companyEmail,
                    'phone' => $faker->phoneNumber,
                    'company_id' => $company['id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }

        foreach ($offices as $office) {
            $db->getConnection()->statement("INSERT INTO `offices` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array_values($office));
        }

        for ($k = 1; $k <= 10; $k++) {
            $db->getConnection()->statement("INSERT INTO `employees` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $k,
                $faker->firstName,
                $faker->lastName,
                $faker->numberBetween(1, count($offices)),
                $faker->email,
                null,
                $faker->jobTitle,
                Carbon::now(),
                Carbon::now()
            ]);
        }

        $db->getConnection()->statement("update companies set head_office_id = 1 where id = 1;");
        $db->getConnection()->statement("update companies set head_office_id = 3 where id = 2;");
        $db->getConnection()->statement("update companies set head_office_id = 5 where id = 3;");
        $db->getConnection()->statement("update companies set head_office_id = 7 where id = 4;");


        $output->writeln('Database created successfully with random data!');
        // $output->writeln('image: ' . FakerFactory::create()->imageUrl(800, 600, 'business'));
        return Command::SUCCESS;
    }
}
