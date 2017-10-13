import React from 'react'

import translate, { lang, setLanguage } from 'translate';

export default class App extends React.Component {
    constructor() {
        super();

        var langId = "no"; // by default
        if(localStorage && localStorage.getItem("lang"))   // if lang id is present in local storage, pick that
        {
            langId =  localStorage.getItem("lang");
        }
        else {   // if langid is passed in the url, pick that
            var href = window.location.href;
            var reg = new RegExp('[?&]' + "lang" + '=([^&#]*)', 'i');
            var string = reg.exec(href);
            if (string) {
                langId = string[1];
                if(localStorage) // and set in local storage
                {
                    localStorage.setItem("lang", langId);
                }
            }
        }

        this.state = { lang : langId}

        this.setLanguage = this.setLanguage.bind(this);

        this.setLanguage(langId);
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
