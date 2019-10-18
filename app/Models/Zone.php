<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';

    protected $fillable = [
        'id','postcode', 'area', 'region', 'city', 'state', 'longitude', 'latitude'
    ];

    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }


    // $client = new \GuzzleHttp\Client();
    // $endpoint = "https://api.pos.com.my/PostcodeWebApi/api/Postcode";

    // for ($postcode = 90000; $postcode < 100000; $postcode++) {
    //     $length = strlen((string)$postcode);
    //     $append = '';

    //     if (5 - $length === 4) {
    //         $append = '0000';
    //     } elseif (5 - $length === 3) {
    //         $append = '000';
    //     } elseif (5 - $length === 2) {
    //         $append = '00';
    //     } elseif (5 - $length === 1) {
    //         $append = '0';
    //     }

    //     $response = $client->request('GET', $endpoint, [
    //         'query' => [
    //             'Postcode' => $append . $postcode
    //         ],
    //         'headers' => [
    //             'Accept'     => 'application/json',
    //         ]
    //     ]);

    //     $content = json_decode($response->getBody(), true);
    //     $insert = [];

    //     if (count($content) > 0) {
    //         foreach ($content as $location) {
    //             $insert[] = [
    //                 'postcode' => $location['Postcode'],
    //                 'city' => $location['Post_Office'],
    //                 'state' => $location['State'],
    //                 'area' => $location['Location']
    //             ];
    //         }
    //         \App\Models\Zone::insert($insert);
    //     }
    // }

    // echo 'done';
}
