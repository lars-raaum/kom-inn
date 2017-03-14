import React from 'react';
import { Link } from 'react-router';

class Person extends React.Component {
    constructor() {
        super();

        this.remove = this.remove.bind(this);
    }

    getAdults() {
        const { person } = this.props;

        return parseInt(person.adults_f, 10) + parseInt(person.adults_m, 10);
    }

    remove(e) {
        e.preventDefault();

        this.props.removePerson(this.props.person);
    }

    render() {
        const { person } = this.props;

        return <div>
            <span className="title">{this.getAdults()} adults. {person.children} children.</span>
            <div className={'info'}>
                <span className="name">{person.name}</span> <span className="info">{person.age} år. {person.adults_f} females. {person.adults_m} males. {person.children} children.</span> <span className="origin">{person.origin}.</span> <br />
                <span className="phone">Phone: <a href={`tel:${person.phone}`}>{person.phone}</a></span> <span className="email">Email <a href={`mailto:${person.email}`}>{person.email}</a></span> <br />
                <span className="address">{person.address} {person.zipcode}</span> <br />
                <span className="bringing">{person.bringing || <i>No people description</i>}</span> <br />
                <span className="freetext">{person.freetext || <i>No description</i>}</span> <br />
                <a href="#" onClick={this.remove}>Remove person from database</a>
            </div>
        </div>
    }
}

class Match extends React.Component {
    constructor() {
        super()

        this.removePerson = this.removePerson.bind(this);
        this.sendReminder = this.sendReminder.bind(this);
    }

    cancelMatch(e) {
        if (e) {
            e.preventDefault();
        }

        return fetch(`/api/match/${this.props.match.id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(matches => this.props.fetchMatches());
    }

    removeBoth(e) {
        if (e) {
            e.preventDefault();
        }

        return fetch(`/api/match/${this.props.match.id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(() => {
            return fetch(`/api/person/${this.props.match.host_id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
        }).then(() => {
            return fetch(`/api/person/${this.props.match.guest_id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
        }).then(matches => this.props.fetchMatches());
    }

    updateMatch() {
        return fetch(`/api/match/${this.props.match.id}`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: this.statusComp.value,
                comment: this.commentComp.value
            })
        }).then(matches => this.props.fetchMatches());
    }

    removePerson(person) {
        return this.cancelMatch().then(() => {
            return fetch(`/api/person/${person.id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
        })
    }

    sendReminder(e) {
        if (e) {
            e.preventDefault();
        }

        return fetch(`/api/match/${this.props.match.id}/email/reminder`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(d => console.log("Mailed"));
    }

    render() {
        const { match } = this.props;

        return <li className="row" key={match.id}>
            <div className="col-sm-2">
                <span className="matched">{match.created}</span>
            </div>
            <div className="col-sm-3">
                <Person person={match.guest} removePerson={this.removePerson} />
            </div>

            <div className="col-sm-3">
                <Person person={match.host} removePerson={this.removePerson} />
            </div>

            <div className="col-sm-1">
                <div>
                    <select defaultValue={match.status} ref={c => this.statusComp = c}>
                        <option value="0">Match</option>
                        <option value="1">Confirmed</option>
                        <option value="2">Executed</option>
                    </select>
                </div>
            </div>
            <div className="col-sm-2">
                <div>
                    Comment:
                    <input defaultValue={match.comment} ref={c => this.commentComp = c} />
                </div>
            </div>
            <div className="col-sm-1">
                <button onClick={e => this.updateMatch()}>Update match</button>
            </div>
            <a onClick={e => this.sendReminder()} className="send-nag-mail" href="#">Send reminder mail to host</a>
            <a onClick={e => this.removeBoth()} className="remove-both" href="#">Remove both persons from DB</a>
            <a onClick={e => this.cancelMatch()} className="cancel-match" href="#">Cancel match</a>
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