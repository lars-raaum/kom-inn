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

    render() {
        return (
            <div className="main-page">
                <h1> Beklager, vi er ikke tilgjengelig for øyeblikket. Kom tilbake litt seinere</h1>
            </div>
        )
    }
}

Register.contextTypes = {
    translate: React.PropTypes.func
}
