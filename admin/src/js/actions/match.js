import apiRequest from '../utils/api-request';

export function fetchMatches({ status }) {
    return apiRequest(`/api/matches?status=${status}`);
}

export function deleteMatch({ id }) {
    return apiRequest(`/api/match/${id}`, {
        method: 'DELETE'
    });
}

export function updateMatch({ id, data }) {
    return apiRequest(`/api/match/${id}`, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

export function nagHost({ id }) {
    return apiRequest(`/api/match/${id}/email/host_nag`, {
        method: 'POST'
    })
}