<?php

namespace App\Console;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Symfony\Component\Clock\now;

class PopulateDatabaseCommand extends Command
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('db:populate');
        $this->setDescription('Populate database with random data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Populating database with random data...');

        /** @var \Illuminate\Database\Capsule\Manager $db */
        $db = $this->app->getContainer()->get('db');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");

        $faker = Faker::create();

        // Generate companies
        $companies = [];
        for ($i = 1; $i <= rand(2, 4); $i++) {
            $companies[] = [
                'id' => $i,
                'name' => $faker->company,
                'phone' => $faker->phoneNumber,
                'email' => $faker->companyEmail,
                'website' => $faker->url,
                'image' => $faker->imageUrl(640, 480, 'business'),
                'created_at' => now(),
                'updated_at' => now(),
                'head_office_id' => null
            ];
        }
        $db->table('companies')->insert($companies);

        // Generate offices
        $offices = [];
        foreach ($companies as $company) {
            for ($j = 1; $j <= rand(2, 3); $j++) {
                $offices[] = [
                    'id' => count($offices) + 1,
                    'name' => $faker->streetName,
                    'address' => $faker->address,
                    'city' => $faker->city,
                    'zip_code' => $faker->postcode,
                    'country' => $faker->country,
                    'email' => $faker->email,
                    'phone' => $faker->phoneNumber,
                    'company_id' => $company['id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        $db->table('offices')->insert($offices);

        // Generate employees
        $employees = [];
        foreach ($offices as $office) {
            for ($k = 1; $k <= rand(10, 15); $k++) {
                $employees[] = [
                    'id' => count($employees) + 1,
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'office_id' => $office['id'],
                    'email' => $faker->email,
                    'phone' => $faker->phoneNumber,
                    'job_title' => $faker->jobTitle,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        $db->table('employees')->insert($employees);

        // Update head office for each company
        foreach ($companies as $company) {
            $headOfficeId = $offices[array_rand(array_filter($offices, fn($office) => $office['company_id'] === $company['id']))]['id'];
            $db->table('companies')->where('id', $company['id'])->update(['head_office_id' => $headOfficeId]);
        }

        $output->writeln('Database populated successfully!');
        return 0;
    }
}