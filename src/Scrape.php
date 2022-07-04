<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{   
    private $crawler;
    private array $products = [];

    public function __construct($intialUrl)
    {
        $this->crawler = ScrapeHelper::fetchDocument($intialUrl);
    }

    public function run(): void
    {
        $pages = ScrapeHelper::findPages($this->crawler);
        foreach ($pages as $page) {
            $this->crawler = ScrapeHelper::fetchDocument($page);
            $this->crawler->filter('html > body > div > div > div')->children()->each(function (Crawler $node) {
                $this->products = array_merge($this->products, ScrapeHelper::parseProducts($node));
            });
        }
        $this->products = ScrapeHelper::deDuplication($this->products);
        file_put_contents('output.json', json_encode($this->products));
    }

}

$scrape = new Scrape('https://www.magpiehq.com/developer-challenge/smartphones');
$scrape->run();
