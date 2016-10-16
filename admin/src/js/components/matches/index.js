import React from 'react';
import { Link } from 'react-router';

class Person extends React.Component {
    getAdults() {
        const { person } = this.props;

        return parseInt(person.adults_f, 10) + parseInt(person.adults_m, 10);
    }

    render() {
        const { person } = this.props;

        return <div>
            <h2>{this.getAdults()} adults. {person.children} children.</h2>
            <div className={'info'}>
                <span className="name">{person.name}</span>
                <span className="info">{person.age} år. {person.adults_f} females. {person.adults_m} males.</span>
                <span className="origin">{person.origin}.</span>
            </div>
            <span className="freetext">{person.freetext}</span>
        </div>
    }
}

class Match extends React.Component {
    cancelMatch() {
        return fetch(`/api/match/${this.props.match.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(matches => this.props.fetchMatches());
    }

    updateMatch() {
        return fetch(`/api/match/${this.props.match.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: this.statusComp.value,
                comment: this.commentComp.value
            })
        }).then(matches => this.props.fetchMatches());
    }

    render() {
        const { match } = this.props;

        return <li className="row" key={match.id}>
            <div className="col-sm-4">
                <Person person={match.guest} />
            </div>

            <div className="col-sm-4">
                <Person person={match.host} />
            </div>

            <div className="col-sm-4">
                <div>
                    Status:
                    <select defaultValue={match.status} ref={c => this.statusComp = c}>
                        <option value="0">Match</option>
                        <option value="1">Confirmed</option>
                        <option value="2">Executed</option>
                    </select>
                </div>
                <div>
                    Comment:
                    <input defaultValue={match.comment} ref={c => this.commentComp = c} />
                </div>
                <button onClick={e => this.updateMatch()}>Update match</button>
                <button onClick={e => this.cancelMatch()}>Cancel match</button>
            </div>
        </li>
    }
}



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
        console.log(e.target.value);
        this.setState({ status: e.target.value }, () => this.fetchMatches());
    }

    fetchMatches() {
        return fetch(`/api/matches?status=${this.state.status}`, {
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