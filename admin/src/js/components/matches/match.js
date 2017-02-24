import React from 'react';
import Person from '../common/person';

export default class Match extends React.Component {
    constructor() {
        super();

        this.removePerson = this.removePerson.bind(this);
        this.nagHost = this.nagHost.bind(this);
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

    removePerson(personComponent) {
        return personComponent.remove()
            .then(this.cancelMatch());
    }

    nagHost(e) {
        if (e) {
            e.preventDefault();
        }

        return fetch(`/api/match/${this.props.match.id}/email/host_nag`, {
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
            <a onClick={e => this.nagHost()} className="send-nag-mail" href="#">Send nagging mail to host</a>
            <a onClick={e => this.removeBoth()} className="remove-both" href="#">Remove both persons from DB</a>
            <a onClick={e => this.cancelMatch()} className="cancel-match" href="#">Cancel match</a>
        </li>
    }
}