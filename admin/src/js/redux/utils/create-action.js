export default function createAction(type, payload = {}, meta = null) {
    if (meta) {
        return { type, payload, meta };
    }

    return { type, payload };
}
