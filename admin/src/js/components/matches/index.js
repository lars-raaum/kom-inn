import React from 'react';
import { Link } from 'react-router';

class Person extends React.Component {
    getAdults() {
        const { person } = this.props;

        return parseInt(person.adults_f, 10) + parseInt(person.adults_m, 10);
    }

    render() {
        const { person } = this.props;

        return <li>
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

export default class Matches extends React.Component {
    constructor() {
        super();

        this.state = {
            matches: []
        };
    }

    componentDidMount() {
        this.fetchMatches();
    }

    fetchMatches() {
        return fetch(`/api/matches`, {
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
                    <div className="col-sm-12">
                        <h1>Matched hosts and guests</h1>
                        <ul>
                            {this.state.matches.map(match => {
                                return <div className="row" key={match.id}>
                                    <div className="col-sm-6">
                                        <Person person={match.guest} />
                                    </div>

                                    <div className="col-sm-6">
                                        <Person person={match.host} />
                                    </div>
                                </div>
                            })}
                        </ul>
                    </div>
                </div>
            </div>
        )
    }
}