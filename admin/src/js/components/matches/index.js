import React from 'react';
import Match from './match';

export default class Matches extends React.Component {
    constructor() {
        super();

        this.state = {
            matches: [],
            status: '0'
        };

        this.fetchMatches = this.fetchMatches.bind(this)
    }

    componentDidMount() {
        this.fetchMatches();
    }

    setStatus(e) {
        this.setState({ status: e.target.value }, () => this.fetchMatches());
    }

    fetchMatches() {
        return fetch(`/api/matches?status=${this.state.status}`, {
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(res => res.json()).then(matches => {
            this.setState({ matches })
        });
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
                                return <Match key={match.id} match={match} fetchMatches={this.fetchMatches} />
                            })}
                        </ul>
                    </div>
                </div>
            </div>
        )
    }
}