import React from 'react'
import ReactDOM from 'react-dom'
import { Router, Route, IndexRoute, browserHistory } from 'react-router'

import App from 'components/app';
import Unmatched from 'components/unmatched';
import Matches from 'components/matches';
import NotFound from 'components/not-found';
import UserMap from 'components/usermap';
import People from 'components/people';
import Person from 'components/person';

// Declarative route configuration (could also load this config lazily
// instead, all you really need is a single root route, you don't need to
// colocate the entire config).
ReactDOM.render((
    <Router history={browserHistory}>
        <Route path="/" component={App}>
            <IndexRoute component={Unmatched} />
            <Route path="matches" component={Matches} />
            <Route path="usermap" component={UserMap} />
            <Route path="people" component={People} />
            <Route path="people/:id" component={Person} />
            <Route path="*" component={NotFound}/>
        </Route>
  </Router>
), document.getElementById('app'))