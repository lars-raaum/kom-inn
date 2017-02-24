import React from 'react';

export default class Person extends React.Component {
    constructor(props) {
        super(props);
        this.remove = this.remove.bind(this);
    }

    getAdults() {
        const { person } = this.props;
        return parseInt(person.adults_f, 10) + parseInt(person.adults_m, 10);
    }

    remove() {
        const { person } = this.props;
        return fetch(`/api/person/${person.id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        });
    }

    render() {
        const { person, handleRemove } = this.props;

        return <div className="person">
            <span className="title">{this.getAdults()} adults. {person.children} children.</span>
            <div className={'info'}>
                <span className="name">{person.name}</span> <span className="info">{person.age} år. {person.adults_f} females. {person.adults_m} males. {person.children} children.</span> <span className="origin">{person.origin}.</span> <br />
                <span className="phone">Phone: <a href={`tel:${person.phone}`}>{person.phone}</a></span> <span className="email">Email <a href={`mailto:${person.email}`}>{person.email}</a></span> <br />
                <span className="address">{person.address} {person.zipcode}</span> <br />
                <span className="bringing">{person.bringing || <i>No people description</i>}</span> <br />
                <span className="freetext">{person.freetext || <i>No description</i>}</span> <br />
                <a href="#" onClick={() => handleRemove(this)}>Remove person from database</a>
            </div>
        </div>
    }
}