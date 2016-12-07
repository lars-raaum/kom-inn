import React from 'react'
import { Link } from 'react-router'

export default class ThankYou extends React.Component {
    renderHost() {
        return (
            <div>
                <h1>Takk {name}</h1>
                <p>Vi har sendt den en bekreftelse på epost.</p>
                <h3>Hva skjer nå?</h3>
                <p>##PLACEHOLDER##</p>
                <h3>Har du andre spørsmål?</h3>
                <p>Les mer på kom-inn.org eller send en epost til <a href="mailto:kominnoslo@gmail.com.">kominnoslo@gmail.com.</a></p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        )
    }

    renderGuest() {
        return (
            <div>
                <h1>Takk {name}</h1>
                <p>Vi har sendt den en bekreftelse på epost.</p>
                <h3>Hva skjer nå?</h3>
                <p>Vi vil prøve å finne noen som kan invitere deg på middag så snart
                som mulig. Av og til tar det bare noen dager, andre ganger tar det lenger
                tid. Vanligvis finner vi noen innen 2 uker. </p>
                <h3>Har du andre spørsmål?</h3>
                <p>Les mer på kom-inn.org eller send en epost til <a href="mailto:kominnoslo@gmail.com.">kominnoslo@gmail.com.</a></p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        )
    }

    render() {
        const type = this.props.params.type;

        if (type === 'vert') {
            return this.renderHost();
        } else if (type === 'gjest') {
            return this.renderGuest();
        }

        return (
            <div>
                <h1>Takk {name}</h1>
                <p>Vi har sendt den en bekreftelse på epost.</p>
                <h3>Har du spørsmål?</h3>
                <p>Les mer på kom-inn.org eller send en epost til <a href="mailto:kominnoslo@gmail.com.">kominnoslo@gmail.com.</a></p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        )
    }
}