<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Psy\Util\Json;
use function GuzzleHttp\json_encode;
use App\Form;

class AddressController extends Controller
{
    public function handler(Request $request)
    {
        // $whichAddressField ;//= "Address";
        // $formId; //= 91826823855973;
        // echo '<pre>';
        // var_dump($request->input('temp'));
        // echo '</pre>';

        if ($request->input('temp') !== null) { //when post request comes from form.blade.php
            $form = new Form();
            $form->address = $request->input('address');
            $form->formId = $request->input('formId');
            $form->save();

            return view('map');
        } else if ($request->input('tempo') !== null) {//when post request comes from ajax in map.js
            $form = Form::latest()->first();
            $whichAddress = $form->address;
            $formId = $form->formId;
            // var_dump($whichAddress);
            // var_dump($formId);
            // $formId = 91826823855973;
            // $whichAddressField = "Address";

            $said = 5; //selected address' id

            // Create a client with a base URI
            //GET SUBMISSIONS
            $base_uri = 'https://api.jotform.com/form/' . $formId . '/submissions?apikey=cdcf7d48e2e6d0dfebae5f5393d51ef6';
            $client = new \GuzzleHttp\Client(['base_uri' => $base_uri]);
            // Send a request to https://foo.com/api/test
            $response = $client->request('GET', '')->getBody();
            $response = json_decode($response);
            $submission_count = $response->resultSet->count;
            for ($i = 0; $i < 1000; $i++) {
                if (isset($response->content[0]->answers->$i)) {
                    if ($response->content[0]->answers->$i->type == 'control_address' &&  $response->content[0]->answers->$i->text == $whichAddress) {
                        $said = $i;
                        break;
                    }
                }
            }
            $submissions = [];
            for ($i = 0; $i < $submission_count; $i++) {
                $id = $response->content[$i]->id;
                $firstname = $response->content[$i]->answers->{'3'}->answer->first;
                $lastname = $response->content[$i]->answers->{'3'}->answer->last;
                $name = $firstname . ' ' . $lastname;
                $addr_line1 = "";
                $addr_line2 = "";
                $city = "";
                $state = "";
                $country = "";
                $postal = "";
                // var_dump($said);
                
                // var_dump($response->content[$i]->answers);
                if (isset($response->content[$i]->answers->$said->answer->addr_line1)) {
                    $addr_line1 = $response->content[$i]->answers->$said->answer->addr_line1;
                }
                if (isset($response->content[$i]->answers->$said->answer->addr_line2)) {
                    $addr_line2 = $response->content[$i]->answers->$said->answer->addr_line2;
                }
                if (isset($response->content[$i]->answers->$said->answer->city)) {
                    $city = $response->content[$i]->answers->$said->answer->city;
                }
                if (isset($response->content[$i]->answers->$said->answer->state)) {
                    $state = $response->content[$i]->answers->$said->answer->state;
                }
                if (isset($response->content[$i]->answers->$said->answer->country)) {
                    $country = $response->content[$i]->answers->$said->answer->country;
                }
                if (isset($response->content[$i]->answers->$said->answer->postal)) {
                    $postal = $response->content[$i]->answers->$said->answer->postal;
                }

                $full_address = $addr_line1 . ' ' . $addr_line2 . ' ' . $city . ' ' . $state . ' ' . $country . ' ' . $postal;
                // var_dump($full_address);

                // $street = $addr_line1 . " " . $addr_line2;
                $url = 'http://www.mapquestapi.com/geocoding/v1/address?key=iqwDHWPWRFIBpvwGnkEppA5rz0gi3kmm&location=' . $full_address;
                // $url = 'http://www.mapquestapi.com/geocoding/v1/address?key=iqwDHWPWRFIBpvwGnkEppA5rz0gi3kmm&street='.$street.'&city='.$city.'&state='.$state.'&postalCode='.$postal.'';

                // Create a client with a base URI  //http://www.mapquestapi.com/geocoding/v1/address?key=KEY&location=Washington,DC
                $client = new \GuzzleHttp\Client(['base_uri' => $url]);
                // Send a request to https://foo.com/api/test
                $latLong = $client->request('GET', '')->getBody();
                $latLong = json_decode($latLong);
                $lat = $latLong->results[0]->locations[0]->latLng->lat;
                $long = $latLong->results[0]->locations[0]->latLng->lng;

                $submissions[$i] = array("id" => $id, "name" => $name, "full" => $full_address, "lat" => $lat, "long" => $long);
            }

            // var_dump($submissions); exit();
            return response()->json([
                'submissions' => $submissions,
            ], 200);
            // return view('map');
            // return view('map', compact('submissions'));
        }
        // }
        // var_dump(self::$whichAddressField);
        // var_dump(self::$formId);
        // exit();
    }
}
