import React from 'react'
import { Link } from 'react-router'

export default class MainPage extends React.Component {
    constructor() {
        super()
        this.form = {};

        this.state = { gender: null, type: null };

        this.submit = this.submit.bind(this);
    }

    getFormData() {
        const data = {
            type: this.state.type,
            name: this.form.name.value,
            email: this.form.email.value,
            gender: this.state.gender,
            age: this.form.age.value,
            children: this.form.children.value,
            adults_m: this.form.adults_male.value,
            adults_f: this.form.adults_female.value,
            origin: this.form.origin.value,
            address: this.form.address.value,
            zipcode: this.form.zipcode.value,
            freetext: this.form.freetext.value
        };

        return data;
    }

    submit(e) {
        e.preventDefault();

        fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.getFormData())
        });
    }

    render() {
        return (
            <div className="main-page">
                <h1>Velkommen.</h1>
                <p>Her komme en intro...</p>
                <form onSubmit={this.submit}>
                    <div className="radio-field">
                        <p>Jeg er en:</p>
                        <label> Gjest <input type="radio" name="type" onChange={() => this.setState({type: 'guest' })} required /> </label>
                        <label> Vert <input type="radio" name="type" onChange={() => this.setState({type: 'host' })} required /> </label>
                    </div>
                    <div className="input-field">
                        <label for="name">Navn:</label>
                        <input type="text" name="name" ref={(c) => this.form.name = c} required />
                    </div>

                    <div className="input-field">
                        <label for="email">E-post:</label>
                        <input type="email" name="email" ref={(c) => this.form.email = c} required />
                    </div>

                    <div className="radio-field">
                        <label> Mann <input type="radio" name="gender" onChange={() => this.setState({gender: 'male' })} /> </label>
                        <label> Kvinne <input type="radio" name="gender" onChange={() => this.setState({gender: 'female' })} /> </label>
                    </div>

                    <div className="input-field">
                        <label for="age">Alder:</label>
                        <input type="number" name="age" ref={(c) => this.form.age = c} required />
                    </div>

                    <div className="input-field">
                        <label for="children">Antall barn:</label>
                        <input type="number" name="children" ref={(c) => this.form.children = c} required />
                    </div>

                    <div className="input-field">
                        <label for="adults_male">Antall voksne menn:</label>
                        <input type="number" name="adults_male" ref={(c) => this.form.adults_male = c} required />
                    </div>

                    <div className="input-field">
                        <label for="adults_female">Antall voksne kvinner:</label>
                        <input type="number" name="adults_female" ref={(c) => this.form.adults_female = c} required />
                    </div>

                    <div className="input-field">
                        <label for="origin">Hvor er du fra:</label>
                        <input type="text" name="origin" ref={(c) => this.form.origin = c} required />
                    </div>

                    <div className="input-field">
                        <label for="address">Adresse:</label>
                        <input type="text" name="address" ref={(c) => this.form.address = c} required />
                    </div>

                    <div className="input-field">
                        <label for="zipcode">Postnummer:</label>
                        <input type="text" name="zipcode" ref={(c) => this.form.zipcode = c} required />
                    </div>

                    <div className="input-field">
                        <label for="freetext">Kan du fortelle litt mer om deg selv?</label>
                        <textarea name="freetext" ref={(c) => this.form.freetext = c} required></textarea>
                    </div>

                    <div className="submit">
                        <button type="submit">Send inn</button>
                    </div>
                </form>
            </div>
        )
    }
}