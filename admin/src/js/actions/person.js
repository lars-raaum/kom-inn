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

export function fetchPerson({ id }) {
    if (id === undefined) {
        throw new Error("id passed to fetchPerson is undefined!");
    }
    return apiRequest(`/api/person/${id}`, {
        method: 'GET'
    })
}
