export async function postSignUp(user) {
    return await axios.post(window.API_URL + '/signup', user);
}
export async function postSignIn(user) {
    return await axios.post(window.API_URL + '/signin', user);
}
export async function postSignOut(token) {
    return await axios.post(window.API_URL + '/signout', null, {
        headers: {
            Authorization: `Bearer ${token}`
        }
    });
}