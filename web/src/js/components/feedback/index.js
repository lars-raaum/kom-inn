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

        return <div className="input-field">
            <label htmlFor="food_concerns">{this.context.translate('Er det noe mat du ikke spiser?')}:</label><br />
            <input type="text" placeholder="Fyll inn" name="food_concerns" ref={(c) => this.form.food_concerns = c} required />
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
                    <div className="radio-field">
                        <label>{translate('Hva vil du?')}</label> <br />
                        <span><input type="radio" name="type" onChange={() => this.setState({type: 'guest' })} required /> {translate('Komme til noen på middagsbesøk?')}</span> <br />
                        <span><input type="radio" name="type" onChange={() => this.setState({type: 'host' })} required /> {translate('Invitere noen på middagsbesøk?')}</span>
                    </div>
                    <div className="input-field">
                        <label htmlFor="name">{translate('Hva er navnet ditt?')}</label> <br />
                        <input type="text" placeholder="Fyll inn" name="name" ref={(c) => this.form.name = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="email">{translate('Hva er e-postadressen din?')}</label> <br />
                        <input type="email" placeholder="Fyll inn" name="email" ref={(c) => this.form.email = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="phone">{translate('Hva er telefonnummeret ditt?')}</label> <br />
                        <input type="phone" placeholder="Fyll inn" name="phone" ref={(c) => this.form.phone = c} required />
                    </div>

                    <div className="radio-field">
                        <label htmlFor="phone">{translate('Er du')}</label> <br />
                        <span><input type="radio" name="gender" onChange={() => this.setState({gender: 'male' })} />  {translate('Mann')}</span> <br />
                        <span><input type="radio" name="gender" onChange={() => this.setState({gender: 'female' })} />  {translate('Kvinne')}</span>
                    </div>

                    <div className="input-field">
                        <label htmlFor="age">{translate('Alder')}:</label><br />
                        <input type="number" placeholder="Fyll inn et tall" max="120" name="age" ref={(c) => this.form.age = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="children">{translate('Antall barn')}:</label><br />
                        <input type="number" placeholder="Fyll inn et tall" max="100" name="children" ref={(c) => this.form.children = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="adults_male">{translate('Antall voksne menn')}:</label><br />
                        <input type="number" placeholder="Fyll inn et tall" max="100" name="adults_male" ref={(c) => this.form.adults_male = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="adults_female">{translate('Antall voksne kvinner')}:</label><br />
                        <input type="number" placeholder="Fyll inn et tall" max="100" name="adults_female" ref={(c) => this.form.adults_female = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="origin">{translate('Hvor er du fra')}:</label><br />
                        <input type="text" placeholder="Fyll inn" name="origin" ref={(c) => this.form.origin = c} required />
                    </div>

                    {this.renderFoodConcerns()}

                    <div className="input-field">
                        <label htmlFor="address">{translate('Adresse')}:</label><br />
                        <input type="text" placeholder="Fyll inn" name="address" ref={(c) => this.form.address = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="zipcode">{translate('Postnummer')}:</label><br />
                        <input type="text" placeholder="Fyll inn" name="zipcode" ref={(c) => this.form.zipcode = c} required />
                    </div>

                    <div className="input-field">
                        <label htmlFor="freetext">{translate('Kan du fortelle litt mer om deg selv?')}</label><br />
                        <textarea name="freetext" ref={(c) => this.form.freetext = c} required></textarea>
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
