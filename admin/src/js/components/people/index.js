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
        const { person } = this.props;
        return fetch(`/api/person/${person.id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(() => { this.props.fetchPeople() }); // TODO update people state instead of refetching
    }

    render() {
        const { person } = this.props;

        return <li>
            <span className="title">{this.getAdults()} adults. {person.children} children.</span>
            <div className={'info'}>
                <span className="name">{person.name}</span> <span className="info">{person.age} år. {person.adults_f} females. {person.adults_m} males.</span> <span className="origin">{person.origin}.</span> <br />
                <span className="phone">Phone: <a href={`tel:${person.phone}`}>{person.phone}</a></span> <span className="email">Email <a href={`mailto:${person.email}`}>{person.email}</a></span> <br />
                <span className="address">{person.address} {person.zipcode}</span> <br />
                <span className="bringing">{person.bringing || <i>No people description</i>}</span> <br />
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
            page: 1,
            limit: 10
            // ,
            // status: '1'
        };
        this.fetchPeople = this.fetchPeople.bind(this);
        this.nextPage = this.nextPage.bind(this);
    }

    componentDidMount() {
        this.fetchPeople();
    }

    fetchPeople() {
        return fetch(`/api/people?page=${this.state.page}`, { // ?status=${this.state.status}
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(res => res.json()).then(people => {
            this.setState({ people })
        });
    }

    nextPage(e) {
        e.preventDefault();
        this.state.page++;
        this.fetchPeople();
    }

    render() {
        return <div>
            <div className="people">
                <h1> People are strange </h1>
                <ul>
                    {this.state.people.map(person => {
                        return <Person key={person.id} person={person} fetchPeople={this.fetchPeople} />
                    })}
                </ul>
            </div>
            <div  className="pagination">
                <ul>
                    <li><button disabled name="prev">Previous</button></li>
                    <li><button disabled name="page-1"> 1 </button></li>
                    <li><button name="page-2"> 2 </button></li>
                    <li><button name="page-3"> 3 </button></li>
                    <li><button name="page-4"> 4 </button></li>
                    <li><button name="next" onClick={this.nextPage}>Next</button> </li>
                </ul>
            </div>
        </div>

    }
}