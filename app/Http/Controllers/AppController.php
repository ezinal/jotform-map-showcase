<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Psy\Util\Json;
use function GuzzleHttp\json_encode;

class AppController extends Controller
{
    public function index()
    {
        return view('selectForm');
    }

    public function handler(Request $request)
    {
        $formId = $request->input('formId');
        if($formId == null){
            return redirect()->action('AppController@index');
        }
        // $formId = 91826823855973; // :3 address
        // var_dump($formId); exit();
        $base_uri0 = 'https://api.jotform.com/form/' . $formId . '/questions?apikey=17fd20c3752ed60a494845250b';
        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri0]);
        // Send a request to https://foo.com/api/test
        $response = $client->request('GET', '')->getBody();
        $response = json_decode($response);
        $address = [];
        for ($i = 0; $i < 1000; $i++) { //assuming max number of 1000 questions
            if (isset($response->content->$i)) { //if there is such a questionId
                if ($response->content->$i->type == "control_address") {
                    array_push($address,$response->content->$i->text);
                }
            }
        }

        return view('form', compact('address', 'formId'));
    }
}
