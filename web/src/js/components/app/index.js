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
