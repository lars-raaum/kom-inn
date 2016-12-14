import React from 'react'
import { Link } from 'react-router'

export default class ThankYou extends React.Component {
    renderHost() {
        return (
            <div>
                <h1>Takk {name}</h1>
                <p>Vi har sendt deg en bekreftelse på epost.</p>
                <h3>Hva skjer nå?</h3>
                <p>Vi vil prøve å finne noen du kan invitere på middag så fort så mulig.
                Når vi har funnet passende gjester - hører dere fra oss!</p>
                <p>Noen ganger går det noen dager, andre ganger bruker vi mange uker.
                Dette kan variere veldig. Vi forsøker så godt vi kan å finne rett match
                så opplevelsen for begge parter skal bli best mulig.
                Om du synes det går for lang tid er det lov å sende oss en mail.</p>
                <p>For deg som bor utenfor Oslo : Kom inn er enda ikke etablert over hele landet.
                Vi håper å få til dette i løpet av ikke så lang tid.
                Vi tar kontakt med deg når vi har gjester i ditt området!  </p>
                <h3>Har du andre spørsmål?</h3>
                <p>Les mer på <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a>
                eller send en epost til <a href="mailto:kominnoslo@gmail.com.">kominnoslo@gmail.com.</a></p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        )
    }

    renderGuest() {
        return (
            <div>
                <h1>Takk {name}</h1>
                <p>Vi har sendt deg en bekreftelse på epost.</p>
                <h3>Hva skjer nå?</h3>
                <p>Vi vil prøve å finne noen som kan invitere deg på middag så snart
                som mulig. Av og til tar det bare noen dager, andre ganger tar det lenger
                tid. Vanligvis finner vi noen innen 2 uker. </p>
                <h3>Har du andre spørsmål?</h3>
                <p>Les mer på <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a>
                eller send en epost til
                <a href="mailto:kominnoslo@gmail.com.">kominnoslo@gmail.com.</a></p>
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
                <p>Vi har sendt deg en bekreftelse på epost.</p>
                <h3>Har du spørsmål?</h3>
                <p>Les mer på <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a>
                 eller send en epost til
                 <a href="mailto:kominnoslo@gmail.com.">kominnoslo@gmail.com.</a></p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        )
    }
}