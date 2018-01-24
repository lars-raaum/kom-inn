import React from 'react';
import Person from '../common/person'
import { deletePerson, fetchPeople, convertPerson } from '../../actions/person';

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
                limit: 150,
            }
            ,
            status: '1'
        };
        this.fetchPeople = this.fetchPeople.bind(this);
        this.nextPage = this.nextPage.bind(this);
        this.prevPage = this.prevPage.bind(this);
        this.gotoPage = this.gotoPage.bind(this);
        this.setStatusAll = this.setStatusAll.bind(this);
        this.setStatusExpired = this.setStatusExpired.bind(this);
        this.setStatusDeleted = this.setStatusDeleted.bind(this);
        this.setStatusActive = this.setStatusActive.bind(this);
        this.setStatusUsed = this.setStatusUsed.bind(this);
        this.removePerson = this.removePerson.bind(this);
        this.convertPerson = this.convertPerson.bind(this);
    }

    componentDidMount() {
        this.fetchPeople();
    }

    shouldComponentUpdate(nextProps, nextState, nextContext) {
        if (this.context.region !== nextContext.region) {
            this.fetchPeople(nextContext.region);
        }

        return true;
    }

    fetchPeople(region = this.context.region) {
        this.setState({ loading: true });
        return fetchPeople({
            page: this.state.meta.page,
            limit: this.state.meta.limit,
            status: this.state.status,
            region: region
        }).then(({ response, headers }) => {
            this.setState({
                loading: false,
                people: response,
                meta: {
                    count: headers.get('X-Count'),
                    page: headers.get('X-Page'),
                    offset: headers.get('X-Offset'),
                    total: headers.get('X-Total'),
                    limit: headers.get('X-Limit')
                }
            });
        });
    }

    setPage(page = this.state.meta.page) {
        // if (page === this.state.meta.page) {
        //     return;
        // }

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

    removePerson(id) {
        return deletePerson({ id }).then(() => this.fetchPeople());
    }

    convertPerson(id) {
        return convertPerson({ id }).then(() => this.fetchPeople());
    }

    setStatusAll(e) {
        e.preventDefault();
        this.state.status = null;
        this.setPage();
    }

    setStatusDeleted(e) {
        e.preventDefault();
        this.state.status = '-1';
        this.setPage();
    }

    setStatusExpired(e) {
        e.preventDefault();
        this.state.status = '-2';
        this.setPage();
    }

    setStatusActive(e) {
        e.preventDefault();
        this.state.status = '1';
        this.setPage();
    }

    setStatusUsed(e) {
        e.preventDefault();
        this.state.status = '2';
        this.setPage();
    }

    // How to do this as after rerender?
    static resetStatusSelectors(disableName) {
        for (let s of window.document.getElementsByClassName("status-selector")) {
            console.log(s);
            s.removeAttribute('disabled');
        }
        for (let s of window.document.getElementsByClassName(disableName)) {
            console.log(s);
            s.setAttribute('disabled', 'disabled');
        }
    }

    render() {
        if (this.state.loading) {
            return <div className="loading-gif">
                <span>LOADING</span>
            </div>;
        }

        const N = Math.ceil(this.state.meta.total / this.state.meta.limit);
        const pages = [...Array(N).keys()]; // + 1

        // Also set the current status selector to disabled
        let status_description;
        switch (this.state.status) {
            case '-2':
                status_description  = "Expired users";
                break;
            case '-1':
                status_description  = "Delete users";
                break;
            case '1':
                status_description  = "Active users";
                break;
            case '2':
                status_description  = "Used users";
                break;
            case null:
            default:
                status_description  = "All users";
                break;

        }

        let region = '';
        console.log(this.context.region)
        if (this.context.region && this.context.region.length) {
            region = `in ${this.context.region}`;
        }

        const pagination = <div className="pagination">
            <ul>
                <li><button className="status-selector status-all" name="status-all" onClick={this.setStatusAll}>All users</button></li>
                <li><button className="status-selector status-active" name="status-active" onClick={this.setStatusActive} >Active users</button></li>
                <li><button className="status-selector status-used" name="status-used" onClick={this.setStatusUsed}>Used users</button></li>
                <li><button className="status-selector status-expired" name="status-expired" onClick={this.setStatusExpired}>Expired users</button></li>
                <li><button className="status-selector status-deleted" name="status-deleted" onClick={this.setStatusDeleted}>Deleted users</button></li>
            </ul>
            <p><strong>{status_description} {region}</strong>, Showing {this.state.meta.count} of {this.state.meta.total} , page {this.state.meta.page}</p>
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
        </div>;

        return <div>
            <div className="people">
                {pagination}
                <div>
                    {this.state.people.map(person => {
                        return <Person key={person.id} person={person} removePerson={this.removePerson} convertPerson={this.convertPerson} />
                    })}
                </div>
                {pagination}
            </div>
        </div>

    }
};

People.contextTypes = {
    region: React.PropTypes.string
};
