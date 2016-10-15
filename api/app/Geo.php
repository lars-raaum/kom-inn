<?php

namespace app;

class Geo {

    protected $geocoder;

    public function __construct() {
        $curl           = new \Ivory\HttpAdapter\CurlHttpAdapter();
        $this->geocoder = new \Geocoder\Provider\GoogleMaps($curl);
    }

    public function getCoords(array $data) {
        $addresses = $this->geocoder->geocode($data['address'] . ' oslo ' . $data['zipcode'] . ' norway');
        $coords    = $addresses->first()->getCoordinates();
        return $coords;
    }

}
