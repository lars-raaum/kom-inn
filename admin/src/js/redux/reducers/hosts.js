import { hosts, guests } from '../action-types';

const defaultState = {
    error: null,
    pending: false,
    items: []
};

function hostsReducer(state = defaultState, action = {}) {
    switch (action.type) {
        case hosts.FETCH_HOSTS_PENDING:
            return Object.assign({}, defaultState, {
                pending: true
            });
        case hosts.FETCH_HOSTS_FAILURE:
            return Object.assign({}, defaultState, {
                error: action.payload
            });
        case hosts.FETCH_HOSTS_SUCCESS:
            if (!action.payload || !action.payload.length) {
                return state;
            }

            return Object.assign({}, defaultState, {
                items: action.payload
            });
        case guests.FETCH_GUESTS_PENDING:
            return defaultState;
        default:
            return state;
    }
}

export default hostsReducer;
