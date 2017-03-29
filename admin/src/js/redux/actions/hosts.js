import { hosts } from '../action-types';
import createAsyncAction from '../utils/create-async-action';
import fetch from '../utils/fetch';

const actions = {
    pending: hosts.FETCH_HOSTS_PENDING,
    success: hosts.FETCH_HOSTS_SUCCESS,
    error: hosts.FETCH_HOSTS_ERROR
};

function fetchHosts({ filters, distance }) {
    return (dispatch, getState) => {
        const query = Object.keys(filters)
            .filter(key => filters[key])
            .map(key => `${key}=yes`);

        const state = getState();
        if (state.ui.region) {
            query.push(`region=${state.ui.region}`);
        }

        if (state.ui.selectedGuestId) {
            query.push(`guest_id=${state.ui.selectedGuestId}`);
        }

        if (distance) {
            query.push(`distance=${distance}`);
        }

        const fetchData = () => fetch(`/api/hosts?${query.join('&')}`);

        return createAsyncAction(dispatch, actions, fetchData, { filters, distance });
    };
}

export { fetchHosts };
