/* eslint-disable no-console */
import createAction from './create-action';

/**
 * Async action helper
 * @param {function} dispatch - redux store dispatch function
 * @param {Object} actionTypes - object containing the pending, success and error action constants
 * @param {Function} asyncAction - function to execute, return promise
 * @param {Object} payload - payload of the original action
 * @return {Promise} returns the promise chain, but handles catching inside
 */
function createAsyncAction(dispatch, actionTypes, asyncAction, payload = {}) {
    dispatch(createAction(actionTypes.pending, payload));

    return asyncAction()
        .catch((err) => {
            // This handles errors from running async action/function itself (ex. fetching data)
            dispatch(createAction(actionTypes.error, {}, {
                originalPayload: payload,
                error: err
            }));
            throw err;
        })
        .then(response => dispatch(createAction(actionTypes.success, response.body, {
            originalPayload: payload
        })))
        .catch((err) => dispatch(createAction(actionTypes.error, response.body, {
            originalPayload: payload
        })));
}

export default createAsyncAction;
