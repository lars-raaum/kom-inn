import React from 'react'

import translate, { lang, setLanguage } from 'translate';

export default class App extends React.Component {
    constructor() {
        super();

        this.state = { lang }

        this.setLanguage = this.setLanguage.bind(this);
    }

    setLanguage(lang) {
        setLanguage(lang);
        this.setState({ lang });
    }

    getChildContext() {
        return { translate };
    }

    renderTranslations() {
        if (lang === 'en') {
            return <span onClick={() => this.setLanguage('no')}>Les på norsk</span>
        } else {
            return <span onClick={() => this.setLanguage('en')}>Read in English</span>
        }
    }

    render() {
        return <div className="wrapper">
            <div className="header">&nbsp;</div>
            <div className="content">
                {this.props.children}
            </div>
            <div className="footer">
                <div className="translate">
                    {this.renderTranslations()}
                </div>
            </div>
        </div>
    }
}

App.childContextTypes = {
  translate: React.PropTypes.func
};
