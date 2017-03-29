import { guests } from '../action-types';

const defaultState = {
    error: null,
    pending: false,
    items: []
};

function guestsReducer(state = defaultState, action = {}) {
    switch (action.type) {
        case guests.FETCH_GUESTS_PENDING:
            return Object.assign({}, defaultState, {
                pending: true
            });
        case guests.FETCH_GUESTS_FAILURE:
            return Object.assign({}, defaultState, {
                error: action.payload
            });
        case guests.FETCH_GUESTS_SUCCESS:
            if (!action.payload || !action.payload.length) {
                return state;
            }

            return Object.assign({}, defaultState, {
                items: action.payload
            });
        default:
            return state;
    }
}

export default guestsReducer;
