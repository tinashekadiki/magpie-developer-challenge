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
        $deliveryDate = ScrapeHelper::parseDeliveryDate($deliveryText);

        $productColors = $node->filterXPath('//div[@class="flex flex-wrap justify-center -mx-2"]')->children()->each(function (Crawler $node) {
            // return $node->filterXPath('//span[@class="border border-black rounded-full block"]')->attr('data-colour');
            return $node;
        });

        // var_dump($productColors);

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
        // var_dump($products);
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

    public static function parseDeliveryDate($var): string
    {
        $ar = explode(' ', $var);
        if (ScrapeHelper::isDate($ar)) {
            $dateParts = ScrapeHelper::findDateParts($ar);
            return $dateParts[0] . '-' . ScrapeHelper::convertMonthToNumber($dateParts[1]) . '-' . $dateParts[2];
        } else {
            return 'N/A';
        }
    }

    public static function isDate($ar)
    {
        if (count($ar) > 3 && count(ScrapeHelper::findDateParts($ar)) == 3) {
            return true;
        } else {
            return false;
        }
    }

    public static function findDateParts($ar)
    {
        $date = [];
        foreach ($ar as $value) {
            if (ScrapeHelper::isMonth($value)) {
                array_push($date, $value);
            }
            if (ScrapeHelper::isDay($value)) {
                array_push($date, $value);
            }
            if (ScrapeHelper::isYear($value)) {
                array_push($date, $value);
            }
        }
        return $date;
    }

    public static function isDay($value)
    {
        return preg_match('/^[0-9]{1,2}$/', $value) && (int) $value <= 31;
    }

    public static function isYear($value)
    {
        return preg_match('/^[0-9]{4}$/', $value) && (int) $value <= 9999 && (int) $value >= 1500;
    }

    public static function isMonth($string)
    {
        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        foreach ($months as $month) {
            if (strpos($month, $string) !== false) {
                return true;
            }
        }
    }

    public static function convertMonthToNumber($month)
    {
        $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'May' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Aug' => 8,
            'Sep' => 9,
            'Oct' => 10,
            'Nov' => 11,
            'Dec' => 12
        ];

        return $months[$month];
    }
}
