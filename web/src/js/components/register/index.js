import React from 'react'
import { Link } from 'react-router'

const MAX_PEOPLE = 9;

export default class Register extends React.Component {
    constructor() {
        super()
        this.form = {};

        this.state = { gender: null, type: null, error: null, success: false, pending: false, peoples: { adults_male: 0, adults_female: 0, children: 0 } };

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

        if (this.form.childrenAge && this.form.childrenAge.value) {
            data.bringing += `\n Alder på barn: ${this.form.childrenAge.value}`
        }

        return data;
    }

    renderFoodConcerns() {
        if (this.state.type !== 'guest') {
            return null;
        }

        return <div className="form-group">
            <div className="input-field col-1-1 no-height">
                <label className="input-header" htmlFor="food_concerns">{this.context.translate('Er det noe mat du/dere ikke spiser')}?</label><br />
                <input type="text" placeholder="Fyll inn" id="food_concerns" ref={(c) => this.form.food_concerns = c} />
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

    addPeople(type) {
        this.setState({
            peoples: Object.assign(this.state.peoples, { [type]: Math.min(MAX_PEOPLE, this.state.peoples[type] + 1) })
        });
    }

    removePeople(type) {
        this.setState({
            peoples: Object.assign(this.state.peoples, { [type]: Math.max(0, this.state.peoples[type] - 1) })
        });
    }

    getPeopleValue(type) {
        let nth = this.state.peoples[type];
        if (document.forms.register && document.forms.register.gender) {
            const { value } = document.forms.register.gender;
            if ((value == 'female' && type == 'adults_female') || (value == 'male' && type == 'adults_male')) {
                nth += 1;
            }
        }

        return nth;
    }

    renderPeopleIcons(type) {
        const icons = (new Array(this.state.peoples[type])).fill().map((_, i) => <span key={i} className={`icon icon-${type}`}></span>)

        if (document.forms.register && document.forms.register.gender) {
            const { value } = document.forms.register.gender;
            if (value == 'female' && type == 'adults_female') {
                icons.unshift(<span key="you" className={`icon icon-adults_female`}></span>)
            } else if (value == 'male' && type == 'adults_male') {
                icons.unshift(<span key="you" className={`icon icon-adults_male`}></span>)
            } else {
                if (this.state.peoples[type] == 0) {
                    if (type == 'adults_female') {
                        icons.unshift(<span key="you" className={`icon icon_female_outline`}></span>)
                    } else if (type == 'adults_male') {
                        icons.unshift(<span key="you" className={`icon icon_male_outline`}></span>)
                    } else if (type == 'children') {
                        icons.unshift(<span key="you" className={`icon icon_children_outline`}></span>)
                    }
                }
            }
        } else {
            if (this.state.peoples[type] == 0) {
                if (type == 'adults_female') {
                    icons.unshift(<span key="you" className={`icon icon_female_outline`}></span>)
                } else if (type == 'adults_male') {
                    icons.unshift(<span key="you" className={`icon icon_male_outline`}></span>)
                } else if (type == 'children') {
                    icons.unshift(<span key="you" className={`icon icon_children_outline`}></span>)
                }
            }
        }
        icons.unshift(<span key="add-one" className="add-one" onClick={() => this.addPeople(type)}>+</span>)
        icons.unshift(<span key="remove-one" className="remove-one" onClick={() => this.removePeople(type)}>−</span>)
        return <div className="people-icons">{icons}</div>
    }

    renderChildrenAgeInput() {
        if (this.state.peoples.children === 0) {
            return null;
        }

        const { translate } = this.context;

        return <div className="input-field col-1-1 no-height">
            <label className="input-header" htmlFor="childrenAge">{translate('Hvor gamle er barna?')}</label>
            <input id="childrenAge" placeholder="Fyll inn" ref={(c) => this.form.childrenAge = c} required />
        </div>
    }

    render() {
        const { translate } = this.context;

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
                <p>{translate("Registrer deg nedenfor dersom du vil komme på middag.")}</p>
                <p>{translate("Vil du heller invitere noen på middag,")} <a href="/som/vert">{translate("gå til skjema for vert.")}</a></p>
                <p>{translate("Gjestene er noen som har nylig kommet til Norge, og de fleste melder seg på etter at Kom inn har blitt presentert på Voksenopplæringene i Oslo. Vertene er noen som snakker norsk flytende. Alle som deltar har meldt seg på via denne nettsiden. Våre frivillige prøver å sette sammen en hyggeligst mulig middag. Når vi finner en match setter vi dere i kontakt, slik at dere selv kan avtale detaljene.")}</p>
                <p>{translate("Det er ingen forpliktelser ved å melde seg på utover å møtes til én middag. De fleste gjester inviterer likevel tilbake og noen har kontakt i lang tid. Det er helt opp til dere, deltakelse er helt frivillig og på eget ansvar.")}</p>
                <p>{translate("Du kan lese mer på ")}<a href="http://www.kom-inn.org/#eat-together">kom-inn.org</a></p>
                </div>
            )
        } else if (this.props.params.type == 'vert') {
            this.state.type = 'host';
            intro = ( <div>
                <h1>{translate("Jeg vil invitere noen på middag!")}</h1>
                <p>{translate("Vil du hjelpe noen å lære norsk? Ideen bak Kom inn er at mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk.")}</p>
                <p>{translate("Registrer deg nedenfor dersom du vil invitere noen på middag.")}</p>
                <p>{translate("Vil du heller bli invitert på middag,")} <a href="/som/gjest">{translate("gå til skjema for gjest.")}</a></p>
                <p>{translate("Gjestene er noen som har nylig kommet til Norge, og de fleste melder seg på etter at Kom inn har blitt presentert på Voksenopplæringene i Oslo. Vertene er noen som snakker norsk flytende. Alle som deltar har meldt seg på via denne nettsiden. Våre frivillige prøver å sette sammen en hyggeligst mulig middag. Når vi finner en match setter vi dere i kontakt, slik at dere selv kan avtale detaljene.")}</p>
                <p>{translate("Det er ingen forpliktelser ved å melde seg på utover å møtes til én middag. De fleste gjester inviterer likevel tilbake og noen har kontakt i lang tid. Det er helt opp til dere, deltakelse er helt frivillig og på eget ansvar.")}</p>
                <p>{translate("Du kan lese mer på ")}<a href="http://www.kom-inn.org/#eat-together">kom-inn.org</a></p>
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
            <div className="register">
                {intro}
                <form name="register" onSubmit={this.submit}>

                    {typeForm}

                    <h2>{translate('Hvem er du')}?</h2>
                    <div className="form-group">
                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="name">{translate('Hva er navnet ditt')}</label>
                            <input type="text" placeholder="Fyll inn" id="name" ref={(c) => this.form.name = c} required />
                        </div>

                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="age">{translate('Alder')}</label>
                            <input type="number" placeholder="Fyll inn et tall" max="120" id="age" ref={(c) => this.form.age = c} required />
                        </div>

                        <div className="radio-field col-1-3">
                            <label className="input-header">{translate('Kjønn')}</label>
                            <label htmlFor="gender-male"><input type="radio" name="gender" value="male" id="gender-male" onChange={() => this.setState({gender: 'male' })} required />  {translate('Mann')}</label>
                            <label htmlFor="gender-female"><input type="radio" name="gender" value="female" id="gender-female" onChange={() => this.setState({gender: 'female' })} required />  {translate('Kvinne')}</label>
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="origin">{translate('Hvor er du fra')}</label>
                            <input type="text" placeholder="Fyll inn" id="origin" ref={(c) => this.form.origin = c} required />
                        </div>
                    </div>

                    <h2>{translate('Hvor mange blir med på middag')}?</h2>
                    <div className="form-group">
                        <div className="col-1-1 people-select-wrapper">
                            <div className="people-select">
                                <input type="number" placeholder="Fyll inn et tall" readOnly value={this.getPeopleValue('adults_female')} max="9" id="adults_female" ref={(c) => this.form.adults_female = c} required />
                                <label htmlFor="adults_female">{translate('Kvinner')}</label>
                                {this.renderPeopleIcons('adults_female')}
                            </div>
                        </div>

                        <div className="col-1-1 people-select-wrapper">
                            <div className="people-select">
                                <input type="number" placeholder="Fyll inn et tall" readOnly value={this.getPeopleValue('adults_male')} max="9" id="adults_male" ref={(c) => this.form.adults_male = c} required />
                                <label htmlFor="adults_male">{translate('Menn')}</label>
                                {this.renderPeopleIcons('adults_male')}
                            </div>
                        </div>

                        <div className="col-1-1 people-select-wrapper">
                            <div className="people-select">
                                <input type="number" placeholder="Fyll inn et tall" readOnly value={this.getPeopleValue('children')} max="9" id="children" ref={(c) => this.form.children = c} required />
                                <label htmlFor="children">{translate('Barn')} (0-18 {translate('År').toLowerCase()})</label>
                                {this.renderPeopleIcons('children')}
                            </div>
                        </div>

                        {this.renderChildrenAgeInput()}
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1 no-height">
                            <label className="input-header" htmlFor="bringing">{translate('Hvem tar du med deg? Vet vi mer er sjansen for at vi finner en god match større.')}</label>
                            <textarea id="bringing" ref={(c) => this.form.bringing = c}></textarea>
                        </div>
                    </div>

                    <h2>{translate('Hvordan kan vi kontakte deg')}?</h2>
                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="email">{translate('Hva er e-postadressen din')}?</label>
                            <input type="email" placeholder="Fyll inn" id="email" ref={(c) => this.form.email = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="phone">{translate('Hva er telefonnummeret ditt')}?</label>
                            <input type="phone" placeholder="Fyll inn" id="phone" ref={(c) => this.form.phone = c} required />
                        </div>
                    </div>

                    <h2>{translate('Hvor bor du')}?</h2>
                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="address">{translate('Adresse')}</label>
                            <input type="text" placeholder="Fyll inn" id="address" ref={(c) => this.form.address = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="zipcode">{translate('Postnummer')}</label>
                            <input type="text" placeholder="Fyll inn" id="zipcode" ref={(c) => this.form.zipcode = c} required />
                        </div>
                    </div>

                    <h2>{translate('Annet')}?</h2>

                    {this.renderFoodConcerns()}

                    <div className="form-group">
                        <div className="input-field col-1-1 no-height">
                            <label className="input-header" htmlFor="freetext">{translate('Er det noe annet vi trenger å vite om deg/dere')}?</label>
                            <textarea id="freetext" ref={(c) => this.form.freetext = c} ></textarea>
                        </div>
                    </div>

                    <div>
                        <p>{translate("Dette gjør vi med det du registrerer: En av våre frivillige prøver å finne en middagsgjest eller middagsvert som passer for deg, og gir gjestens informasjon videre til verten, slik at dere kan kan prate sammen og avtale en middag. Etter en stund vil du få e-post fra oss hvor vi spør deg om du fortsatt vil være registrert. Vi sletter informasjonen om deg automatisk etter 90 dager, hvis du ikke sier ja til å fortsatt være registrert. Ønsker du å slette opplysningene dine før, kan du alltids sende oss en e-post på") + ' '}
                        <a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a></p>
                        <br />
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

Register.contextTypes = {
    translate: React.PropTypes.func
}
