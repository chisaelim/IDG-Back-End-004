export function apiGetChats(params = {}) {
  return axios.get(window.API_URL + '/chats', { params });
}
export function apiCreateChat(chatData) {
  return axios.post(window.API_URL + '/chats/create', chatData);
}
export function apiReadChat(chatId) {
  return axios.get(window.API_URL + `/chats/read/${chatId}`);
}
export function apiUpdateGroupChat(chatId, chatData) {
  return axios.patch(window.API_URL + `/chats/update/${chatId}`, chatData);
}
export function apiDeleteGroupChat(chatId) {
  return axios.delete(window.API_URL + `/chats/delete/${chatId}`);
}
export function apiLeaveGroupChat(chatId) {
  return axios.post(window.API_URL + `/chats/leave/${chatId}`);
}
export function apiGetChatFile(uri) {
  return axios.get(uri, {
    responseType: 'blob',
  });
}