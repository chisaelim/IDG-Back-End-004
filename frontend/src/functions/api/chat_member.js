export function apiGetMembers(chatId, params = {}) {
  return axios.get(window.API_URL + `/chats/${chatId}/members`, { params });
}
export function apiAddMember(chatId, memberData) {
  return axios.post(window.API_URL + `/chats/${chatId}/members/add`, memberData);
}
export function apiUpdateMember(chatId, memberId, updateData) {
  return axios.patch(window.API_URL + `/chats/${chatId}/members/update/${memberId}`, updateData);
}
export function apiRemoveMember(chatId, memberId) {
  return axios.delete(window.API_URL + `/chats/${chatId}/members/remove/${memberId}`);
}