import { ui, match } from '../action-types';

const defaultState = {
    region: 'oslo',
    lastMatchId: null,
    selectedGuestId: null,
    selectedHostId: null
};

function uiReducer(state = defaultState, action = {}) {
    switch (action.type) {
        case ui.SET_REGION:
            return Object.assign({}, state, {
                region: action.payload,
                selectedGuestId: null,
                selectedHostId: null
            });
        case ui.SELECT_GUEST:
            return Object.assign({}, state, {
                selectedGuestId: action.payload,
                selectedHostId: null
            });
        case ui.SELECT_HOST:
            return Object.assign({}, state, {
                selectedHostId: action.payload
            });
        case match.MATCH_SUCCESS:
            return Object.assign({}, state, {
                lastMatchId: action.payload.id,
                selectedGuestId: null,
                selectedHostId: null
            });
        default:
            return state;
    }
}

export default uiReducer;
