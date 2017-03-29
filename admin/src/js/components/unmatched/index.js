import React, { PropTypes } from 'react';
import cs from 'classnames';
import { connect } from 'react-redux';

import { fetchGuests } from '../../redux/actions/guests';
import { fetchHosts } from '../../redux/actions/hosts';
import { matchGuestWithHost } from '../../redux/actions/match';
import { selectGuest, selectHost } from '../../redux/actions/ui';

import Person from './person';
import Hosts from './hosts';

class Unmatched extends React.Component {
    constructor() {
        super();

        this.state = {
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
    }

    componentDidMount() {
        this.props.fetchGuests();
    }

    componentWillReceiveProps(nextProps) {
        if (
            nextProps.region !== this.props.region ||
            nextProps.lastMatchId !== this.props.lastMatchId) {
            this.props.fetchGuests();
        }
        if (nextProps.selectedGuestId !== this.props.selectedGuestId && nextProps.selectedGuestId) {
            this.fetchHosts();
        }
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

    fetchHosts() {
        const { filters, distance } = this.state;

        return this.props.fetchHosts({
            filters,
            distance
        });
    }

    selectGuest(guest) {
        this.props.selectGuest(guest.id);
    }

    selectHost(host) {
        this.props.selectHost(host.id);
    }

    renderHosts() {
        if (!this.props.selectedGuestId) {
            return null;
        }

        const guest = this.props.guests.find(g => g.id === this.props.selectedGuestId);

        return <Hosts guest={guest}
            hosts={this.props.hosts}
            filters={this.state.filters}
            distance={this.state.distance}
            setFilter={this.setFilter}
            setDistance={this.setDistance}
            selectHost={this.selectHost}
            selectedHostId={this.props.selectedHostId}
            />;
    }

    render() {
        const matchBoxCs = cs('match', {
            active: this.props.selectedGuestId && this.props.selectedHostId
        });

        return (
            <div className="unmatched">
                <div className="row">
                    <div className="col-sm-12">
                        <button className={matchBoxCs} onClick={this.props.matchGuestWithHost}>
                            Match guest with host
                        </button>
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-6">
                        <h1>Unmatched guests</h1>
                        <ul>
                            {this.props.guests.map(guest => {
                                const selected = this.props.selectedGuestId === guest.id;
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

function mapStateToProps(state) {
    return {
        guests: state.guests.items,
        hosts: state.hosts.items,
        region: state.ui.region,
        selectedGuestId: state.ui.selectedGuestId,
        selectedHostId: state.ui.selectedHostId,
        lastMatchId: state.ui.lastMatchId
    };
}

const mapDispatchToProps = { fetchGuests, fetchHosts, selectGuest, selectHost, matchGuestWithHost };

export default connect(mapStateToProps, mapDispatchToProps)(Unmatched);
