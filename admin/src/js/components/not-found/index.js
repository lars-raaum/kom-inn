import React from 'react'
import { Link } from 'react-router'

export default class NotFound extends React.Component {
    render() {
        return (
            <div>
                <h1>Side ikke funnet.</h1>
                <p>Gå til <Link to="/">forsiden</Link></p>
            </div>
        )
    }
}