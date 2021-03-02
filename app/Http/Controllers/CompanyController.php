<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

class CompanyController extends Controller
{
    public function getCompanies(Request $request) {
        dd('Portal Scraping');
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
                            'timezone' => $item['timezone'],
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
        // $companies = Company::all();
        $companies = Company::where('id', '>=', 4567)->get();
        foreach ($companies as $item) {
            if($item->avatar_image != '') {           
                $url = $item->avatar_image;
                $info = pathinfo($url);
                try {
                    $contents = file_get_contents($url);
                    $extension = isset($info['extension']) ? $info['extension'] : 'jpg';
                    $new_file_name = $item->username."_marijuana_".time();
                    $file = public_path('avatar/'.$new_file_name.".".$extension);
                    file_put_contents($file, $contents);  
                    $item->update(['image' => $new_file_name.".".$extension]); //code...
                } catch (\Throwable $th) {
                    //throw $th;
                }
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

    public function getAttributes(Request $request) {
        ini_set('max_execution_time', '0');

        $user_agents = array(
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:86.0) Gecko/20100101 Firefox/86.0",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Edg/88.0.705.81",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36 OPR/74.0.3911.187",
        );
        $random_agent = $user_agents[array_rand($user_agents)];
        $item = Company::whereNull('atm')->whereNull('security')->first();
        while ($item != null) {
            $item = Company::whereNull('atm')->whereNull('security')->first();
            // dump($item->name); continue;
            if(fmod($item->id, 10) == 0) {
                // $this->changeIpAddress();
                $random_agent = $user_agents[array_rand($user_agents)];
            }
            $url = $item->web_url;
            $curl = curl_init();
            $proxy = 'de9.proxidize.com:40131';
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_PROXY => $proxy,
                CURLOPT_PROXYUSERPWD => "lySNUDU:NTrJJCv",
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "User-Agent: ".$random_agent,
                    // 'Cookie: ajs_anonymous_id=%22d0377bd5-5fd8-4627-ac57-d417a8c33602%22; _pxhd=ca83b03c28a08347564a5e24a841db206c0c63d6c10c80ae2040e5064e9e2762:031d1b90-34e0-11eb-bd4f-19dade27ea77'
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
                dump('Blocked Requestes');
                $this->changeIpAddress();
                continue;
            };
            if($script_tag && $script_tag->text) {
                $response_data = json_decode($script_tag->text, true);
                if(isset($response_data['props']) && isset($response_data['props']['storeInitialState'])) {
                    if(!isset($response_data['props']['storeInitialState']['listing']['listing'])) {
                        $item->delete();
                        continue;
                    }
                    $company_data = $response_data['props']['storeInitialState']['listing']['listing'];
                } else {
                    $company_data = null;
                }
                if($company_data) {
                    $item->state_license = $company_data['license_number'];
                    $item->timezone = $company_data['timezone'];
                    
                    // Process Socials
                    $facebook_url = $company_data['social']['facebook_id'];
                    $instagram_url = $company_data['social']['instagram_id'];
                    $twitter_url = $company_data['social']['twitter_id'];
                    $youtube_url = $company_data['social']['youtube_ids'][0] ?? '';
                    if($company_data['social']['facebook_id'] != '') {
                        if(strpos($company_data['social']['facebook_id'], 'facebook') === false) {
                            $facebook_url = 'facebook.com/'.$company_data['social']['facebook_id'];
                        } else if(strpos($company_data['social']['facebook_id'], 'http') !== false) {
                            $facebook_url = str_replace('https://', '', $company_data['social']['facebook_id']);
                        }
                    }
                    if($company_data['social']['twitter_id'] != '') {
                        if(strpos($company_data['social']['twitter_id'], 'twitter') === false) {
                            $twitter_url = 'twitter.com/'.$company_data['social']['twitter_id'];
                        } else if(strpos($company_data['social']['twitter_id'], 'http') !== false) {
                            $twitter_url = str_replace('https://', '', $company_data['social']['twitter_id']);
                        }
                    }
                    if($company_data['social']['instagram_id'] != '') {
                        if(strpos($company_data['social']['instagram_id'], 'instagram') === false) {
                            $instagram_url = 'instagram.com/'.$company_data['social']['instagram_id'];
                        } else if(strpos($company_data['social']['instagram_id'], 'http') !== false) {
                            $instagram_url = str_replace('https://', '', $company_data['social']['instagram_id']);
                        }
                    }
                    if(count($company_data['social']['youtube_ids'])) {
                        if(strpos($company_data['social']['youtube_ids'][0], 'youtube') === false) {
                            $youtube_url = 'youtube.com/'.$company_data['social']['youtube_ids'][0];
                        } else if(strpos($company_data['social']['youtube_ids'][0], 'http') !== false) {
                            $youtube_url = str_replace('https://', '', $company_data['social']['youtube_ids'][0]);
                        }
                    }
                    $item->website_url = $company_data['website'];
                    $item->facebook_url = $facebook_url;
                    $item->twitter_url = $twitter_url;
                    $item->instagram_url = $instagram_url;
                    
                    $item->youtube_url = $youtube_url;
                    // ATM Security
                    $item->atm = $company_data['has_atm'] ? 1 : 0;
                    $item->security = $company_data['has_security_guard'] ? 1 : 0;
                    // Business Times
                    $item->mon_open = $this->processBusnessHours($company_data['business_hours']['monday'], 'open');
                    $item->mon_close = $this->processBusnessHours($company_data['business_hours']['monday'], 'close');
                    $item->tue_open = $this->processBusnessHours($company_data['business_hours']['tuesday'], 'open');
                    $item->tue_close = $this->processBusnessHours($company_data['business_hours']['tuesday'], 'close');
                    $item->wed_open = $this->processBusnessHours($company_data['business_hours']['wednesday'], 'open');
                    $item->wed_close = $this->processBusnessHours($company_data['business_hours']['wednesday'], 'close');
                    $item->thu_open = $this->processBusnessHours($company_data['business_hours']['thursday'], 'open');
                    $item->thu_close = $this->processBusnessHours($company_data['business_hours']['thursday'], 'close');
                    $item->fri_open = $this->processBusnessHours($company_data['business_hours']['friday'], 'open');
                    $item->fri_close = $this->processBusnessHours($company_data['business_hours']['friday'], 'close');
                    $item->sat_open = $this->processBusnessHours($company_data['business_hours']['saturday'], 'open');
                    $item->sat_close = $this->processBusnessHours($company_data['business_hours']['saturday'], 'close');
                    $item->sun_open = $this->processBusnessHours($company_data['business_hours']['sunday'], 'open');
                    $item->sun_close = $this->processBusnessHours($company_data['business_hours']['sunday'], 'close');

                    $item->mon_closed = $this->checkBusinessClosed($company_data['business_hours'], 'monday');
                    $item->tue_closed = $this->checkBusinessClosed($company_data['business_hours'], 'tuesday');
                    $item->wed_closed = $this->checkBusinessClosed($company_data['business_hours'], 'wednesday');
                    $item->thu_closed = $this->checkBusinessClosed($company_data['business_hours'], 'thursday');
                    $item->fri_closed = $this->checkBusinessClosed($company_data['business_hours'], 'friday');
                    $item->sat_closed = $this->checkBusinessClosed($company_data['business_hours'], 'saturday');
                    $item->sun_closed = $this->checkBusinessClosed($company_data['business_hours'], 'sunday');

                    $item->save();
                }
            }
            sleep(5);
        }
        
    }

    public function checkBusinessClosed($business_hours, $week_day) {
        $result = null;
        if(isset($business_hours[$week_day]['is_allday'])) {
            $result = $business_hours[$week_day]['is_allday'] == true ? 1 : 2;
        }
        return $result;
    }

    public function processBusnessHours($week_day, $type) {
        if(isset($week_day[$type])) {
            $hour = $week_day[$type];
            $hour = str_replace('am', ' AM', $hour);
            $hour = str_replace('pm', ' PM', $hour);  
        } else {
            $hour = null;
        }
        return $hour;
    }

    public function changeIpAddress() {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://trigger.macrodroid.com/9b00f894-38a8-465d-b740-c70ac0ef8c81/ip',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        if($response == 'ok') {
            dump('Proxy Ip address was changed.');
        }
        sleep(60);
    }
}
