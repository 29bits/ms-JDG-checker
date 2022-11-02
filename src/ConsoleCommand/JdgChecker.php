<?php

namespace App\ConsoleCommand;

use App\DTO\Company;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:jdg-checker',
    description: 'Sprawdzanie ludzi w CEIDG',
    hidden: false
)]
class JdgChecker extends Command
{
    const CEIDG_ENDPOINT = 'https://dane.biznes.gov.pl/api/ceidg/v2/firma';
    private HttpClientInterface $client;
    private string $token;

    public function __construct(HttpClientInterface $client, string $token)
    {
        parent::__construct();
        $this->client = $client;
        $this->token = $token;
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Ścieżka do pliku csv');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!($handle = fopen($input->getArgument('file'), "r"))) {
            $output->writeln("Nie można otworzyć pliku {$input->getArgument('file')}");
            return Command::FAILURE;
        }

        $handle = fopen('test.csv', 'r');
        fgets($handle);

        while($line = fgetcsv($handle)) {
            $name = $line[0];
            if(!isset($line[5])) {
                continue;
            }

            $id = str_replace('https://aplikacja.ceidg.gov.pl/CEIDG/CEIDG.Public.UI/SearchDetails.aspx?Id=', '', $line[5]);
            if(!$company = $this->getCompany($id)) {
                sleep(5);
                $company = $this->getCompany($id);
            }

            if(!$company) {
                $output->writeln("Nie znaleziono firmy {$name}");
                continue;
            }

            if($company->isCorrect($name)) {
                $output->writeln("{$name} OK");
            } else {
                $output->writeln("{$name} NIE OK");
            }

            sleep(4);
        }

        fclose($handle);

        return Command::SUCCESS;
    }

    public function getCompany(string $companyId): ?Company
    {
        try {
            $response = $this->client->request('GET', sprintf("%s/%s", self::CEIDG_ENDPOINT, $companyId), [
                'auth_bearer' => $this->token,
            ]);
        } catch (TransportExceptionInterface $e) {
            return null;
        }

        if($response->getStatusCode() !== 200) {
            return null;
        }

        $company = null;
        if(isset($response->toArray()['firma'][0])) {
            $company = $response->toArray()['firma'][0];
        }

        if(!$company) {
            return null;
        }

        $name = sprintf("%s %s", $company['wlasciciel']['nazwisko'], $company['wlasciciel']['imie']);
        $isActive = $company['status'] === 'AKTYWNY';
        $hasPkd = $company['pkdGlowny'] === '6201Z' || in_array('6201Z', $company['pkd']);

        return new Company($name, $isActive, $hasPkd);
    }
}
