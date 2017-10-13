import React from 'react'
import { Link } from 'react-router'

export default class ThankYou extends React.Component {
    renderHost() {
        return (
            <div>
                <h1>Takk {name}</h1>
                <h3>Hva skjer nå?</h3>
                <p>Vi vil prøve å finne noen du kan invitere på middag så fort så mulig.
                Når vi har funnet passende gjester - hører du fra oss!</p>
                <p>Noen ganger går det noen dager, andre ganger bruker vi flere uker.
                Dette kan variere veldig. Vi forsøker så godt vi kan å finne rett match
                så opplevelsen for begge parter skal bli best mulig.
                Hvis du synes det går for lang tid er det lov å sende oss en mail.</p>
                <p>For deg som bor utenfor Oslo : <i>Kom inn</i> er enda ikke etablert over hele landet.
                Vi håper å få til dette i løpet av ikke så lang tid.
                Vi tar kontakt med deg når vi har gjester i ditt området!  </p>
                <h3>Har du andre spørsmål?</h3>
                <p>Les mer på <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a> eller send en epost til <a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a>.</p>
                <p className="fb-share-button" data-href="https://www.facebook.com/" data-layout="button" data-size="small" data-mobile-iframe="false"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwww.facebook.com%2F&amp;src=sdkpreparse">{this.context.translate('Del på Facebook')}</a></p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        )
    }

    renderGuest() {
        return (
            <div>
                <h1>Takk {name}</h1>
                <h3>Hva skjer nå?</h3>
                <p>Vi vil prøve å finne noen som kan invitere deg på middag så snart
                som mulig. Av og til tar det noen dager, andre ganger tar det lenger
                tid. Vanligvis finner vi noen innen 2 uker. </p>
                <h3>Har du andre spørsmål?</h3>
                <p>Les mer på <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a> eller send en epost til <a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a>.</p>
                <p className="fb-share-button" data-href="https://www.facebook.com/" data-layout="button" data-size="small" data-mobile-iframe="false"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwww.facebook.com%2F&amp;src=sdkpreparse">{this.context.translate('Del på Facebook')}</a></p>
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
                <h3>Har du spørsmål?</h3>
                <p>Les mer på <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a> eller send en epost til <a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a>.</p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        )
    }
}
ThankYou.contextTypes = {
    translate: React.PropTypes.func
}