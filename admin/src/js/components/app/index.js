import React from 'react'

export default class App extends React.Component {
    render() {
        return <div className="wrapper">
            <div className="header">&nbsp;</div>
            <div className="content">
                {this.props.children}
            </div>
            <div className="footer"></div>
        </div>
    }
}
