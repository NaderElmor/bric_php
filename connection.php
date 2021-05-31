// This is a class responsible of interacting with an API, what recomendations do you have for this? No need to code them just explaining what can be improved is fine

/*
 I think we have to make a general trait has several methods  to return responses to make this code less noisy
*/
<?php

namespace App\Modules\CarSharing\Helpers\Hardware\Adapters;


use App\Modules\CarSharing\Helpers\Hardware\HardwareContract;
use App\Modules\Fleets\Models\Vehicle;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Collection;

class InversAdapter implements HardwareContract
{
    const GATEWAY_URL = "https://api.cloudboxx.invers.com";
    const BASE_URI = "/api";
    protected $http_client;
    private $username;
    private $password;
    protected $gateway_url;
    protected $base_uri;

    public function __construct()
    {
        $this->username = p('car-sharing','invers_username', '');
        $this->password = p('car-sharing','invers_password', '');
        $this->gateway_url = static::GATEWAY_URL;
        $this->base_uri = static::BASE_URI;
        $this->http_client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->gateway_url,
            'auth' => [
                $this->username,
                $this->password
            ]
        ]);
    }

    /**
     * @return string[]
     */
    public static function getAuthenticationFields(): array
    {
        return [
            'invers_username',
            'invers_password',
        ];
    }

    /**
     * @return string[]
     */
    public function getListFields(): array
    {
        return [
            'id' => 'qnr',
            'label' => 'qnr',
        ];
    }

    /**
     * @return Collection
     */
    public function listDevices()
    {
        try {
            $response = guzzleJson($this->http_client->get($this->base_uri.'/devices', [
                'query' => ['limit' => 100],
            ]));

            return $response['data'];

        } catch (ServerException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Server Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        } catch (ClientException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Client Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        }
    }

    /**
     * @param \App\Modules\Fleets\Models\Vehicle $vehicle
     * @return boolean
     */
    public function openCar(Vehicle $vehicle)
    {
        try {
            $response = guzzleJson($this->http_client->put($this->base_uri.'/devices/'.$vehicle->hardware_id.'/central-lock', [
                'json' => $this->changeState("unlocked")
            ]));


            if($response['state'] == 'unlocked') {
                return true;
            }
            return false;

        } catch (ServerException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Server Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        } catch (ClientException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Client Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        }
    }

    /**
     * @param \App\Modules\Fleets\Models\Vehicle $vehicle
     * @return boolean
     */
    public function closeCar(Vehicle $vehicle)
    {
        try {
            $response = guzzleJson($this->http_client->put($this->base_uri.'/devices/'.$vehicle->hardware_id.'/central-lock', [
                'json' => $this->changeState("locked")
            ]));

            if($response['state'] == 'locked') {
                return true;
            }
            return false;

        } catch (ServerException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Server Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        } catch (ClientException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Client Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        }
    }

    /**
     * @param \App\Modules\Fleets\Models\Vehicle $vehicle
     * @return Vehicle
     */
    public function getCarDetails(Vehicle $vehicle)
    {

        try {
            $response = guzzleJson($this->http_client->get($this->base_uri.'/devices/'.$vehicle->hardware_id.'/status', []));

            return $response;

        } catch (ServerException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Server Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        } catch (ClientException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Client Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        }

    }

    /**
     * @param \App\Modules\Fleets\Models\Vehicle $vehicle
     * @return boolean
     */
    public function allowEngineStart(Vehicle $vehicle)
    {
        try {
            $response = guzzleJson($this->http_client->put($this->base_uri.'/devices/'.$vehicle->hardware_id.'/immobilizer', [
                'json' => $this->changeState("unlocked")
            ]));

            if($response['state'] == 'unlocked') {
                return true;
            }
            return false;

        } catch (ServerException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Server Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        } catch (ClientException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Client Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        }
    }

    /**
     * @param \App\Modules\Fleets\Models\Vehicle $vehicle
     * @return boolean
     */
    public function stopEngine(Vehicle $vehicle)
    {
        try {
            $response = guzzleJson($this->http_client->put($this->base_uri.'/devices/'.$vehicle->hardware_id.'/immobilizer', [
                'json' => $this->changeState("locked")
            ]));

            if($response['state'] == 'locked') {
                return true;
            }
            return false;

        } catch (ServerException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Server Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        } catch (ClientException $e) {
            $eMessage = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            throw new \Exception("Client Error [" .
                                 $e->getResponse()->getStatusCode() . '] '. $eMessage->error);
        }
    }

    private function changeState($state)
    {
        return [
            'state' => $state
        ];
    }
}
