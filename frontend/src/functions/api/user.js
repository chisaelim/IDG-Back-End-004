export function apiGetUsers(params = {}) {
  return axios.get(window.API_URL + '/users', { params });
}