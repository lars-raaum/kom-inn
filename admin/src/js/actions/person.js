import apiRequest from '../utils/api-request';

export function fetchPeople({ page, offset = 0, limit = 150, status = null, region = null }) {
    let url = `/api/people?page=${page}&offset=${offset}&limit=${limit}`;

    if (region && region.length) {
        url += `&region=${region}`;
    }
    if (status) {
        url += `&status=${status}`;
    }

    return apiRequest(url);
}

export function deletePerson({ id }) {
    return apiRequest(`/api/person/${id}`, {
        method: 'DELETE'
    });
}

export function convertPerson({ id }) {
    return apiRequest(`/api/person/${id}/convert`, {
        method: 'POST'
    });
}

export function updatePerson({ id, data}) {
    return apiRequest(`/api/person/${id}`, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

export function fetchPerson({ id }) {
    return apiRequest(`/api/person/${id}`, {
        method: 'GET'
    })
}
