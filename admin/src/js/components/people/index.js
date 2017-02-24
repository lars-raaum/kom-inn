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
            meta: {
                page: 1,
                limit: 10
            }
            // ,
            // status: '1'
        };
        this.fetchPeople = this.fetchPeople.bind(this);
        this.nextPage = this.nextPage.bind(this);
        this.prevPage = this.prevPage.bind(this);
        this.gotoPage = this.gotoPage.bind(this);
    }

    componentDidMount() {
        this.fetchPeople();
    }

    fetchPeople() {
        return fetch(`/api/people?page=${this.state.meta.page}`, { // ?status=${this.state.status}
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(res => {
            this.setState({
                meta: {
                    count: res.headers.get('X-Count'),
                    page: res.headers.get('X-Page'),
                    offset: res.headers.get('X-Offset'),
                    total: res.headers.get('X-Total'),
                    limit: res.headers.get('X-Limit')
                }
            });
            return res.json();
        }).then(people => { // brab meta data from Headers
            this.setState({
                people
            })
        });
    }

    setPage(page) {
        if (page === this.state.meta.page) {
            return;
        }

        this.setState({
            meta: Object.assign(this.state.meta, {
                page: page
            })
        }, this.fetchPeople);
    }

    nextPage(e) {
        e.preventDefault();
        const max = Math.ceil(this.state.meta.total / this.state.meta.limit);
        const page = this.state.meta.page;

        if (page < max) {
            page++;
        }

        this.setPage(page);
    }

    prevPage(e) {
        e.preventDefault();
        const page = this.state.meta.page;

        if (page > 1) {
            page--;
        }

        this.setPage(page);
    }

    gotoPage(e) {
        e.preventDefault();
        const page = e.target.getAttribute('data-page');
        this.setPage(page);
    }

    render() {
        const N = Math.ceil(this.state.meta.total / this.state.meta.limit);
        const pages = [...Array(N).keys()]; // + 1

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
                    <li>
                        <button name="prev" onClick={this.prevPage}>Previous</button>
                    </li>
                    {pages.map((p) => {
                         p = p + 1;
                        return (<li key={p}>
                            <button data-page={p} onClick={this.gotoPage}> {p} </button>
                        </li>)
                    })}
                    <li>
                        <button name="next" onClick={this.nextPage}>Next</button>
                    </li>
                </ul>
                <p>Showing {this.state.meta.count} of {this.state.meta.total}, page {this.state.meta.page}</p>
            </div>
        </div>

    }
}