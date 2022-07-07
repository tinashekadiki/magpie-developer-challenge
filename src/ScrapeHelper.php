<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    public static function findPages(Crawler $crawler): array
    {
        $pages = $crawler->filterXPath('//div[@class="flex flex-wrap justify-center -mx-6"]')->children()->each(function (Crawler $node) {
            return str_replace('../', 'https://www.magpiehq.com/developer-challenge/', $node->attr('href'));
        });

        return $pages;
    }

    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    public static function parseProducts(Crawler $node): array
    {
        $products = [];

        $productName = $node->filterXPath('//span[@class="product-name"]')->text();
        $productCapacity = ScrapeHelper::convertCapacityToMB($node->filterXPath('//span[@class="product-capacity"]')->text());
        $productImage = str_replace('../', 'https://www.magpiehq.com/developer-challenge/', $node->filterXPath('//img')->attr('src'));
        $productPrice = str_replace('£', '', $node->filterXPath('//div[@class="my-8 block text-center text-lg"]')->text());
        $availabilityText = $node->filterXPath('//div[@class="my-4 text-sm block text-center"]')->first()->text();
        $isAvailable = strpos($availabilityText, 'In') !== false;
        $deliveryText = $node->filterXPath('//div[@class="my-4 text-sm block text-center"]')->last()->text();
        if (strpos($deliveryText, 'Availability') !== false) {
            $deliveryText = 'N/A';
        } 
        $deliveryDate = DateStringHelper::parseDeliveryDate($deliveryText);


        $productColors = $node->filterXPath('//div[@class="flex flex-wrap justify-center -mx-2"]')->children()->each(function (Crawler $node) {
            return $node;
        });

        foreach ($productColors as $value) {
            $color = $value->filterXPath('//span[@class="border border-black rounded-full block"]')->attr('data-colour');

            $product = new Product(
                $productName,
                $productPrice,
                $productImage,
                $productCapacity,
                $color,
                $availabilityText,
                $isAvailable,
                $deliveryText,
                $deliveryDate
            );

            $products[] = $product;
        }
        return $products;
    }


    public static function deDuplication(array $products): array
    {
        $uniqueProducts = [];

        foreach ($products as $product) {
            $uniqueProducts[$product->title . $product->color] = $product;
        }

        return array_values($uniqueProducts);
        return $products;
    }

    public static function convertCapacityToMB($productCapacity): int
    {
        if (strpos($productCapacity, 'GB') !== false) {
            return (int) str_replace('GB', '', $productCapacity) * 1024;
        } else {
            return (int) str_replace('MB', '', $productCapacity);
        }
    }
}
