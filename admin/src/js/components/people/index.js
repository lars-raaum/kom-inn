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

        return <li>
            <span className="title">{this.getAdults()} adults. {person.children} children.</span>
            <div className={'info'}>
                <span className="name">{person.name}</span> <span className="info">{person.age} år. {person.adults_f} females. {person.adults_m} males.</span> <span className="origin">{person.origin}.</span> <br />
                <span className="phone">Phone: <a href={`tel:${person.phone}`}>{person.phone}</a></span> <span className="email">Email <a href={`mailto:${person.email}`}>{person.email}</a></span> <br />
                <span className="address">{person.address} {person.zipcode}</span> <br />
                <span className="freetext">{person.freetext || <i>No description</i>}</span> <br />
                <a href="#" onClick={this.remove}>Remove person from database</a>
            </div>
        </li>
    }
}

export default class People extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            people: [],
            status: '1'
        };
        this.fetchPeople = this.fetchPeople.bind(this);
    }

    componentDidMount() {
        this.fetchPeople();
    }

    fetchPeople() {
        return fetch(`/api/people?status=${this.state.status}`, {
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(res => res.json()).then(people => {
            this.setState({ people })
        });
    }

    render() {
        return <div className="people">
            <h1> People are strange </h1>
            <ul>
                {this.state.people.map(person => {
                    return <Person key={person.id} person={person} fetchPeople={this.fetchPeople} />
                })}
            </ul>
        </div>
    }
}