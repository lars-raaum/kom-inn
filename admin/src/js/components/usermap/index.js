import React, {PropTypes, Component} from 'react';

import Map, { GoogleApiWrapper } from 'google-maps-react'

import camelize from 'camelize';
const evtNames = ['click', 'mouseover', 'recenter'];

const wrappedPromise = function() {
    var wrappedPromise = {},
        promise = new Promise(function (resolve, reject) {
            wrappedPromise.resolve = resolve;
            wrappedPromise.reject = reject;
        });
    wrappedPromise.then = promise.then.bind(promise);
    wrappedPromise.catch = promise.catch.bind(promise);
    wrappedPromise.promise = promise;

    return wrappedPromise;
}

export class Marker extends React.Component {

    componentDidMount() {
        this.markerPromise = wrappedPromise();
        this.renderMarker();
    }

    componentDidUpdate(prevProps) {
        if ((this.props.map !== prevProps.map) ||
            (this.props.position !== prevProps.position)) {
            this.renderMarker();
        }
    }

    componentWillUnmount() {
        if (this.marker) {
            this.marker.setMap(null);
        }
    }

    renderMarker() {
        let {
            map, google, position, mapCenter, color
        } = this.props;
        if (!google) {
            return null
        }

        let pos = position || mapCenter;
        if (!(pos instanceof google.maps.LatLng)) {
            position = new google.maps.LatLng(pos.lat, pos.lng);
        }

        var pinColor = color;
        var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
            new google.maps.Size(21, 34),
            new google.maps.Point(0,0),
            new google.maps.Point(10, 34));
        var pinShadow = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_shadow",
            new google.maps.Size(40, 37),
            new google.maps.Point(0, 0),
            new google.maps.Point(12, 35));

        const pref = {
            map: map,
            position: position,
            icon: pinImage,
            shadow: pinShadow
        };
        this.marker = new google.maps.Marker(pref);

        evtNames.forEach(e => {
            this.marker.addListener(e, this.handleEvent(e));
    });

        this.markerPromise.resolve(this.marker);
    }

    getMarker() {
        return this.markerPromise;
    }

    handleEvent(evt) {
        return (e) => {
            const evtName = `on${camelize(evt)}`
            if (this.props[evtName]) {
                this.props[evtName](this.props, this.marker, e);
            }
        }
    }

    render() {
        return null;
    }
}

Marker.propTypes = {
    position: PropTypes.object,
    map: PropTypes.object,
    color: PropTypes.string,
}

evtNames.forEach(e => Marker.propTypes[e] = PropTypes.func)

Marker.defaultProps = {
    name: 'Marker'
}

export default class UserMap extends Component {
    constructor() {
        super();

        this.state = {
            guests: [],
            hosts: []
        };
    }

    componentDidMount() {
        this.fetchGuests();
        this.fetchHosts();
    }

    fetchGuests() {
        return fetch('/api/guests', {
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(res => res.json()).then(guests => {
            this.setState({ guests })
        });
    }

    fetchHosts() {
        return fetch('/api/hosts', {
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(res => res.json()).then(hosts => {
            this.setState({ hosts })
        });
    }

    render() {
        var guestPosition = {};
        guestPosition.lat = 59.9035576;
        guestPosition.lng = 10.7354951;
        return (
            <div>
                <Map key="waawawlsdfa" google={window.google}
                    style={{width: '100%', height: '800px'}}
                    className={'map'}
                    zoom={12}
                    initialCenter={guestPosition}>
                        {this.state.hosts.filter(host => {
                            return host.loc_lat && host.loc_long;
                            }).map((host, index) => {
                                    const position = {
                                        lat: parseFloat(host.loc_lat, 10),
                                        lng: parseFloat(host.loc_long, 10)
                                    };

                                return (
                                    <Marker key={index} position={position} title={host.name} color='e41a1c'/>
                            );
                        })}
                        {this.state.guests.filter(guest => {
                            return guest.loc_lat && guest.loc_long;
                        }).map((guest, index) => {
                            const position = {
                                lat: parseFloat(guest.loc_lat, 10),
                                lng: parseFloat(guest.loc_long, 10)
                            };

                            return (
                                <Marker key={index} position={position} title={guest.name} opacity='50' color='377eb8' />
                        );
                        })}
                </Map>
            </div>
        );
    }
}
