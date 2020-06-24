<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Repositories\ManufacturersModelsRepository;
use App\Repositories\ManufacturersRepository;
use App\Repositories\PagesRepository;
use App\Repositories\VehiclesAccessoriesRepository;
use App\Repositories\VehiclesAttributesRepository;
use App\Repositories\VehiclesRepository;
use Illuminate\Console\Command;
use DuskCrawler\Dusk;
use DuskCrawler\Inspector;
use DuskCrawler\Exceptions\InspectionFailed;
use Laravel\Dusk\Browser;
use Facebook\WebDriver\WebDriverBy;

class SeminovosCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:seminovos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches pages and process all data to database';

    protected static $domain = 'seminovos.com.br';
    protected static $baseUrl = 'https://seminovos.com.br/';
    protected static $startUrl = 'https://seminovos.com.br/carro?registrosPagina=50';
    protected $browser;
    protected $mainPagesFetched = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // \Artisan::call('dusk --filter duskSpiderTest');
        \Artisan::call('migrate:fresh');

        $dusk = new Dusk('seminovosbh');

        $dusk->headless()
            ->disableGpu()
            ->noSandbox();
        $dusk->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

        $dusk->start();

        $this->begin($dusk);
    }

    public function begin(Dusk $dusk)
    {
        $pagesRepository = new PagesRepository();
        $startingLink = $pagesRepository->create([
            'url' => self::$startUrl . '&page=1',
            'isCrawled' => false,
        ]);

        $dusk->browse(function ($browser) use ($startingLink) {
            $this->getLinks($browser, $startingLink);
        });

        $dusk->stop();
    }

    protected function getLinks(Browser $browser, $currentUrl)
    {
        $this->info('-> Preparing page processing: ' . $currentUrl->id);
        $this->processCurrentUrl($browser, $currentUrl);

        foreach((new PagesRepository())->findBy('isCrawled', false)->get() as $page) {
            $this->getLinks($browser, $page);
        }
    }

    private function normalizeEncoding($str) {
        $str = strtolower(utf8_decode($str));
        return utf8_encode($str);
    }

    protected function processCurrentUrl(Browser $browser, $currentUrl)
    {
        try {
            $this->info('-> Processing page ' . $currentUrl->id);
            $pagesRepository = new PagesRepository();

            //Check if already crawled
            if ($pagesRepository->findBy('url', $currentUrl->url)->get()->first()->isCrawled == true) {
                $this->info('! Page ' . $currentUrl->id . ' already processed');
                return;
            }

            //Visit URL
            $browser->visit($currentUrl->url);

            $totalPages = (int)$browser->driver->findElements(WebDriverBy::cssSelector('.resultado .pagination-container .info b'))[1]->getText();

            for ($i = 1; $i < $totalPages; $i++) {
                $url = self::$startUrl . '&page=' . ($i + 1);

                if ($this->isValidUrl($url)) {
                    (new PagesRepository())->create([
                        'url' => $url,
                        'isCrawled' => false,
                    ]);
                }
            }

            $this->processVehiclesLinks($browser, $currentUrl);

            //Update current url status to crawled
            $data = [
                'status' => $this->getHttpStatus($browser->driver->getCurrentURL()),
                'title' => $browser->driver->getTitle(),
                'isCrawled' => true
            ];
            $pagesRepository->update($data, $currentUrl->id);
            $this->info('-> Page ' . $currentUrl->id . ' processed');
        } catch (\Exception $e) {
            \Log::info('[PROCESS_CURRENT_URL] ' . $e->getMessage());
        }
    }

    protected function isValidUrl($url)
    {
        $parsed_url = parse_url($url);

        if (isset($parsed_url['host'])) {
            if (strpos($parsed_url['host'], self::$domain) !== false && !(new PagesRepository())->findBy('url', $url)->exists()) {
                return true;
            }
        }

        return false;
    }

    protected function trimUrl($url)
    {
        $url = strtok($url, '#');
        $url = rtrim($url,"/");
        return $url;
    }

    protected function getHttpStatus($url)
    {
        $headers = get_headers($url, 1);
        return intval(substr($headers[0], 9, 3));
    }

    protected function processVehiclesLinks(Browser $browser, $page)
    {
        try {
            $links = $this->fetchVehiclesListFromPage($browser, $page);

            foreach ($links as $index => $link) {
                $browser->visit($link);

                $this->info('-> Fetching: ' . $link);

                $carContent = $browser->driver->findElements(WebDriverBy::cssSelector('.veiculo-conteudo .item'));
                $carDetails = $carContent[0]->findElements(WebDriverBy::cssSelector('.focus-in-focus'));
                $carAttributes = $carContent[0]->findElements(WebDriverBy::cssSelector('dd > span'));
                $carAccessories = $carContent[0]->findElements(WebDriverBy::cssSelector('.full-features li > span'));

                $nameParts = explode(' ', $carDetails[0]->getText());

                $price = explode(' ', $carDetails[2]->getText())[1];
                $price = str_replace('.', '', $price);
                $price = (float)$price;

                $km = explode(' ', $carAttributes[1]->getText() ?? '0 Km')[0];
                $km = str_replace('.', '', $km);
                $km = (float)$km;

                $gearbox = isset($carAttributes[2]) ? $carAttributes[2]->getText() : 'manual';
                $gearbox = $this->normalizeEncoding($gearbox);
                $fuel = isset($carAttributes[4]) ? $carAttributes[4]->getText() : 'gasolina';
                $fuel = $this->normalizeEncoding($fuel);

                $exchange = isset($carAttributes[7]) ? $carAttributes[7]->getText() : '';

                $car = [
                    'name' => $carDetails[0]->getText(),
                    'model' => $nameParts[1],
                    'description' => $carDetails[1]->findElements(WebDriverBy::cssSelector('p'))[0]->getText(),
                    'price' => $price,
                    'attributes' => [
                        'year' => explode('/', $carAttributes[0]->getText())[0],
                        'model' => explode('/', $carAttributes[0]->getText())[1],
                        'km' => $km,
                        'gearbox' => $gearbox,
                        'doors' => (int)$carAttributes[3]->getText() ?? null,
                        'fuel' => $fuel,
                        'color' => strtolower($carAttributes[5]->getText()) ?? null,
                        'plate' => $carAttributes[6]->getText(),
                        'exchangeable' => strtolower($exchange) == 'aceito troca' ? 1 : 0,
                    ],
                    'accessories' => []
                ];

                foreach ($carAccessories as $accessory) {
                    $name = \Str::slug($accessory->getText());
                    $car['accessories'][$name] = 1;
                }

                $manufacturersRepository = new ManufacturersRepository();
                $manufacturer = $manufacturersRepository->findBy('name', $nameParts[0])->get()->first() ?? $manufacturersRepository->create(['name' => $nameParts[0]]);

                $carModelName = $car['name'] . ' ' . $car['description'];
                $manufactorerModelData = [
                    'manufacturer_id' => $manufacturer->id,
                    'name' => $carModelName,
                    'description' => $car['description'],
                    'year' => $car['attributes']['year'],
                    'model' => $car['attributes']['model'],
                ];

                $manufacturersModelsRepository = new ManufacturersModelsRepository();
                $manufacturerModel = $manufacturersModelsRepository->getModel()
                        ->where('name', $carModelName)
                        ->where('year', $car['attributes']['year'])
                        ->where('model', $car['attributes']['model'])
                        ->get()
                        ->first() ?? $manufacturersModelsRepository->create($manufactorerModelData);

                $car['manufacturers_models_id'] = $manufacturerModel->id;
                $car['slug'] = \Str::slug($carModelName) . '-' . time();

                $vehiclesRepository = new VehiclesRepository();
                $carModel = $vehiclesRepository->create($car);

                $car['attributes']['vehicle_id'] = $carModel->id;
                $car['accessories']['vehicle_id'] = $carModel->id;

                $vehiclesAttributesRepository = new VehiclesAttributesRepository();
                $vehiclesAttributesRepository->create($car['attributes']);

                $vehiclesAccessoriesRepository = new VehiclesAccessoriesRepository();
                $vehiclesAccessoriesRepository->create($car['accessories']);

                $this->info('<- Fetched');

                //            $pagesRepository = new PagesRepository();
                //            $pagesRepository->update([
                //                'status' => $this->getHttpStatus($browser->driver->getCurrentURL()),
                //                'title' => $browser->driver->getTitle(),
                //                'isCrawled' => true
                //            ], $page->id);
            }
        } catch (\Exception $e) {
            \Log::info('[PROCESS_VEHICLE_LINK] ' . $e->getMessage() . "\n" . $page->url);
        }
    }

    protected function fetchVehiclesListFromPage($browser, $page)
    {
        $this->info('-> Fetching vehicles link');
        $browser->visit($page->url);

        $linksElements = $browser->driver->findElements(WebDriverBy::cssSelector('.resultado .anuncios .anuncio-container .card-body > a'));
        $links = [];

        foreach ($linksElements as $index => $element) {
            $this->info(' -- Fetching link ' . ($index + 1) . ' of ' . count($linksElements));

            $href = $element->getAttribute('href');
            $link = $this->trimUrl($href);

            $links[] = $link;
        }

        return $links;
    }
}
