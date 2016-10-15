import React from 'react'
import { Link } from 'react-router'

export default class MainPage extends React.Component {
    render() {
        return (
            <div className="main-page">
                <h1>Kom inn.</h1>
                <p>Mennesker som snakker norsk inviterer noen som lærer seg norsk på middagsbesøk.</p>
                <p>Hvis du vil komme på middag, eller invitere noen på middag registrerer du deg nedenfor. Vi matcher dere basert på hvem dere er og hvor dere bor og setter dere i kontakt for å avtale tidspunkt.</p>
                <p>Du finner mer informasjon på <a href="http://www.kom-inn.org">www.kom-inn.org</a></p>
            </div>
        )
    }
}