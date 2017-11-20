import React from 'react';
import Person from '../common/person';
import {fetchPerson} from "../../actions/person";

export default class People extends React.Component {
    constructor(props) {
        super();
        this.form = {};
        this.state = {
            loading: true,
            person: {},
            gender: null,
            error: null,
            success: false,
            pending: false
        };
        this.fetchPerson = this.fetchPerson.bind(this);
        this.updatePerson = this.updatePerson.bind(this);
        this.submit = this.submit.bind(this);
    }

    getFormData() {
        return {
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
    }

    fetchPerson() {
        this.setState({ loading: true });
        return fetchPerson({ id: this.props.params.id }).then(({ response, headers }) => {
            console.log(response);
            this.setState({
                loading: false,
                person: response,
                gender: response.gender
            });
            this.form.name.value = this.state.person.name;
            this.form.email.value = this.state.person.email;
            this.form.phone.value = this.state.person.phone;
            this.form.age.value = this.state.person.age;
            this.form.children.value = this.state.person.children;
            this.form.adults_male.value = this.state.person.adults_m;
            this.form.adults_female.value = this.state.person.adults_f;
            this.form.bringing.value = this.state.person.bringing;
            this.form.origin.value = this.state.person.origin;
            this.form.address.value = this.state.person.address;
            this.form.zipcode.value = this.state.person.zipcode;
            this.form.freetext.value = this.state.person.freetext;
            if (this.state.person.type === 'GUEST') {
                this.form.food_concerns.value = this.state.person.food_concerns || '';
            }
        });
    }

    updatePerson() {

    }

    submit(e) {
        e.preventDefault();
        this.setState({pending: true});
        const data = this.getFormData();
        console.log(data);
    }

    componentDidMount() {
        this.fetchPerson()
    }
    render() {
        if (this.state.loading) {
            return <div className="loading-gif">
                <span>LOADING</span>
            </div>;
        }

        let fc = '';
        if (this.state.person.type === 'GUEST') {
            fc = <div className="form-group">
                <div className="input-field col-1-1 no-height">
                    <label className="input-header" htmlFor="food_concerns">Food concerns</label><br />
                    <input type="text" id="food_concerns" ref={(c) => this.form.food_concerns = c} />
                </div>
            </div>;
        }

        return <div>
            <div className="edit-person people">
                <h2>{this.state.person.name}</h2>
                <form onSubmit={this.submit}>

                    <div className="form-group">
                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="name">Name</label>
                            <input type="text" id="name" ref={(input) => this.form.name = input} required />
                        </div>

                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="age">Age</label>
                            <input type="number" max="120" id="age" ref={(c) => this.form.age = c} required />
                        </div>

                        <div className="radio-field col-1-3">
                            <label className="input-header">Gender</label>
                            <label htmlFor="gender-male"><input type="radio" name="gender" id="gender-male" onChange={() => this.setState({gender: 'male' })} checked={this.state.gender === "male"} />  Male</label>
                            <label htmlFor="gender-female"><input type="radio" name="gender" id="gender-female" onChange={() => this.setState({gender: 'female' })} checked={this.state.gender === "female"} />  Female</label>
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="origin">Origin</label>
                            <input type="text" id="origin" ref={(c) => this.form.origin = c} required />
                        </div>
                    </div>

                    <h2>Bringing?</h2>
                    <div className="form-group">
                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="adults_female">Women</label>
                            <input type="number" max="100" id="adults_female" ref={(c) => this.form.adults_female = c} required />
                        </div>

                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="adults_male">Men</label>
                            <input type="number" max="100" id="adults_male" ref={(c) => this.form.adults_male = c} required />
                        </div>

                        <div className="input-field col-1-3">
                            <label className="input-header" htmlFor="children">Children under 18</label>
                            <input type="number" max="100" id="children" ref={(c) => this.form.children = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1 no-height">
                            <label className="input-header" htmlFor="bringing">Description of bringing</label>
                            <textarea id="bringing" ref={(c) => this.form.bringing = c}></textarea>
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="email">Email</label>
                            <input type="email" id="email" ref={(c) => this.form.email = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="phone">Phone number</label>
                            <input type="phone" id="phone" ref={(c) => this.form.phone = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="address">Address</label>
                            <input type="text" id="address" ref={(c) => this.form.address = c} required />
                        </div>
                    </div>

                    <div className="form-group">
                        <div className="input-field col-1-1">
                            <label className="input-header" htmlFor="zipcode">Post Code</label>
                            <input type="text" id="zipcode" ref={(c) => this.form.zipcode = c} required />
                        </div>
                    </div>

                    {fc}

                    <div className="form-group">
                        <div className="input-field col-1-1 no-height">
                            <label className="input-header" htmlFor="freetext">Other</label>
                            <textarea id="freetext" ref={(c) => this.form.freetext = c} ></textarea>
                        </div>
                    </div>

                    <div className="submit">
                        <button type="submit">Save</button>
                    </div>

                </form>
            </div>
        </div>
    }
}
