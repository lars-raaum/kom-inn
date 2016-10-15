import React from 'react'

export default class Header extends React.Component {
    render() {
        return <div className="header">
            <div className="location">
                <select disabled>
                    <option>Oslo</option>
                </select>
            </div>
            <ul className="tabs">
                <li>Unmatched</li>
                <li>Matched</li>
            </ul>
        </div>
    }
}
