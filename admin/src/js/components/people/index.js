import React from 'react';
import Person from '../common/person'

export default class People extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            people: [],
            meta: {
                count: 0,
                page: 1,
                offset: 0,
                total: 0,
                limit: 10
            }
            // ,
            // status: '1'
        };
        this.fetchPeople = this.fetchPeople.bind(this);
        this.nextPage = this.nextPage.bind(this);
        this.prevPage = this.prevPage.bind(this);
        this.gotoPage = this.gotoPage.bind(this);
        this.removePerson = this.removePerson.bind(this);
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
        let page = this.state.meta.page;

        if (page < max) {
            page++;
        }

        this.setPage(page);
    }

    prevPage(e) {
        e.preventDefault();
        let page = this.state.meta.page;

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

    removePerson(personComponent) {
        return personComponent.remove()
            .then(() => { this.props.fetchPeople() });

        // @TODO update people state instead of refetching
    }

    render() {
        if (this.state.people.length === 0) {
            return <div className="loading-gif">
                <span>LOADING</span>
            </div>;
        }

        const N = Math.ceil(this.state.meta.total / this.state.meta.limit);
        const pages = [...Array(N).keys()]; // + 1

        return <div>
            <div className="people">
                <h1> People are strange </h1>
                <div>
                    {this.state.people.map(person => {
                        return <Person key={person.id} person={person} fetchPeople={this.fetchPeople} handleRemove={this.removePerson} />
                    })}
                </div>
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