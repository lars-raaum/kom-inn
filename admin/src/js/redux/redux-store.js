/* eslint-disable no-underscore-dangle */
import { createStore, combineReducers, compose, applyMiddleware } from 'redux';
import { routerReducer, routerMiddleware } from 'react-router-redux';
import { browserHistory, createMemoryHistory } from 'react-router';
import thunk from 'redux-thunk';

import * as reducers from './reducers';

const isDev = process.env.NODE_ENV === 'development';

const createRootReducer = () => combineReducers({
    ...reducers,
    routing: routerReducer
});

const reducer = createRootReducer();

let initialState = {};
if (process.browser && window.__REDUX_STATE__) {
    initialState = window.__REDUX_STATE__;
}

// Sync dispatched route actions to the history
const historyMiddleware = routerMiddleware(process.browser ? browserHistory : createMemoryHistory());
const middleware = applyMiddleware(historyMiddleware, thunk);

let composeEnhancers = compose;
// for redux devtools browser plugin
if (process.browser && isDev) {
    composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
}

const storeEnhancers = composeEnhancers(middleware);

const store = createStore(reducer, initialState, storeEnhancers);

export default store;
