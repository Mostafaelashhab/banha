<?php

namespace App\Support;

class BusinessCovers
{
    /**
     * Curated Unsplash photo IDs per business category.
     * URLs are constructed as https://images.unsplash.com/photo-{id}?w=800&h=500&fit=crop&q=80
     */
    private const COVERS = [
        'food' => [
            // Restaurants / dining
            '1517248135467-4c7edcad34c4', // food spread
            '1414235077428-338989a2e8c0', // restaurant interior
            '1565299624946-b28f40a0ae38', // plated food
            '1546069901-ba9599a7e63c',    // bowl
            '1568901346375-23c9450c58cd', // burger
            '1513104890138-7c749659a591', // pizza
            '1565895405138-6c3a1555da6a', // shawarma
            '1559339352-11d035aa65de',    // arabic food
        ],
        'medical' => [
            '1576091160550-2173dba999ef', // pharmacy shelves
            '1631549916768-4119b2e5f926', // medicine
            '1559757148-5c350d0d3c56',    // pills
            '1551601651-bc60f254d532',    // doctor
            '1612349317150-e413f6a5b16d', // clinic
            '1666214280557-f1b5022eb634', // stethoscope
        ],
        'shops' => [
            '1604719312566-8912e9227c6a', // grocery
            '1542838132-92c53300491e',    // supermarket aisle
            '1556909114-f6e7ad7d3136',    // shop
            '1601598851547-4302969d0614', // produce
            '1573246123716-6b1782bfc499', // butcher
        ],
        'craftsmen' => [
            '1572774717547-1ab46f66c27d', // tools
            '1581244277943-fe4a9c777189', // workshop tools
            '1567427017947-545c5f8d16ad', // workshop
            '1504148455328-c376907d081c', // electrical work
            '1621905251189-08b45d6a269e', // construction
            '1530124566582-a618bc2615dc', // wood working
        ],
        'services' => [
            '1521590832167-7bcbfaa6381f', // service shop
            '1493963230532-bd5edd7f1c1a', // barbershop
            '1562322140-8baeececf3df',    // laundry
            '1607002432516-8e51e5e7048a', // tailor
            '1503951914875-452162b0f3f1', // photo studio
        ],
    ];

    public static function pick(string $category, int $seed = 0): string
    {
        $list  = self::COVERS[$category] ?? self::COVERS['food'];
        $index = abs($seed) % count($list);
        $id    = $list[$index];
        return "https://images.unsplash.com/photo-{$id}?w=800&h=500&fit=crop&q=80&auto=format";
    }
}
