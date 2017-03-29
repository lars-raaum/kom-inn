import createAsyncAction from '../utils/create-async-action';
import fetch from '../utils/fetch';
import { match } from '../action-types';

const matchActions = {
    pending: match.MATCH_PENDING,
    success: match.MATCH_SUCCESS,
    error: match.MATCH_ERROR
};

function matchGuestWithHost() {
    return (dispatch, getState) => {
        const state = getState();
        const data = {
            guest_id: state.ui.selectedGuestId,
            host_id: state.ui.selectedHostId,
            comment: ''
        };

        const doMatch = () => fetch(`/api/match`, {
            method: 'POST',
            body: JSON.stringify(data)
        });

        return createAsyncAction(dispatch, matchActions, doMatch, data);
    };
}

export { matchGuestWithHost };

