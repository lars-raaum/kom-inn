import React from 'react'
import ReactDOM from 'react-dom'
import { Router, Route, IndexRoute, browserHistory } from 'react-router'

import App from 'components/app';
import MainPage from 'components/main-page';
import Register from 'components/register';
import NotFound from 'components/not-found';

// Declarative route configuration (could also load this config lazily
// instead, all you really need is a single root route, you don't need to
// colocate the entire config).
ReactDOM.render((
    <Router history={browserHistory}>
        <Route path="/" component={App}>
            <IndexRoute component={MainPage} />
            <Route path="/register" component={Register}/>
            <Route path="/register/guest" component={Register}/>
            <Route path="/register/host" component={Register}/>
            <Route path="*" component={NotFound}/>
        </Route>
  </Router>
), document.getElementById('app'))