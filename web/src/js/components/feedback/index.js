import React from 'react'
import { Link } from 'react-router'

export default class Feedback extends React.Component {
    constructor() {
        super()
        this.form = {};

        this.state = { gender: null, type: null, error: null, success: false };

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

        return <div className="form-group">
            <div className="input-field col-1-1">
                <label className="input-header" htmlFor="food_concerns">{this.context.translate('Er det noe mat du ikke spiser?')}:</label><br />
                <input type="text" placeholder="Fyll inn" id="food_concerns" ref={(c) => this.form.food_concerns = c} required />
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

        fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.getFormData())
        }).then(() => {
            this.setState({ success: true });
        }).catch(err => {
            this.setState({ error: err.message ? err.message : err });
        });
    }

    render() {
        const translate = this.context.translate;
        return (
            <div className="main-page">
                <h1>Kom inn</h1>
                <p>{translate('Mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk.')}</p>
                <p>{translate('Hvis du vil komme på middag, eller invitere noen på middag registrerer du deg nedenfor. Vi matcher dere basert på hvem dere er og hvor dere bor og setter dere i kontakt for å avtale tidspunkt.')}</p>

                <p>{translate('Du finner mer informasjon på')} <a href="http://www.kom-inn.org">www.kom-inn.org.</a></p>
                <form onSubmit={this.submit}>
                    <h2>{translate('Hva vil du?')}</h2>
                    <div className="form-group">
                        <div className="radio-field">
                            <label htmlFor="type-guest"><input type="radio" name="type" id="type-guest" onChange={() => this.setState({type: 'guest' })} required /> {translate('Komme til noen på middagsbesøk?')}</label> <br />
                            <label htmlFor="type-host"><input type="radio" name="type" id="type-host" onChange={() => this.setState({type: 'host' })} required /> {translate('Invitere noen på middagsbesøk?')}</label>
                        </div>
                    </div>

                    <h2>Hvem er du?</h2>
                    <div className="form-group">
                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="name">{translate('Hva er navnet ditt?')}</label>
                            <input type="text" placeholder="Fyll inn" id="name" ref={(c) => this.form.name = c} required />
                        </div>

                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="age">{translate('Alder')}:</label>
                            <input type="number" placeholder="Fyll inn et tall" max="120" id="age" ref={(c) => this.form.age = c} required />
                        </div>

                        <div className="radio-field col-1-3">
                            <label className="input-header">{translate('Kjønn')}</label>
                            <label htmlFor="gender-male"><input type="radio" name="gender" id="gender-male" onChange={() => this.setState({gender: 'male' })} />  {translate('Mann')}</label>
                            <label htmlFor="gender-female"><input type="radio" name="gender" id="gender-female" onChange={() => this.setState({gender: 'female' })} />  {translate('Kvinne')}</label>
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="email">{translate('Hva er e-postadressen din?')}</label>
                            <input type="email" placeholder="Fyll inn" id="email" ref={(c) => this.form.email = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="phone">{translate('Hva er telefonnummeret ditt?')}</label>
                            <input type="phone" placeholder="Fyll inn" id="phone" ref={(c) => this.form.phone = c} required />
                        </div>
                    </div>

                    <h2>Hvor mange er dere?</h2>
                    <div className="form-group">
                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="adults_female">{translate('Kvinner')}</label>
                            <input type="number" placeholder="Fyll inn et tall" max="100" id="adults_female" ref={(c) => this.form.adults_female = c} required />
                        </div>

                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="adults_male">{translate('Menn')}</label>
                            <input type="number" placeholder="Fyll inn et tall" max="100" id="adults_male" ref={(c) => this.form.adults_male = c} required />
                        </div>

                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="children">{translate('Barn')} (0-18 {translate('År').toLowerCase()})</label>
                            <input type="number" placeholder="Fyll inn et tall" max="100" id="children" ref={(c) => this.form.children = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="origin">{translate('Hvor er du fra')}?</label>
                            <input type="text" placeholder="Fyll inn" id="origin" ref={(c) => this.form.origin = c} required />
                        </div>
                    </div>

                    {this.renderFoodConcerns()}

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="address">{translate('Adresse')}?</label>
                            <input type="text" placeholder="Fyll inn" id="address" ref={(c) => this.form.address = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="zipcode">{translate('Postnummer')}?</label>
                            <input type="text" placeholder="Fyll inn" id="zipcode" ref={(c) => this.form.zipcode = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1 no-height">
                            <label className="input-header" htmlFor="freetext">{translate('Kan du fortelle litt mer om deg selv?')}</label>
                            <textarea id="freetext" ref={(c) => this.form.freetext = c} required></textarea>
                        </div>
                    </div>

                    <div className="submit">
                        <button type="submit">{translate('Send inn')}</button>
                    </div>

                    {this.renderError()}
                </form>
            </div>
        )
    }
}

Feedback.contextTypes = {
    translate: React.PropTypes.func
}
