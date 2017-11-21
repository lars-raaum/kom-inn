import apiRequest from '../utils/api-request';

export function fetchPeople({ page, offset = 0, limit = 150, status = null }) {
    if (status === null) {
        return apiRequest(`/api/people?page=${page}&offset=${offset}&limit=${limit}`);
    } else {
        return apiRequest(`/api/people?page=${page}&offset=${offset}&limit=${limit}&status=${status}`);
    }
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
