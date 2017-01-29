import React from 'react'
import { Link } from 'react-router'

export default class Feedback extends React.Component {
    constructor() {
        super()
        this.reactivate = this.reactivate.bind(this);
    }

    reactivate(e) {
        e.preventDefault();

        var data = {
            id: this.props.params['id'],
            code: this.props.params['code'],
            status: this.props.params['completed'] == 'yes' ? 2 : -1
        };

        // @TODO this should be done on page view, but only once!
        fetch('/api/feedback', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(response => {
            console.log(response);
        }).catch(err => {
            console.error(err);
            this.setState({ error: err.message ? err.message : err });
        });

        // @TODO how to get person id of host

        // Actual action of this page
        var data = {id: this.props.params['id'], code: this.props.params['code']};
        fetch('/api/reactivate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(response => {
            console.log(response);
        }).catch(err => {
            console.error(err);
            this.setState({ error: err.message ? err.message : err });
        });
    }

    render() {
        const type = this.props.params.completed;
        if (type == 'yes') {
            return this.renderYes();
        } else {
            return this.renderNo();
        }
    }

    renderNo() {
        return (
            <div>
                <h1>Det var dumt at middagen ikke ble noe av.</h1>
                <p>Vi håper allikevel at du vil prøve en gang til!</p>
                <p>Hvis du vil melde deg på en gang til, og få en ny gjest når vi har en passende match</p>
                <button className="calltoaction" onClick={this.reactivate}> klikk her</button>
                <p>Spørsmål og tilbakemeldinger kan sendes til <a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a></p>
                <p>Hilsen oss i Kom Inn:)</p>
            </div>
        )
    }

    renderYes() {
        return (
            <div>
                <h1>Så hyggelig at dere har gjennomført en middag!</h1>
                <p>Vi håper dere hadde en fin kveld!</p>
                <p>Dere står selvsagt helt fritt til å opprettholde kontakten med deres middagsgjest - eller
                 ikke. Det er helt opp til hver enkelt vert og gjest. </p>
                <p>Hvis dere ønsker å bli tildelt en ny middagsgjest når vi har en passende match</p>
                <button className="calltoaction" onClick={this.reactivate}> klikk her</button>
                <p>Spørsmål og tilbakemeldinger kan sendes til <a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a></p>
                <p>Hilsen oss i Kom Inn:)</p>
            </div>
        )
    }
}