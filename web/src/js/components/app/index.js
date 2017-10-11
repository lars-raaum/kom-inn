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
            return <button onClick={() => this.setLanguage('no')} class="button">Les på norsk</button>
        } else {
            return <button onClick={() => this.setLanguage('en')} class="button">Read in English</button>
        }
    }

    render() {
        return <div className="wrapper">
            <div className="header">
                <div className="translate">
                    {this.renderTranslations()}
                </div>
            </div>
            <div className="content">
                {this.props.children}
            </div>
        </div>
    }
}

App.childContextTypes = {
  translate: React.PropTypes.func
};
