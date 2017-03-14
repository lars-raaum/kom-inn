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
}
