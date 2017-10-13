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
          return <a className="mdl-navigation__link translate" href="" onClick={(e) => {e.preventDefault();this.setLanguage('no')}}>Les på norsk</a>
        } else {
          return <a className="mdl-navigation__link translate" href="" onClick={(e) => {e.preventDefault();this.setLanguage('en')}}>Read in English</a>
        }
    }

    render() {
        return <div className="demo-layout-transparent mdl-layout mdl-js-layout">
  <header className="mdl-layout__header mdl-layout__header--transparent">
    <div className="mdl-layout__header-row">
    <a className="mdl-layout-title" href="http://www.kom-inn.org" id="top">        
      <img src="//static1.squarespace.com/static/57681c7520099ed9b9f13e4d/t/5768504229687fc14c0ff129/1507756555899/?format=1500w" alt="Kom inn"/>
    </a>
    <div className="mdl-layout-spacer"></div>
      <nav className="mdl-navigation">
        {this.renderTranslations()}
      </nav>
    </div>
  </header>
  <main className="mdl-layout__content">
    <div className="content">
      {this.props.children}
    </div>
  </main>
</div>

    }
}

App.childContextTypes = {
  translate: React.PropTypes.func
};
