import React from 'react'
import { Link } from 'react-router';

export default class Header extends React.Component {
    render() {
        return <div className="header">
            <div className="location">
                <select disabled>
                    <option>Oslo</option>
                </select>
            </div>
            <div className="tabs">
                <Link to="/" activeClassName="selected">
                        Unmatched
                </Link>
                <Link to="/matched" activeClassName="selected">
                    Matched
                </Link>
            </div>
        </div>
    }
}
