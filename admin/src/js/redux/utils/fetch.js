const defaultOptions = {
    credentials: 'include',
    headers: {
        'Content-Type': 'application/json'
    }
}
export default function fetchHelper(url, options) {
    return fetch(url, Object.assign({}, defaultOptions, options))
        .then((response) => {
            if (!response.ok || response.status < 200 || response.status >= 400) {
                const error = new Error(response.statusText);
                error.response = response;
                throw error;
            }
            return response;
        })
        .then(response => response.json())
        .then(json => ({ body: json }));
}

