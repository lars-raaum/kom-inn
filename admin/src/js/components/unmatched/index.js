import React from 'react';
import { Link } from 'react-router';
import cs from 'classnames';
import Map, { GoogleApiWrapper, Marker } from 'google-maps-react'

class Person extends React.Component {
    getAdults() {
        const { person } = this.props;

        return parseInt(person.adults_f, 10) + parseInt(person.adults_m, 10);
    }

    render() {
        const { person, selected } = this.props;

        const className = cs({ selected });

        return <li className={className} onClick={() => this.props.onClick(person)}>
            <h2>{this.getAdults()} adults. {person.children} children.</h2>
            <div className={'info'}>
                <span className="name">{person.name}</span>
                <span className="info">{person.age} år.</span>
                <span className="origin">{person.origin}.</span>
            </div>
            <span className="freetext">{person.freetext}</span>
        </li>
    }
}

class Hosts extends React.Component {
    renderFilters() {
        return <div className="filters">
            <span className="info">
                Show hosts with:
            </span>
            <label><input type="checkbox" onChange={e => this.props.setFilter('male', e.target.checked)} /> Male</label>
            <label><input type="checkbox" onChange={e => this.props.setFilter('female', e.target.checked)} /> Female</label>
            <label><input type="checkbox" onChange={e => this.props.setFilter('children', e.target.checked)} /> Children</label>
        </div>
    }
    renderMap() {
        const { guest, hosts, filters } = this.props;

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
            <Map key={guest.id + '-' + query} google={window.google}
                style={{width: '100%', height: '400px'}}
                className={'map'}
                zoom={14}
                initialCenter={guestPosition}>
                    {hosts.filter(host => {
                        return host.loc_lat && host.loc_long;
                    }).map((host, index) => {
                        const position = {
                            lat: parseFloat(host.loc_lat, 10),
                            lng: parseFloat(host.loc_long, 10)
                        };

                        return (
                            <Marker key={index} position={position} title={host.name} onClick={() => this.props.selectHost(host)}/>
                        );
                    }).concat(<Marker key={'guest'} position={guestPosition} icon={{ path: google.maps.SymbolPath.CIRCLE, scale: 10 }} />)}
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

export default class Unmatched extends React.Component {
    constructor() {
        super();

        this.state = {
            guests: [],
            hosts: [],
            selectedGuest: null,
            selectedHost: null,
            filters: {
                men: false,
                women: false,
                children: false
            }
        };

        this.selectGuest = this.selectGuest.bind(this);
        this.selectHost = this.selectHost.bind(this);
        this.setFilter = this.setFilter.bind(this);
        this.matchUsers = this.matchUsers.bind(this);
    }

    componentDidMount() {
        fetch('/api/guests', {
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(res => res.json()).then(guests => {
            this.setState({ guests })
        });
    }

    setFilter(filter, val) {
        const filters = Object.assign(this.state.filters, {
            [filter]: val
        });

        this.setState({ filters })
        this.fetchHosts();
    }

    matchUsers() {

    }

    selectGuest(guest) {
        this.setState({ selectedGuest: guest, selectedHost: null });
        this.fetchHosts();
    }

    fetchHosts() {
        const { filters } = this.state;
        const query = Object.keys(filters).filter(key => filters[key]).map(key => key + '=yes').join('&');

        fetch(`/api/hosts?${query}`, {
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(res => res.json()).then(hosts => {
            this.setState({ hosts })
        });
    }

    selectHost(host) {
        this.setState({ selectedHost: host });
    }

    renderHosts() {
        if (!this.state.selectedGuest) {
            return null;
        }

        return <Hosts guest={this.state.selectedGuest}
            hosts={this.state.hosts}
            filters={this.state.filters}
            setFilter={this.setFilter}
            selectHost={this.selectHost}
            selectedHost={this.state.selectedHost}
            />;
    }

    render() {
        return (
            <div className="unmatched">
                <div className="row">
                    <div className="col-sm-12">
                        <button className="match" onClick={this.matchUsers()}>
                            Match guest with host
                        </button>
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-6">
                        <h1>Unmatched guests</h1>
                        <ul>
                            {this.state.guests.map(guest => {
                                const selected = this.state.selectedGuest && this.state.selectedGuest.id === guest.id;
                                return <Person key={guest.id + '-' + (selected ? 1 : 0)}
                                    person={guest}
                                    selected={selected}
                                    onClick={this.selectGuest} />
                            })}
                        </ul>
                    </div>
                    <div className="col-sm-6">
                        <h1>Available hosts</h1>
                        {this.renderHosts()}
                    </div>
                </div>
            </div>
        )
    }
}