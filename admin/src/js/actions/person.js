import apiRequest from '../utils/api-request';

export function fetchPeople({ page, offset = 0 }) {
    return apiRequest(`/api/people?page=${page}&offset=${offset}`);
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