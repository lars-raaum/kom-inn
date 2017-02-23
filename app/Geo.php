<?php

namespace app;

/**
 * Class Geo
 */
class Geo {

    /**
     * @var \Geocoder\Provider\GoogleMaps
     */
    protected $geocoder;

    /**
     * Geo constructor.
     */
    public function __construct() {
        $curl           = new \Ivory\HttpAdapter\CurlHttpAdapter();
        $this->geocoder = new \Geocoder\Provider\GoogleMaps($curl);
    }

    /**
     * @param array $data
     * @return \Geocoder\Model\Coordinates
     */
    public function getCoords(array $data) : \Geocoder\Model\Coordinates
    {
        $addresses = $this->geocoder->geocode($data['address'] . ' ' . $data['zipcode'] . ' norway');
        $coords    = $addresses->first()->getCoordinates();
        return $coords;
    }
}
