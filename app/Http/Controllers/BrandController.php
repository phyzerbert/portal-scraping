<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Brand;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

class BrandController extends Controller
{
    public function getBrands(Request $request) {
        dd('Brand Scraping');
        ini_set('max_execution_time', '0');
        $total_brands = 2422;
        $count = 0;
        $page = 1;
        while ($count <= $total_brands) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api-g.weedmaps.com/discovery/v1/brands?page_size=150&page=$page",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36',
                ),
            ));            
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response, true);
            if(isset($result['data']) && isset($result['data']['brands'])) {
                $brands = $result['data']['brands'];
                foreach ($brands as $item) {
                    $avatar_image = $item['avatar_image']['original_url'];
                    if($avatar_image == 'https://images.weedmaps.com/original/image_missing.jpg') {
                        $avatar_image = '';
                    }
                    Brand::create([
                        'name' => $item['name'],
                        'username' => $item['slug'],
                        'slug' => $item['slug'],
                        'avatar_image' => $avatar_image,
                        'web_url' => 'https://weedmaps.com/brands/'.$item['slug'],
                    ]);  
                    $count++;
                }
            }
            $page++;
        }
        
        dd('Successfully Done');
    }
    public function downloadImages(Request $request) {
        ini_set('max_execution_time', '0');
        // $brands = Brand::all();
        $brands = Brand::where('id', '>=', 4567)->get();
        $item = Brand::whereNull('image')->first();
        while ($item != null) {
            if($item->avatar_image != '') {           
                $url = $item->avatar_image;
                $info = pathinfo($url);
                try {
                    $contents = file_get_contents($url);
                    $extension = isset($info['extension']) ? $info['extension'] : 'jpg';
                    $new_file_name = $item->username."_marijuana_".time();
                    $file = public_path('brands/'.$new_file_name.".".$extension);
                    file_put_contents($file, $contents);  
                    $item->update(['image' => $new_file_name.".".$extension]);
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {
                $item->update(['image' => '']);
            }
            $item = Brand::whereNull('image')->first();
        }
    }

    public function checkDuplicates(Request $request) {
        $brands = Brand::all();
        foreach ($brands as $item) {
            $info = pathinfo($item->avatar_image);
            $filename = $info['basename'];
            $count = Brand::where('avatar_image', 'like',"%$filename%")->count();
            if($count > 1) {
                dump("Duplicates $count    ".$filename);
            }
        }
        dump('Done');
    }

    public function getAttributes(Request $request) {
        ini_set('max_execution_time', '0');

        $proxy_array = array(
            ['proxy' => 'de9.proxidize.com:53615', 'userpwd' => 'MyvRkI5:NguoDPl', 'change_ip' => 'https://trigger.macrodroid.com/1065a9d0-7111-4f77-949a-4d8713f4a89e/ip'],
            ['proxy' => 'de9.proxidize.com:38596', 'userpwd' => 'n1TPe9t:TlAn2Os', 'change_ip' => 'https://trigger.macrodroid.com/f1a555bd-0d85-423e-93e6-788f992a4c39/ip'],
            ['proxy' => 'de9.proxidize.com:40131', 'userpwd' => 'lySNUDU:NTrJJCv', 'change_ip' => 'https://trigger.macrodroid.com/9b00f894-38a8-465d-b740-c70ac0ef8c81/ip'],
            ['proxy' => 'de9.proxidize.com:42456', 'userpwd' => 'bjNEhbs:K09e1ko', 'change_ip' => 'https://trigger.macrodroid.com/af12fa69-d4ca-44ed-ad66-5cbea0bd32f2/ip'],
        );

        $user_agents = array(
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:86.0) Gecko/20100101 Firefox/86.0",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Edg/88.0.705.81",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36 OPR/74.0.3911.187",
        );
        $random_agent = $user_agents[array_rand($user_agents)];
        $random_proxy = $proxy_array[array_rand($proxy_array)];
        $item = Brand::whereNull('website_url')->first();
        while ($item != null) {
            $item = Brand::whereNull('website_url')->first();
            // dump($item->name); continue;
            if(fmod($item->id, 10) == 0) {
                $random_agent = $user_agents[array_rand($user_agents)];
                $random_proxy = $proxy_array[array_rand($proxy_array)];
            }
            $url = $item->web_url;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_PROXY => $random_proxy['proxy'],
                CURLOPT_PROXYUSERPWD => $random_proxy['userpwd'],
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "User-Agent: ".$random_agent,
                ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);
            $dom = new Dom;
            $dom->setOptions(
                (new Options())->setRemoveScripts(false)
            );
            $dom->loadStr($response);
            $script_tag = $dom->getElementById('__NEXT_DATA__');
            // dump($response); continue;
            if($script_tag == null) {
                $this->changeIpAddress($random_proxy['change_ip']);
                dump('Blocked proxy '.$random_proxy['proxy']);
                $random_proxy = $proxy_array[array_rand($proxy_array)];
                continue;
            };
            if($script_tag && $script_tag->text) {
                $response_data = json_decode($script_tag->text, true);
                if(isset($response_data['props']) && isset($response_data['props']['storeInitialState'])) {
                    if(!isset($response_data['props']['storeInitialState']['brands']['brand'])) {
                        $item->delete();
                        continue;
                    }
                    $brand_data = $response_data['props']['storeInitialState']['brands']['brand'];
                } else {
                    $brand_data = null;
                }
                if($brand_data) {
                    
                    // Process Socials
                    $social_profiles = $brand_data['socialNetworkProfiles'];
                    $facebook_url = $this->getSocialProfile($social_profiles, 'Facebook') ? 'facebook.com/'.$this->getSocialProfile($social_profiles, 'Facebook') : '';
                    $instagram_url = $this->getSocialProfile($social_profiles, 'Instagram') ? 'instagram.com/'.$this->getSocialProfile($social_profiles, 'Instagram') : '';
                    $twitter_url = $this->getSocialProfile($social_profiles, 'Twitter') ? 'twitter.com/'.$this->getSocialProfile($social_profiles, 'Twitter') : '';
                    $youtube_url = $this->getSocialProfile($social_profiles, 'Youtube') ? 'youtube.com/'.$this->getSocialProfile($social_profiles, 'Youtube') : '' ?? '';
                    $website_url = $this->getSocialProfile($social_profiles, 'Custom') ? $this->getSocialProfile($social_profiles, 'Custom') : '' ?? '';
                    
                    $item->website_url = $website_url;
                    $item->facebook_url = $facebook_url;
                    $item->twitter_url = $twitter_url;
                    $item->instagram_url = $instagram_url;
                    $item->youtube_url = $youtube_url;

                    $item->save();
                }
            }
            sleep(5);
        }
        
    }

    public function getSocialProfile($social_profiles, $key) {
        foreach ($social_profiles as $item) {
            if($item['networkName'] == $key && !$item['externalId']) {
                return $item['externalId'];
            }
        }
        return false;
    }
}
