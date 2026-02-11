export function apiGetUsers(params = {}) {
  return axios.get(window.API_URL + '/users', { params });
}

export function apiGetDetailUsers(params = {}) {
  return axios.get(window.API_URL + `/manage/users`, { params });
}

export function apiReadDetailUser(id) {
  return axios.get(window.API_URL + `/manage/users/read/${id}`);
}

export function apiCreateUser(data) {
  return axios.post(window.API_URL + `/manage/users/create`, data);
}

export function apiUpdateUser(id, data) {
  return axios.put(window.API_URL + `/manage/users/update/${id}`, data);
}

export function apiDeleteUser(id) {
  return axios.delete(window.API_URL + `/manage/users/delete/${id}`);
}