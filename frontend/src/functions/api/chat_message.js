export function apiGetMessages(chatId, params = {}) {
  return axios.get(window.API_URL + `/chats/${chatId}/messages`, { params });
}
export function apiCreateMessage(chatId, messageData) {
  return axios.post(window.API_URL + `/chats/${chatId}/messages/create`, messageData);
}
export function apiUpdateMessage(chatId, messageId, messageData) {
  return axios.patch(window.API_URL + `/chats/${chatId}/messages/update/${messageId}`, messageData);
}
export function apiDeleteMessage(chatId, messageId) {
  return axios.delete(window.API_URL + `/chats/${chatId}/messages/delete/${messageId}`);
}
export function apiMarkMessageAsSeen(chatId, messageId) {
  return axios.post(window.API_URL + `/chats/${chatId}/messages/seen/${messageId}`);
}
export function apiMarkAllMessagesAsSeen(chatId) {
  return axios.post(window.API_URL + `/chats/${chatId}/messages/seen-all`);
}