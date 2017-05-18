import React, { PropTypes } from 'react';
import Map, { GoogleApiWrapper } from 'google-maps-react'

import Person from './person';
import Marker from './marker';

export default class Hosts extends React.Component {
    renderFilters() {
        return <div className="filters">
            <span className="info">
                Show hosts with:
            </span>
            <label>
                <select onChange={e => this.props.setDistance(parseInt(e.target.value, 10))}>
                    <option value="20">20 km</option>
                    <option value="10">10 km</option>
                    <option value="5">5 km</option>
                    <option value="2">2 km</option>
                </select>
            </label>
            <label>
                <input type="checkbox" onChange={e => this.props.setFilter('men', e.target.checked)} /> Male
            </label>
            <label>
                <input type="checkbox" onChange={e => this.props.setFilter('women', e.target.checked)} /> Female
            </label>
            <label>
                <input type="checkbox" onChange={e => this.props.setFilter('children', e.target.checked)} /> Children
            </label>
            <label>
                <input type="checkbox" onChange={e => this.props.setFilter('childless', e.target.checked)} /> No Children
            </label>
        </div>
    }
    renderMap() {
        const { guest, hosts, filters, distance } = this.props;

        console.log(guest);

        const guestPosition = {};
        if (guest.loc_lat && guest.loc_long) {
            guestPosition.lat = parseFloat(guest.loc_lat, 10);
            guestPosition.lng = parseFloat(guest.loc_long, 10);
        } else {
            return null;
        }

        const query = Object.keys(filters).filter(key => filters[key]).map(key => key + '=yes').join('&');

        return <div className="map-view">
            <Map key={guest.id + '-' + query + '-' + distance} google={window.google}
                style={{width: '100%', height: '400px'}}
                className={'map'}
                zoom={12}
                initialCenter={guestPosition}>
                    {hosts.filter(host => {
                        return host.loc_lat && host.loc_long;
                    }).map((host, index) => {
                        const position = {
                            lat: parseFloat(host.loc_lat, 10),
                            lng: parseFloat(host.loc_long, 10)
                        };

                        return (
                            <Marker key={index} position={position} title={host.name} color='e41a1c' onClick={() => this.props.selectHost(host)}/>
                        );
                    }).concat(<Marker key={'guest'} position={guestPosition} color='377eb8'  icon={{ path: google.maps.SymbolPath.CIRCLE, scale: 10 }} />)}
            </Map>
        </div>
    }

    render() {
        const { guest, hosts, selectHost } = this.props;

        return <div>
            {this.renderFilters()}
            {this.renderMap()}
            <ul>
                {hosts.map(host => {
                    const selected = this.props.selectedHost && this.props.selectedHost.id === host.id;
                    return <Person key={host.id + '-' + (selected ? 1 : 0)}
                        person={host}
                        selected={selected}
                        onClick={selectHost}
                    />
                })}
            </ul>
        </div>
    }
}
