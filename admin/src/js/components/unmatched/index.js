import React, { PropTypes } from 'react';
import cs from 'classnames';

import Person from './person';
import Hosts from './hosts';

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
            },
            distance: 20
        };

        this.selectGuest = this.selectGuest.bind(this);
        this.selectHost = this.selectHost.bind(this);
        this.setFilter = this.setFilter.bind(this);
        this.setDistance = this.setDistance.bind(this);
        this.matchUsers = this.matchUsers.bind(this);
    }

    componentDidMount() {
        this.fetchGuests();
    }

    setFilter(filter, val) {
        const filters = Object.assign(this.state.filters, {
            [filter]: val
        });

        this.setState({ filters })
        this.fetchHosts();
    }

    setDistance(distance) {
        this.setState({ distance }, () => this.fetchHosts())
    }

    matchUsers() {
        fetch('/api/match', {
            credentials: 'include',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                guest_id: this.state.selectedGuest.id,
                host_id: this.state.selectedHost.id,
                comment: ''
            })
        }).then(res => res.json()).then(guests => {
            this.setState({
                selectedGuest: null,
                selectedHost: null
            });

            return Promise.all([
                this.fetchGuests()
            ])
        });
    }

    selectGuest(guest) {
        this.setState({ selectedGuest: guest, selectedHost: null });
        this.fetchHosts(guest);
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

    fetchHosts(guest) {
        const { filters, selectedGuest, distance } = this.state;
        const query = Object.keys(filters).filter(key => filters[key]).map(key => key + '=yes');
        var filterGuest = undefined;

        if (selectedGuest == undefined) {
          filterGuest = guest;
        } else {
          filterGuest = selectedGuest;
        }

        if (filterGuest) {
            query.push(`guest_id=${filterGuest.id}`);
        }

        if (distance) {
            query.push(`distance=${distance}`);
        }

        console.log(distance, query)

        const queryString = query.join('&');
        return fetch(`/api/hosts?${queryString}`, {
            credentials: 'include',
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
            distance={this.state.distance}
            setFilter={this.setFilter}
            setDistance={this.setDistance}
            selectHost={this.selectHost}
            selectedHost={this.state.selectedHost}
            />;
    }

    render() {
        const matchBoxCs = cs('match', {
            active: this.state.selectedGuest && this.state.selectedHost
        });

        return (
            <div className="unmatched">
                <div className="row">
                    <div className="col-sm-12">
                        <button className={matchBoxCs} onClick={() => this.matchUsers()}>
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
