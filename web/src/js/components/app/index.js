import React from 'react'

import translate, { lang, setLanguage } from 'translate';

export default class App extends React.Component {
    constructor() {
        super();

        if(localStorage && localStorage.getItem("lang"))
        {
            this.state = localStorage.getItem("lang");
        }

        this.state = { lang }

        this.setLanguage = this.setLanguage.bind(this);
    }

    setLanguage(lang) {
        if(localStorage) {
            localStorage.setItem("lang", lang);
        }
        setLanguage(lang);
        this.setState({ lang });
    }

    getChildContext() {
        return { translate };
    }

    renderTranslations() {

        let language = lang;

        if(localStorage && localStorage.getItem("lang"))
        {
            language = localStorage.getItem("lang");
        }

        if (language === 'en') {
            return <button onClick={() => this.setLanguage('no')} class="button button1">Les på norsk</button>
        } else {
            return <button onClick={() => this.setLanguage('en')} class="button button1">Read in English</button>
        }
    }

    render() {
        return <div className="wrapper">
            <div className="header">
                <div><a className="logo" href="https://www.kom-inn.org/"></a></div>
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
