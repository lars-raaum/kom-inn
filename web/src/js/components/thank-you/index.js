import React from 'react';
import { Link } from 'react-router';

import translate from 'translate';

export default class ThankYou extends React.Component {
    renderHost() {
        return (
            <div>
                <h2>{translate('Takk for at du har meldt deg på!')}</h2>
                <br />
                <h3>{translate('Hva skjer nå?')}</h3>
                <p>
                    {translate(
                        'Vi vil prøve å finne noen du kan invitere på middag så fort så mulig. Når vi har funnet passende gjester - hører du fra oss!'
                    )}
                </p>
                <p>
                    {translate(
                        'Noen ganger går det noen dager, andre ganger bruker vi flere uker. Dette kan variere veldig. Vi forsøker så godt vi kan å finne rett match så opplevelsen for begge parter skal bli best mulig. Hvis du synes det går for lang tid er det lov å sende oss en mail.'
                    )}
                </p>
                <p>
                    {translate(
                        'For deg som bor utenfor Oslo : Kom inn er enda ikke etablert over hele landet. Vi håper å få til dette i løpet av ikke så lang tid. Vi tar kontakt med deg når vi har gjester i ditt området!'
                    )}
                </p>
                <br /><br />
                <h3>{translate('Har du andre spørsmål?')}</h3>
                <p>
                    {translate('Les mer på')}{' '}
                    <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a>{' '}
                    {translate('eller send en epost til')}{' '}
                    <a href="mailto:kominnoslo@gmail.com">
                        kominnoslo@gmail.com
                    </a>.
                </p>
                <p>{translate('Ha en fortsatt fin dag!')}</p>
                <p>{translate('Hilsen oss i Kom inn.')}</p>
            </div>
        );
    }

    renderGuest() {
        return (
            <div className="nextSteps">
                <h2>{translate('Takk for at du har meldt deg på!')}</h2>
                <br />
                <h3>{translate('Hva skjer nå?')}</h3>
                <p>
                    {translate(
                        'Vi vil nå matche deg med noen som ønsker å invitere deg, vanligvis innen to uker.'
                    )}
                    {' '}
                    {translate(
                        'Du vil da motta en invitasjon via SMS fra verten.'
                    )}
                    {' '}
                    {translate(
                        'Sammen blir dere enige om når du kommer til huset til middag.'
                    )}
                </p>
                <br />
                <h3>{translate('For flere detaljer')}</h3>
                <p>
                    {translate('Les mer på')}{' '}
                    <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a>{' '}
                    {translate('eller send en epost til')}{' '}
                    <a href="mailto:kominnoslo@gmail.com">
                        kominnoslo@gmail.com
                    </a>.
                </p>
                <p>{translate('Ha en fortsatt fin dag!')}</p>
            </div>
        );
    }

    render() {
        const type = this.props.type || this.props.params.type;

        if (type === 'vert') {
            return this.renderHost();
        } else if (type === 'gjest') {
            return this.renderGuest();
        }

        return (
            <div>
                <h3>Har du spørsmål?</h3>
                <p>
                    Les mer på{' '}
                    <a href="http://www.kom-inn.org/#hjem">kom-inn.org</a> eller
                    send en epost til{' '}
                    <a href="mailto:kominnoslo@gmail.com">
                        kominnoslo@gmail.com
                    </a>.
                </p>
                <p>Ha en fortsatt fin dag!</p>
                <p>Hilsen oss i Kom inn.</p>
            </div>
        );
    }
}
