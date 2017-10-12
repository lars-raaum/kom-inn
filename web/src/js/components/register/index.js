import React from 'react'
import { Link } from 'react-router'

export default class Register extends React.Component {
    constructor() {
        super()
        this.form = {};

        this.state = { gender: null, type: null, error: null, success: false, pending: false };

        this.submit = this.submit.bind(this);
    }

    getFormData() {
        const data = {
            type: this.state.type,
            name: this.form.name.value,
            email: this.form.email.value,
            phone: this.form.phone.value,
            gender: this.state.gender,
            age: this.form.age.value,
            children: this.form.children.value,
            adults_m: this.form.adults_male.value,
            adults_f: this.form.adults_female.value,
            bringing: this.form.bringing.value,
            origin: this.form.origin.value,
            food_concerns: this.form.food_concerns ? this.form.food_concerns.value : null,
            address: this.form.address.value,
            zipcode: this.form.zipcode.value,
            freetext: this.form.freetext.value
        };

        return data;
    }

    renderFoodConcerns() {
        if (this.state.type !== 'guest') {
            return null;
        }

        return  <div className="mdl-grid mdl-cell mdl-cell--12-col">
                     <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input className="mdl-textfield__input" type="text" max="120" id="food_concerns"  ref={(c) => this.form.food_concerns = c} required />
                        <label className="mdl-textfield__label" htmlFor="food_concerns">{this.context.translate('Er det noe mat du/dere ikke spiser')}?</label>
                    </div>
                </div>
    }

    renderError() {
        if (this.state.error === null) {
            return null;
        }

        return <span className="error">{this.state.error}</span>
    }

    submit(e) {
        e.preventDefault();

        this.setState({ pending: true });

        fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.getFormData())
        }).then(response => {
            if (response.status !== 200) {
                const error = new Error(response.statusText)
                error.response = response
                throw error
            }

            return response.json();
        }).then(() => {
            this.setState({ pending: false, success: true });
            if (this.state.type === 'host') {
                window.location.href = '/takk/vert';
            } else {
                window.location.href = '/takk/gjest';
            }

        }).catch(err => {
            this.setState({ pending: false, error: err.message ? err.message : err });
        });
    }

    renderPending() {
        return (<div className="main-page">Vennligst vent...</div>);
    }

    renderSuccess() {
        return (<div className="main-page">Takk!</div>);
    }

    renderAboutYouSection(translate) {
        return (<div>
            <h2>{translate('Hvem er du')}?</h2>
            <div className="form-group">
            <div className= "mdl-grid">
        
                    <div className=" mdl-cell mdl-cell--12-col ">
                        <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input className="mdl-textfield__input" type="text" id="name" ref={(c) => this.form.name = c} required />
                            <label className="mdl-textfield__label" htmlFor="name">{translate('Hva er navnet ditt')}</label>
                        </div>
                    </div>

                    <div className="mdl-cell mdl-cell--12-col ">
                        <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input className="mdl-textfield__input" type="text" max="120" id="age" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.age = c} required />
                            <label className="mdl-textfield__label" htmlFor="age">{translate('Alder')}</label>
                            <span className="mdl-textfield__error">Input is not a number!</span>
                        </div>
                    </div>

                    <div className="mdl-cell mdl-cell--12-col  ">
                    <fieldset>
                        <legend><b>{translate('Kjønn')}</b></legend>
                        
                        <label className="mdl-radio mdl-js-radio mdl-js-ripple-effect" htmlFor="gender-male">
                            <input type="radio" id="gender-male" onChange={() => this.setState({gender: 'male' })} className="mdl-radio__button" name="gender" />
                            <span className="mdl-radio__label">{translate('Mann')}</span>
                        </label>
                        <label className="mdl-radio mdl-js-radio mdl-js-ripple-effect" htmlFor="gender-female">
                            <input type="radio" id="gender-female" onChange={() => this.setState({gender: 'female' })} className="mdl-radio__button" name="gender" />
                            <span className="mdl-radio__label"><h6>{translate('Kvinne')}</h6></span>
                        </label>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div className="mdl-cell mdl-cell--12-col  ">
                <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                    <input className="mdl-textfield__input" type="text" id="origin" ref={(c) => this.form.origin = c} required />
                    <label className="mdl-textfield__label" htmlFor="origin">{translate('Hvor er du fra')}</label>
                </div>
            </div>
        </div>)
    }

    renderYourGuestsSection(translate) {
        return (<div>
                <h2>{translate('Hvor mange blir med på middag i tillegg til deg')}?</h2>
                <div className="form-group">
                    <div className="mdl-grid">

                        <div className="mdl-cell mdl-cell--4-col">
                            <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input className="mdl-textfield__input" type="text" max="120" id="adults_female" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.adults_female = c} required />
                                <label className="mdl-textfield__label" htmlFor="adults_female">{translate('Kvinner')}</label>
                                <span className="mdl-textfield__error">Input is not a number!</span>
                            </div>
                        </div>

                        <div className="mdl-cell mdl-cell--4-col">
                            <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input className="mdl-textfield__input" type="text" max="120" id="adults_male" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.adults_male = c} required />
                                <label className="mdl-textfield__label" htmlFor="adults_male">{translate('Menn')}</label>
                                <span className="mdl-textfield__error">Input is not a number!</span>
                            </div>
                        </div>

                        <div className="mdl-cell mdl-cell--4-col">
                            <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                <input className="mdl-textfield__input" type="text" max="120" id="children" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.children = c}  required />
                                <label className="mdl-textfield__label" htmlFor="children">{translate('Barn')} (0-18 {translate('År').toLowerCase()})</label>
                                <span className="mdl-textfield__error">Input is not a number!</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div className="mdl-textfield mdl-js-textfield">
                    <label className="mdl-textfield__label" htmlFor="bringing">{translate('Hvem tar du med deg? Vet vi mer er sjansen for at vi finner en god match større. Eksempelvis alder på barna.')}</label>
                    <textarea className="mdl-textfield__input" type="text" id="bringing" ref={(c) => this.form.bringing = c} rows="3" ></textarea>
                </div>
                
            </div>);
    }
    renderContactInfoSection(translate) {
        return (<div>
                <h2>{translate('Hvordan kan vi kontakte deg')}?</h2>

                <div className="mdl-grid mdl-cell mdl-cell--12-col">
                     <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input className="mdl-textfield__input" type="text" max="120" id="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"  ref={(c) => this.form.email = c} required />
                        <label className="mdl-textfield__label" htmlFor="email">{translate('Hva er e-postadressen din')}?</label>
                        <span className="mdl-textfield__error">Input is not an email!</span>
                    </div>
                </div>

                 <div className="mdl-grid mdl-cell mdl-cell--12-col">
                     <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input className="mdl-textfield__input" type="text" max="120" id="phone" pattern="(7|8|9)\d{9}$"  ref={(c) => this.form.phone = c}  required />
                        <label className="mdl-textfield__label" htmlFor="phone">{translate('Hva er telefonnummeret ditt')}?</label>
                        <span className="mdl-textfield__error">Input is not a valid phone number!</span>
                    </div>
                </div>

                <h2>{translate('Hvor bor du')}?</h2>

                <div className="mdl-grid mdl-cell mdl-cell--12-col">
                     <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input className="mdl-textfield__input" type="text" max="120" id="address"  ref={(c) => this.form.address = c} required />
                        <label className="mdl-textfield__label" htmlFor="address">{translate('Adresse')}</label>
                    </div>
                </div>

                 <div className="mdl-grid mdl-cell mdl-cell--12-col">
                     <div className="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input className="mdl-textfield__input" type="text" max="120" id="zipcode" pattern="[0-9]{5}" ref={(c) => this.form.zipcode = c} required />
                        <label className="mdl-textfield__label" htmlFor="zipcode">{translate('Postnummer')}</label>
                        <span className="mdl-textfield__error">Input is not a valid zipcode</span>
                    </div>
                </div>
            </div>);
    }

    renderOthersSection(translate) {
        return (<div>
            <h2>{translate('Annet')}?</h2>
            {this.renderFoodConcerns()}
              <div className="mdl-textfield mdl-js-textfield">
                    <label className="mdl-textfield__label" htmlFor="freetext">{translate('Er det noe annet vi trenger å vite om deg/dere')}?</label>
                    <textarea className="mdl-textfield__input" type="text" id="freetext" ref={(c) => this.form.freetext = c} rows="3" ></textarea>
                </div>
        </div>);
    }

    render() {
        const translate = this.context.translate;

        if (this.state.pending) {
            return this.renderPending();
        }

        if (this.state.success) {
            return this.renderSuccess();
        }

        let typeForm, intro;

        intro = (<div>
            <h1>Kom inn</h1>
            <p>{translate('Mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk')}.</p>
            <p>{translate('Hvis du vil komme på middag, eller invitere noen på middag registrerer du deg nedenfor. Vi matcher dere basert på hvem dere er og hvor dere bor og setter dere i kontakt for å avtale tidspunkt.')}</p>
            </div>
        )
        if (this.props.params.type == 'gjest' || this.props.params.type == 'vert') {
            // Good, lets proceed
        } else {
            // No preselected, assume guest as per #24
            this.props.params.type = 'gjest';
        }
        if (this.props.params.type == 'gjest') {
            this.state.type = 'guest';
            intro = (<div>
                <h1>{translate("Jeg vil komme på middag!")}</h1>
                <p>{translate("Vil du øve på å snakke norsk? Ideen bak Kom inn er at mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk.")}</p>
                <p>{translate("Registrer deg nedenfor dersom du vil komme på middag. For å koble dere trenger vi å vite litt om hvem dere er og hvor dere bor. Når vi finner en match ber vi verten ta kontakt for å avtale tidspunkt.")}</p>
                <p>{translate("Vil du heller invitere noen på middag,")} <a href="/som/vert">{translate("gå til skjema for vert.")}</a></p>
                </div>
            )
        } else if (this.props.params.type == 'vert') {
            this.state.type = 'host';
            intro = ( <div>
                <h1>{translate("Jeg vil invitere noen på middag!")}</h1>
                <p>{translate("Vil du hjelpe noen å lære norsk? Ideen bak Kom inn er at mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk.")}</p>
                <p>{translate("Registrer deg nedenfor dersom du vil invitere noen på middag. For å koble dere trenger vi å vite litt om hvem dere er og hvor dere bor. Når vi finner en match setter vi dere i kontakt for å avtale tidspunkt.")}</p>
                <p>{translate("Vil du heller bli invitert på middag,")} <a href="/som/gjest">{translate("gå til skjema for gjest.")}</a></p>
                </div>
            )
        } else {
            typeForm = (
                <div>
                    <h2>{translate('Hva vil du')}</h2>
                    <div className="form-group">
                        <div className="radio-field">
                            <label htmlFor="type-guest"><input type="radio" name="type" id="type-guest" onChange={() => this.setState({type: 'guest' })} required /> {translate('Komme til noen på middagsbesøk')}</label> <br />
                            <label htmlFor="type-host"><input type="radio" name="type" id="type-host" onChange={() => this.setState({type: 'host' })} required /> {translate('Invitere noen på middagsbesøk')}</label>
                        </div>
                    </div>
                </div>
            )
        }
        
        return (
            <div className="main-page">
                {intro}
                <p>{translate('Du finner mer informasjon på')} <a href="http://www.kom-inn.org">www.kom-inn.org.</a></p>
                <form onSubmit={this.submit}>

                    {typeForm}

                    {this.renderAboutYouSection(translate)}

                    {this.renderYourGuestsSection(translate)}

                    {this.renderContactInfoSection(translate)}

                    {this.renderOthersSection(translate)}

                    <div className="submit">
                        <button type="submit">{translate('Send inn')}</button>
                    </div>

                    {this.renderError()}
                </form>
            </div>
        )
    }
}

Register.contextTypes = {
    translate: React.PropTypes.func
}
