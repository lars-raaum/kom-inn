import React from 'react'
import { Link, IndexLink } from 'react-router';

export default class Header extends React.Component {
    render() {
        return <div className="header">
            <div className="location">
                <select disabled>
                    <option>Oslo</option>
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
