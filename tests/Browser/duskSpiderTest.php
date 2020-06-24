<?php

namespace Tests\Browser;

use App\VehiclesAccessories;
use Facebook\WebDriver\WebDriverBy;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Page;
use App\VehiclesAttributes;

class duskSpiderTest extends DuskTestCase
{
    protected static $domain = 'seminovos.com.br';
    protected static $baseUrl = 'https://seminovos.com.br';
    protected static $startUrl = 'https://seminovos.com.br/carro?registrosPagina=50';

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');

        // dd($this->hexToString('E3'), $this->hexToString('AD'));
    }

    function normalizeEncoding($str) {
        $str = strtolower(utf8_decode($str));
        return utf8_encode($str);

        if (!in_array($str, ['manual', 'gasolina'])) {
            dump('1', $str);
        }

        $str = preg_replace("/(\\\\x)(ad+)/u", '', $str);

        if (!in_array($str, ['manual', 'gasolina'])) {
            dump('2', $str);
        }

        preg_match_all("/(\\\\x)([0-9A-Fa-f]{2}+)/u", $str, $matches);

        foreach ($matches[2] as $i => $hex) {
            dump('3', $hex);
            if (strlen($hex) % 2 != 0) {
                throw new \Exception('String length must be an even number.', 1);
            }

            $hexdec = hexdec($hex);
            $portion = trim(chr($hexdec));
            $str = str_replace($matches[$i], $portion, $str);
        }

        if (!in_array($str, ['manual', 'gasolina'])) {
            dd($matches);
        }

        return $str;
    }

    /** @test */
    public function urlSpider()
    {
        $startingLink = Page::create([
            'url' => self::$startUrl . '&page=1',
            'isCrawled' => false,
        ]);

        $this->browse(function (Browser $browser) use ($startingLink) {
            $this->getLinks($browser, $startingLink);
        });
    }

    protected function getLinks(Browser $browser, $currentUrl)
    {
        $this->processCurrentUrl($browser, $currentUrl);

        try{
            foreach(Page::where('isCrawled', false)->get() as $link) {
                $this->getLinks($browser, $link);
            }
        }catch(\Exception $e){

        }
    }

    protected function processCurrentUrl(Browser $browser, $currentUrl)
    {
        //Check if already crawled
        if (Page::where('url', $currentUrl->url)->first()->isCrawled == true)
            return;

        //Visit URL
        $browser->visit($currentUrl->url);

        $totalPages = (int) $browser->driver->findElements(WebDriverBy::cssSelector('.resultado .pagination-container .info b'))[1]->getText();

        for ($i = 2; $i < $totalPages; $i++) {
            $url = self::$startUrl . '&page=' . $i;

            if ($this->isValidUrl($url)) {
                Page::create([
                    'url' => $url,
                    'isCrawled' => false,
                ]);
            }
        }

        $this->processCarsLinks($browser, $currentUrl);

        // Update current url status to crawled
        $currentUrl->isCrawled = true;
        $currentUrl->status  = $this->getHttpStatus($currentUrl->url);
        $currentUrl->title = $browser->driver->getTitle();
        $currentUrl->save();
    }

    protected function isValidUrl($url)
    {
        $parsed_url = parse_url($url);

        if (isset($parsed_url['host'])) {
            if (strpos($parsed_url['host'], self::$domain) !== false && !Page::where('url', $url)->exists()) {
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

    protected function processCarsLinks($browser, $page)
    {
        try {
            $links = $this->fetchCarLinksFromPage($browser, $page);

            foreach ($links as $index => $link) {
                $browser->visit($link);

                $carContent = $browser->driver->findElements(WebDriverBy::cssSelector('.veiculo-conteudo .item'));
                $carDetails = $carContent[0]->findElements(WebDriverBy::cssSelector('.focus-in-focus'));
                $carAttributes = $carContent[0]->findElements(WebDriverBy::cssSelector('dd > span'));
                $carAccessories = $carContent[0]->findElements(WebDriverBy::cssSelector('.full-features li > span'));

                $nameParts = explode(' ', $carDetails[0]->getText());

                $price = explode(' ', $carDetails[2]->getText())[1];
                $price = str_replace('.', '', $price);
                $price = (float) $price;

                $km = explode(' ', $carAttributes[1]->getText() ?? '0 Km')[0];
                $km = str_replace('.', '', $km);
                $km = (float) $km;

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
                        'doors' => (int) $carAttributes[3]->getText() ?? null,
                        'fuel' => $fuel,
                        'color' => strtolower($carAttributes[5]->getText()) ?? null,
                        'plate' => $carAttributes[6]->getText(),
                        'exchange' => strtolower($exchange) == 'aceito troca' ? true : false,
                    ],
                    'accessories' => []
                ];

                foreach ($carAccessories as $accessory) {
                    $name = \Str::slug($accessory->getText());
                    $car['accessories'][$name] = 1;
                }

                $manufacturer = \App\Manufacturer::where('name', $nameParts[0])->get()->first() ?? \App\Manufacturer::create(['name' => $nameParts[0]]);

                $carModelName = $car['name'] . ' ' . $car['description'];
                $manufactorerModelData = [
                    'manufacturer_id' => $manufacturer->id,
                    'name' => $carModelName,
                    'description' => $car['description'],
                    'year' => $car['attributes']['year'],
                    'model' => $car['attributes']['model'],
                ];
                $manufacturerModel = \App\ManufacturersModels::where('name', $carModelName)->get()->first() ?? \App\ManufacturersModels::create($manufactorerModelData);

                $car['manufacturers_models_id'] = $manufacturerModel->id;
                $carModel = \App\Vehicle::create($car);

                $carAttributes = new VehiclesAttributes($car['attributes']);
                $carAccessoriesObj = new VehiclesAccessories($car['accessories']);
                $carModel->attributes()->save($carAttributes);
                $carModel->accessories()->save($carAccessoriesObj);
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage() . "\n" . $link);
        }
    }

    protected function fetchCarLinksFromPage($browser, $page)
    {
        $browser->visit($page->url);
        $linksElements = $browser->driver->findElements(WebDriverBy::cssSelector('.resultado .anuncios .anuncio-container .card-body > a'));
        $links = [];

        foreach ($linksElements as $element) {
            $href = $element->getAttribute('href');
            $link = $this->trimUrl($href);

            $links[] = $link;
        }

        return $links;
    }
}
