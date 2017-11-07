import React from 'react';
import Match from './match';
import { fetchMatches } from '../../actions/match';

export default class Matches extends React.Component {
    constructor() {
        super();

        this.state = {
            matches: [],
            status: '0'
        };

        this.fetchMatches = this.fetchMatches.bind(this);
        this.optimisticRemoveMatch = this.optimisticRemoveMatch.bind(this);
        this.optimisticUpdateMatch = this.optimisticUpdateMatch.bind(this);
    }

    componentDidMount() {
        this.fetchMatches();
    }

    setStatus(e) {
        this.setState({ status: e.target.value }, () => this.fetchMatches());
    }

    fetchMatches() {
        const { status } = this.state;
        return fetchMatches({ status }).then(({ response }) => {
            if (!Array.isArray(response)) {
                response = [];
            }

            this.setState({
                matches: response
            })
        });
    }

    optimisticRemoveMatch(id) {
        const matches = this.state.matches.filter(match => match.id !== id);
        this.setState({ matches });
    }

    optimisticUpdateMatch(id, data) {
        let match = this.state.matches.find(match => match.id === id);

        match = Object.assign(match, data);

        this.setState({ matches });
    }

    render() {
        return (
            <div className="matches">
                <div className="row">
                    <div className="col-sm-3" style={{ float: 'right', textAlign: 'right' }}>
                        Show:
                        <select defaultValue={this.state.status} onChange={e => this.setStatus(e)}>
                            <option value="0">Match</option>
                            <option value="1">Confirmed</option>
                            <option value="2">Executed</option>
                        </select>
                    </div>
                </div>
                <div className="row">
                    <div className="col-sm-12">
                        <h1>Matched hosts and guests</h1>
                        <ul>
                            {this.state.matches.map(match => {
                                return <Match key={match.id} match={match} optimisticRemoveMatch={this.optimisticRemoveMatch} optimisticUpdateMatch={this.optimisticUpdateMatch} fetchMatches={this.fetchMatches} />
                            })}
                        </ul>
                    </div>
                </div>
            </div>
        )
    }
}
