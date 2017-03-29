import { guests } from '../action-types';
import createAsyncAction from '../utils/create-async-action';
import fetch from '../utils/fetch';

const actions = {
    pending: guests.FETCH_GUESTS_PENDING,
    success: guests.FETCH_GUESTS_SUCCESS,
    error: guests.FETCH_GUESTS_ERROR
};

function fetchGuests() {
    return (dispatch, getState) => {
        const region = getState().ui.region
        const fetchData = () => fetch(`/api/guests?region=${region}`);

        return createAsyncAction(dispatch, actions, fetchData, { region });
    };
}

export { fetchGuests };
