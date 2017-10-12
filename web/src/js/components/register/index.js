import React from 'react'
import { Link } from 'react-router'

export default class Register extends React.Component {
    constructor(props) {
        super(props)
        const { params } = this.props;
        const paramsType = params && params.type;
        // if type is vert type is host, if anything else default to guest
        const type = paramsType === 'vert' ? 'host' : 'guest';

        this.form = {};

        this.state = { gender: null, type, error: null, success: false, pending: false };

        this.submit = this.submit.bind(this);
    }

    componentDidMount() {
        var cleanTextFields = document.querySelectorAll('.textfield--clean input');
        for(let i=0; i<cleanTextFields.length; i++){

            cleanTextFields[i].onblur = function(){
                this.onblur = undefined;
                this.parentElement.classList.remove('textfield--clean')
            };
        };
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
        return  <div className="mdl-cell mdl-cell--6-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                    <input className="mdl-textfield__input" type="text" id="food_concerns"  ref={(c) => this.form.food_concerns = c}  />
                    <label className="mdl-textfield__label" htmlFor="food_concerns">{this.context.translate('Er det noe mat du/dere ikke spiser')}?</label>
                </div>
    }

    renderError() {
        if (this.state.error === null) {
            return null;
        }

        return <span className="error">{this.state.error}</span>
    }

    validateInputAndUpdateErrorMessage(){
        console.log(this.state.gender)
        var invalidInputs = document.getElementsByClassName('is-invalid');
        if(invalidInputs && invalidInputs.length > 0){
            for(let i=0; i<invalidInputs.length; i++){
                invalidInputs[i].classList.remove('textfield--clean');
            }
            this.setState({error: this.context.translate('vær så snill å fikse uthevede feil')});
            return false;
        }
        if(!this.state.gender){
            this.setState({error: this.context.translate('vennligst velg kjønn')});
            return false;
        }
        return true;
    }

    submit(e) {
        e.preventDefault();

        if(!this.validateInputAndUpdateErrorMessage()) {
            return;
        }

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
            <h3>{translate('Hvem er du')}?</h3>
            <div className="form-group">
                <div className= "mdl-grid">

                    <fieldset className="mdl-cell mdl-cell--12-col  ">
                        <label className="mdl-radio mdl-js-radio mdl-js-ripple-effect" htmlFor="guest-user">
                            <input type="radio" id="guest-user" className="mdl-radio__button" name="user-type" checked={this.state.type==='guest'}
                            onChange={() => this.setState({type: 'guest' })} />
                            <span className="mdl-radio__label">{translate("Jeg vil komme på middag!")}</span>
                        </label>
                        <div className="mdl-layout-spacer"></div>
                        <label className="mdl-radio mdl-js-radio mdl-js-ripple-effect" htmlFor="host-user">
                            <input type="radio" id="host-user" className="mdl-radio__button" name="user-type" checked={this.state.type==='host'}
                            onChange={() => this.setState({type: 'host' })} />
                            <span className="mdl-radio__label">{translate("Jeg vil invitere noen på middag!")}</span>
                        </label>
                    </fieldset>

                    <fieldset>
                        <div className="mdl-cell mdl-cell--6-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                            <input className="mdl-textfield__input" type="text" id="name" pattern="[a-zA-Z][a-zA-Z_\.]{1,20}$" ref={(c) => this.form.name = c} required />
                            <label className="mdl-textfield__label" htmlFor="name">{translate('Hva er navnet ditt')}*</label>
                            <span className="mdl-textfield__error">This is not a valid name</span>
                        </div>

                        <div className="mdl-cell mdl-cell--6-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                            <input className="mdl-textfield__input" type="number" max="120" id="age" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.age = c} required />
                            <label className="mdl-textfield__label" htmlFor="age">{translate('Alder')}*</label>
                            <span className="mdl-textfield__error">This is not a valid age</span>
                        </div>
                        <div className="mdl-cell mdl-cell--12-col">
                            <legend>{translate('Kjønn')}</legend>

                            <label className="mdl-radio mdl-js-radio mdl-js-ripple-effect" htmlFor="gender-male">
                                <input type="radio" id="gender-male" onChange={() => this.setState({gender: 'male' })} className="mdl-radio__button" name="gender" />
                                <span className="mdl-radio__label">{translate('Mann')}</span>
                            </label>
                            <div className="mdl-layout-spacer"></div>
                            <label className="mdl-radio mdl-js-radio mdl-js-ripple-effect" htmlFor="gender-female">
                                <input type="radio" id="gender-female" onChange={() => this.setState({gender: 'female' })} className="mdl-radio__button" name="gender" />
                                <span className="mdl-radio__label"><h6>{translate('Kvinne')}</h6></span>
                            </label>
                        </div>
                        <div className="mdl-cell mdl-cell--12-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                            <input className="mdl-textfield__input" type="text" id="origin" ref={(c) => this.form.origin = c} required />
                            <label className="mdl-textfield__label" htmlFor="origin">{translate('Hvor er du fra')}*</label>
                            <span className="mdl-textfield__error">This is not a valid location</span>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>)
    }

    renderYourGuestsSection(translate) {
        return (
            <div>
                <h3>{translate('Hvor mange blir med på middag i tillegg til deg')}?</h3>
                <div className="form-group">
                    <div>
                        <fieldset>
                            <div className="mdl-cell mdl-cell--3-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                                <input className="mdl-textfield__input" type="number" id="adults_female" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.adults_female = c} required />
                                <label className="mdl-textfield__label" htmlFor="adults_female">{translate('Kvinner')}*</label>
                                <span className="mdl-textfield__error">This is not a valid number</span>
                            </div>

                            <div className="mdl-cell mdl-cell--3-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                                <input className="mdl-textfield__input" type="number" id="adults_male" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.adults_male = c} required />
                                <label className="mdl-textfield__label" htmlFor="adults_male">{translate('Menn')}*</label>
                                <span className="mdl-textfield__error">This is not a valid number</span>
                            </div>

                            <div className="mdl-cell mdl-cell--3-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                                <input className="mdl-textfield__input" type="number" id="children" pattern="-?[0-9]*(\.[0-9]+)?" ref={(c) => this.form.children = c}  required />
                                <label className="mdl-textfield__label" htmlFor="children">{translate('Barn')} (0-18 {translate('År').toLowerCase()})*</label>
                                <span className="mdl-textfield__error">This is not a valid number</span>
                            </div>
                        </fieldset>

                    </div>
                </div>

                <div className="mdl-cell mdl-cell--10-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                    <label className="mdl-textfield__label" htmlFor="bringing">{translate('Hvem tar du med deg? Vet vi mer er sjansen for at vi finner en god match større. Eksempelvis alder på barna.')}</label>
                    <textarea className="mdl-textfield__input" type="text" id="bringing" ref={(c) => this.form.bringing = c} rows="3" ></textarea>
                </div>

            </div>);
    }
    renderContactInfoSection(translate) {
        return (
            <div>
                <h3>{translate('Hvordan kan vi kontakte deg')}?</h3>

                     <div className="mdl-cell mdl-cell--4-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                        <input className="mdl-textfield__input" type="text" id="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"  ref={(c) => this.form.email = c} required />
                        <label className="mdl-textfield__label" htmlFor="email">{translate('Hva er e-postadressen din')}?*</label>
                        <span className="mdl-textfield__error">This is not a valid email</span>
                    </div>

                     <div className="mdl-cell mdl-cell--4-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                        <input className="mdl-textfield__input" type="text" id="phone" pattern="[0-9]{10}"  ref={(c) => this.form.phone = c}  required />
                        <label className="mdl-textfield__label" htmlFor="phone">{translate('Hva er telefonnummeret ditt')}?*</label>
                        <span className="mdl-textfield__error">This is not a valid phone number</span>
                    </div>

                    <h3>{translate('Hvor bor du')}?</h3>

                     <div className="mdl-cell mdl-cell--4-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                        <input className="mdl-textfield__input" type="text" id="address"  ref={(c) => this.form.address = c} required />
                        <label className="mdl-textfield__label" htmlFor="address">{translate('Adresse')}*</label>
                        <span className="mdl-textfield__error">This is not a valid address</span>
                    </div>
                     <div className="mdl-cell mdl-cell--4-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                        <input className="mdl-textfield__input" type="text" id="zipcode" pattern="[0-9]{4}" ref={(c) => this.form.zipcode = c} required />
                        <label className="mdl-textfield__label" htmlFor="zipcode">{translate('Postnummer')}*</label>
                        <span className="mdl-textfield__error">This is not a valid zipcode</span>
                    </div>
            </div>);
    }

    renderOthersSection(translate) {
        return (
            <div>
                <h3>{translate('Annet')}?</h3>
                {this.renderFoodConcerns()}
                <div className="mdl-cell mdl-cell--12-col mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield--clean">
                    <label className="mdl-textfield__label" htmlFor="freetext">{translate('Er det noe annet vi trenger å vite om deg/dere')}?</label>
                    <textarea className="mdl-textfield__input" type="text" id="freetext" ref={(c) => this.form.freetext = c} rows="3" ></textarea>
                </div>
            </div>);
    }

    render() {
        const translate = this.context.translate;
        const { type, pending, success } = this.state;

        if (pending) {
            return this.renderPending();
        }

        if (success) {
            return this.renderSuccess();
        }

        let typeForm, intro;

        intro = (<div className="mdl-cell mdl-cell--12-col">
            <h1>Kom inn</h1>
            <p>{translate('Mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk')}.</p>
            <p>{translate('Hvis du vil komme på middag, eller invitere noen på middag registrerer du deg nedenfor. Vi matcher dere basert på hvem dere er og hvor dere bor og setter dere i kontakt for å avtale tidspunkt.')}</p>
            </div>
        )
        if (type == 'guest') {
            intro = (<div className="mdl-cell mdl-cell--12-col">
                <p>{translate("Vil du øve på å snakke norsk? Ideen bak Kom inn er at mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk.")}</p>
                <p>{translate("Registrer deg nedenfor dersom du vil komme på middag. For å koble dere trenger vi å vite litt om hvem dere er og hvor dere bor. Når vi finner en match ber vi verten ta kontakt for å avtale tidspunkt.")}</p>
                </div>
            )
        } else if (type == 'host') {
            intro = ( <div className="mdl-cell mdl-cell--12-col">
                <p>{translate("Vil du hjelpe noen å lære norsk? Ideen bak Kom inn er at mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk.")}</p>
                <p>{translate("Registrer deg nedenfor dersom du vil invitere noen på middag. For å koble dere trenger vi å vite litt om hvem dere er og hvor dere bor. Når vi finner en match setter vi dere i kontakt for å avtale tidspunkt.")}</p>
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

            <div className="mdl-grid">
                <div className="mdl-cell mdl-cell--12-col mdl-card mdl-shadow--2dp">
                  <div className="mdl-card__title">
                    <h2 className="mdl-card__title-text">{translate('Takk skal du ha')}</h2>
                  </div>
                  <div className="mdl-card__supporting-text">
                    {intro}

                    {typeForm}

                    {this.renderAboutYouSection(translate)}

                    {this.renderYourGuestsSection(translate)}

                    {this.renderContactInfoSection(translate)}

                    {this.renderOthersSection(translate)}

                    {this.renderError()}
                  </div>
                  <div className="mdl-card__actions mdl-card--border">
                    <a onClick={this.submit} className="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
                      {translate('Send inn')}
                    </a>
                  </div>
                </div>
            </div>
        )
    }
}

Register.contextTypes = {
    translate: React.PropTypes.func
}
