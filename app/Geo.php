<?php

namespace app;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\GoogleMaps;
use Ivory\HttpAdapter\CurlHttpAdapter;

/**
 * Class Geo
 */
class Geo implements \Pimple\ServiceProviderInterface
{
    /**
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * Registers this model in the app and gives it access to @app
     *
     * @param \Pimple\Container $app
     */
    public function register(\Pimple\Container $app)
    {
        $this->app = $app;
        $app['geo'] = $this;
    }

    /**
     * @var GoogleMaps
     */
    protected $geocoder;

    /**
     * Geo constructor.
     */
    public function __construct() {
        $curl           = new CurlHttpAdapter();
        $this->geocoder = new GoogleMaps($curl);
    }

    public static function distanceToRadians(float $distance_in_km) : float
    {
        return pow($distance_in_km * 0.539956803 / 60, 2);
    }

    /**
     * @param array $data
     * @return Coordinates
     */
    public function getCoords(array $data) : Coordinates
    {
        $addresses = $this->geocoder->geocode($data['address'] . ' ' . $data['zipcode'] . ' norway');
        $coords    = $addresses->first()->getCoordinates();
        return $coords;
    }

    /**
     * @param string $region
     * @return array with `loc_lang`, `loc_long` and `distance_in_km`
     */
    public function getTargetByRegion(string $region) : array
    {
        switch ($region) {
            case 'bergen':
                return ['loc_lat' => 60.389444, 'loc_long' => 5.33, 'distance_in_km' => 110.0];
            case 'oslo':
            default:
                return ['loc_lat' => 59.9139, 'loc_long' => 10.7522, 'distance_in_km' => 110.0];
        }
    }
}
