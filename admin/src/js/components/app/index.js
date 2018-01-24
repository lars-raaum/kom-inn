import React from 'react'

import Header from 'components/header';

export default class App extends React.Component {
    constructor() {
        super();

        this.state = { region: null };

        this.setRegion = this.setRegion.bind(this);
        this.getRegion = this.getRegion.bind(this);
    }

    setRegion(region) {
        this.setState({ region });
    }

    getRegion() {
        return this.state.region;
    }

    getChildContext() {
        return {
            region: this.state.region
        };
    }

    render() {
        return <div className="wrapper">
            <Header setRegion={this.setRegion} />
            <div className="container-fluid">
                {this.props.children}
            </div>
            <div className="footer"></div>
        </div>
    }
}

App.childContextTypes = {
  region: React.PropTypes.string
};