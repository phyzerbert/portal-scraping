<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company;

class CompanyController extends Controller
{
    public function getCompanies(Request $request) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-g.weedmaps.com/discovery/v1/listings?page_size=150&page=1&filter%5Bany_retailer_services%5D%5B%5D=storefront',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: _pxhd=8704ef463b86d3167229f7530be0d779c85ce222dea13a6154ab6dd336788a76:4da7aee0-34d7-11eb-ae42-5faf642e9d49; ajs_anonymous_id=%22d0377bd5-5fd8-4627-ac57-d417a8c33602%22'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        dd($response);
        // dump(json_decode($response, true));
    }
}
