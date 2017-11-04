import React from 'react'
import { Link } from 'react-router'

export default class ThankYou extends React.Component {
    renderHost() {
        return (
            <div>
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
        const translate = this.context.translate;
        return (
            <div className="nextSteps">
                <h3>{translate("Hva skjer nå?")}</h3>
                <ul>
                <li>{translate("Vi vil nå matche deg med noen som ønsker å invitere deg, vanligvis innen to uker.")}</li>
                <li>{translate("Du vil da motta en invitasjon via SMS fra verten")}</li>
                <li>{translate("Sammen er du enige om når du kommer til huset til middag")} </li>
                </ul>
                <h3>{translate("For flere detaljer")}</h3>
                <p>{translate("Les mer på")} <a href='http://www.kom-inn.org/#hjem'>kom-inn.org</a> {translate("eller send en epost til")} <a href='mailto:kominnoslo@gmail.com'>kominnoslo@gmail.com</a>.</p>
                <p className="fb-share-button" data-href="https://www.facebook.com/" data-layout="button" data-size="small" data-mobile-iframe="false"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwww.facebook.com%2F&amp;src=sdkpreparse">{this.context.translate('Del på Facebook')}</a></p>
                <p>{translate("Ha en fortsatt fin dag!")}</p>
            </div>
        )
    }

    getAppropriateContent(type){
        if (type === 'vert') {
            return this.renderHost();
        } else if (type === 'gjest') {
            return this.renderGuest();
        } else {
            return <div>
                    <h1>Takk {name}</h1>
                    <h3>Har du spørsmål?</h3>
                    <p>Les mer på <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a> eller send en epost til <a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a>.</p>
                    <p>Ha en fortsatt fin dag!</p>
                    <p>Hilsen oss i Kom inn.</p>
                </div>;
        }
    }

    render() {
        const type = this.props.params.type;

        return <div className="mdl-grid">
            <div className="mdl-cell mdl-cell--12-col mdl-card mdl-shadow--2dp">
                <div className="mdl-card__title">{this.context.translate("Takk")} {name}</div>
                <div className="mdl-card__supporting-text">
                    {this.getAppropriateContent(type)}
                </div>
            </div>
        </div>
    }
}
ThankYou.contextTypes = {
    translate: React.PropTypes.func
}