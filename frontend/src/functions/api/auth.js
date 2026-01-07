export async function postSignUp(user) {
    return await axios.post(window.API_URL + '/signup', user);
}