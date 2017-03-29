import { ui } from '../action-types';
import createAction from '../utils/create-action';

function setRegion(region) {
    return dispatch => {
        return dispatch(createAction(ui.SET_REGION, region))
    };
}

function selectGuest(guestId) {
    return dispatch => {
        return dispatch(createAction(ui.SELECT_GUEST, guestId))
    };
}

function selectHost(hostId) {
    return dispatch => {
        return dispatch(createAction(ui.SELECT_HOST, hostId))
    };
}

export { setRegion, selectGuest, selectHost };

