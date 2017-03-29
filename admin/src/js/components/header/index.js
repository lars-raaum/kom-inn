import React from 'react'
import { Link, IndexLink } from 'react-router';
import { connect } from 'react-redux';

import { setRegion } from '../../redux/actions/ui';

class Header extends React.Component {
    onChange(e) {
        const region = e.target.value.toLowerCase();
        this.props.setRegion(region);
    }

    render() {
        return <div className="header">
            <div className="location">
                <select onChange={e => this.onChange(e)}>
                    <option>Oslo</option>
                    <option>Bergen</option>
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

const DEFAULT_OBJECT = {};

function mapDispatchToProps(state) {
    const location = state.routing.locationBeforeTransitions || DEFAULT_OBJECT;
    return {
        path: location.pathname
    };
}

export default connect(mapDispatchToProps, { setRegion })(Header);
