import React, { PropTypes } from 'react';
import cs from 'classnames';

export default class Person extends React.Component {
    getAdults() {
        const { person } = this.props;

        return parseInt(person.adults_f, 10) + parseInt(person.adults_m, 10);
    }

    renderDistance() {
        if (this.props.person.distance === undefined && (this.props.person.loc_lat === null || this.props.person.loc_long === null)) {
            return <span className="distance">
                <svg className="octicon octicon-alert" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path d="M8.865 1.52c-.18-.31-.51-.5-.87-.5s-.69.19-.87.5L.275 13.5c-.18.31-.18.69 0 1 .19.31.52.5.87.5h13.7c.36 0 .69-.19.86-.5.17-.31.18-.69.01-1L8.865 1.52zM8.995 13h-2v-2h2v2zm0-3h-2V6h2v4z"></path></svg>
            </span>
        }

        if (this.props.person.distance === undefined) {
            return null;
        }

        const distance = this.props.person.distance > 1 ? Math.floor(this.props.person.distance) : this.props.person.distance.toFixed(1);
        return <span className="distance"><b>{distance} km</b></span>;
    }

    render() {
        const { person, selected } = this.props;

        const className = cs({ selected });

        return <li className={className} onClick={() => this.props.onClick(person)}>
            <b>{this.getAdults()} adults. {person.children} children.</b>
            <div className={'info'}>
                <span className="name">{person.name}</span> <span className="info">{person.age} år. {person.adults_f} females. {person.adults_m} males. {person.children} children.</span> <span className="origin">{person.origin}.</span> <br />
                <span className="bringing">{person.bringing || <i>No people description</i>}</span> <br />

                <span className="phone">Phone: <a href={`tel:${person.phone}`}>{person.phone}</a></span> <span className="email">Email <a href={`mailto:${person.email}`}>{person.email}</a></span> <br />
                <span className="address">{person.address} {person.zipcode}</span>
            </div>
            <div className="freetext">{person.freetext || <i>No description</i>}</div>
            {this.renderDistance()}
            <div className="updated">{person.waited}</div>
        </li>
    }
}
