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
        $this->setDescription('Populate database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Populate database...');

        $db = $this->app->getContainer()->get('db');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");

        $faker = \Faker\Factory::create('fr_FR');

        $companyIds = [];
        $officeIds = [];
        $companyHeadOffice = [];

        $numCompanies = rand(2, 4);

        for ($c = 0; $c < $numCompanies; $c++) {
            $name = $faker->company;
            $phone = preg_replace('/[^0-9+]/', '', $faker->phoneNumber);
            $email = $faker->companyEmail;
            $website = $faker->url;
            $image = 'https://via.placeholder.com/200x100?text='
                . rawurlencode($name);

            $db->getConnection()->statement(
                "INSERT INTO `companies` (name, phone, email, website, image, "
                . "created_at, updated_at) VALUES (?, ?, ?, ?, ?, now(), now())",
                [$name, $phone, $email, $website, $image]
            );

            $companyId = (int)$db->getConnection()->getPdo()->lastInsertId();
            $companyIds[] = $companyId;

            $officeCount = rand(2, 3);
            for ($o = 0; $o < $officeCount; $o++) {
                $addr = $faker->streetAddress;
                $city = $faker->city;
                $zip = preg_replace('/[^0-9]/', '', $faker->postcode);
                $country = 'France';
                $officeEmail = 'contact+' . $companyId . '+' . ($o + 1) . '@'
                    . strtolower(preg_replace('/\\W+/', '', $name)) . '.fr';
                $officeName = ($o === 0 ? 'Siège social - ' : 'Bureau - ')
                    . $city;

                $db->getConnection()->statement(
                    "INSERT INTO `offices` (name, address, city, zip_code, "
                    . "country, email, created_at, updated_at, company_id) "
                    . "VALUES (?, ?, ?, ?, ?, ?, now(), now(), ?)",
                    [$officeName, $addr, $city, $zip, $country, $officeEmail,
                        $companyId]
                );

                $officeId = (int)$db->getConnection()->getPdo()->lastInsertId();
                $officeIds[] = $officeId;

                if ($o === 0) {
                    $companyHeadOffice[$companyId] = $officeId;
                }
            }
        }

        $jobs = [
            'Développeur', 'Ingénieur', 'Manager', 'Testeur', 'DBA',
            'Administrateur réseau', 'Chef de projet', 'Analyste', 'Consultant'
        ];
        $employeeCount = 10;

        for ($e = 0; $e < $employeeCount; $e++) {
            $first = $faker->firstName;
            $last = $faker->lastName;
            $officeId = $officeIds[array_rand($officeIds)];
            $email = strtolower(
                preg_replace('/\\W+/', '', $first . '.' . $last)
            ) . '@' . strtolower($faker->domainName);
            $phone = preg_replace('/[^0-9+]/', '', $faker->phoneNumber);
            $job = $jobs[array_rand($jobs)];

            $db->getConnection()->statement(
                "INSERT INTO `employees` (first_name, last_name, office_id, "
                . "email, phone, job_title, created_at, updated_at) "
                . "VALUES (?, ?, ?, ?, ?, ?, now(), now())",
                [$first, $last, $officeId, $email, $phone, $job]
            );
        }

        foreach ($companyIds as $companyId) {
            $headOfficeId = $companyHeadOffice[$companyId] ?? $officeIds[0];
            if ($headOfficeId) {
                $db->getConnection()->statement(
                    "UPDATE companies SET head_office_id = ? WHERE id = ?",
                    [$headOfficeId, $companyId]
                );
            }
        }

        $output->writeln('Database populated successfully!');
        return 0;
    }
}
