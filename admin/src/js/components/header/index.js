import React from 'react'
import { Link, IndexLink } from 'react-router';

export default class Header extends React.Component {

    changeRegion(e) {
        e.preventDefault();
        console.log('Region changed. Change state for unmatched!');
    }

    render() {
        return <div className="header">
            <div className="location">
                <select name="Region" onChange={this.changeRegion}>
                    <option value="ALL">ALL</option>
                    <option value="Oslo" default>Oslo</option>
                    <option value="Bergen">Bergen</option>
                    <option value="Trondheim">Trondheim</option>
                </select>
            </div>
            <div className="tabs">
                <IndexLink to="/" activeClassName="selected">
                    Unmatched
                </IndexLink>
                <Link to="/matches" activeClassName="selected">
                    Matches
                </Link>
                <Link to="/people" activeClassName="selected">
                    People
                </Link>
                <Link to="/usermap" activeClassName="selected">
                    Overview map
                </Link>
            </div>
        </div>
    }
}
