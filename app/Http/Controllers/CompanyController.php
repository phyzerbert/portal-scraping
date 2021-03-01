<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company;

class CompanyController extends Controller
{
    public function getCompanies(Request $request) {
        ini_set('max_execution_time', '0');
        $total_companies = 7836;
        $count = 0;
        $page = 1;
        while ($count <= $total_companies) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api-g.weedmaps.com/discovery/v1/listings?page_size=150&page=$page&filter%5Bany_retailer_services%5D%5B%5D=storefront",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36',
                    'Cookie: _pxhd=8704ef463b86d3167229f7530be0d779c85ce222dea13a6154ab6dd336788a76:4da7aee0-34d7-11eb-ae42-5faf642e9d49; ajs_anonymous_id=%22d0377bd5-5fd8-4627-ac57-d417a8c33602%22'
                ),
            ));            
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response, true);
            if(isset($result['data'])) {
                $companies = $result['data']['listings'];
                foreach ($companies as $item) {
                    if($item['package_level'] != 'free') {
                        $avatar_image = $item['avatar_image']['original_url'];
                        if($avatar_image == 'https://images.weedmaps.com/original/image_missing.jpg') {
                            $avatar_image = '';
                        }
                        if($item['type'] == 'dispensary') $store_type = 1;
                        if($item['type'] == 'delivery') $store_type = 2;
                        Company::create([
                            'name' => $item['name'],
                            'username' => $item['slug'],
                            'slug' => $item['slug'],
                            'email' => $item['email'],
                            'phone_number' => $item['phone_number'],
                            'store_type' => $store_type,
                            'package_level' => $item['package_level'],
                            'city' => $item['city'],
                            'state' => $item['state'],
                            'postal' => $item['zip_code'],
                            'address' => $item['address'],
                            'latitude' => $item['latitude'],
                            'longitude' => $item['longitude'],
                            'timezone' => $item['longitude'],
                            'avatar_image' => $avatar_image,
                            'web_url' => $item['web_url'],
                            'recreational' => $item['license_type'] == 'hybrid' || $item['license_type'] == 'recreational' ? 1 : 0,
                            'medical' => $item['license_type'] == 'hybrid' || $item['license_type'] == 'medical' ? 1 : 0,
                            'description' => strip_tags($item['intro_body']),
                        ]);  
                    }
                    $count++;
                }
            }
            $page++;
        }
        
        dd('Successfully Done');
    }
    public function downloadImages(Request $request) {
        ini_set('max_execution_time', '0');
        $companies = Company::all();
        foreach ($companies as $item) {
            if($item->avatar_image != '') {           
                $url = $item->avatar_image;
                $info = pathinfo($url);
                $contents = file_get_contents($url);
                $extension = isset($info['extension']) ? $info['extension'] : 'jpg';
                $new_file_name = $item->username."_marijuana_".time();
                $file = public_path('avatar/'.$new_file_name.".".$extension);
                file_put_contents($file, $contents);  
                $item->update(['image' => $new_file_name.".".$extension]);
            }
        }
    }

    public function checkDuplicates(Request $request) {
        $companies = Company::all();
        foreach ($companies as $item) {
            $info = pathinfo($item->avatar_image);
            $filename = $info['basename'];
            $count = Company::where('avatar_image', 'like',"%$filename%")->count();
            if($count > 1) {
                dump("Duplicates $count    ".$filename);
            }
        }
        dump('Done');
    }
}
