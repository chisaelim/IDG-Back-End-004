//  Route::prefix('chats')->group(function () {
//         // Chat management
//         Route::get('/', [ChatController::class, 'getChats']);
//         Route::post('/create', [ChatController::class, 'createChat']);
//         Route::get('/read/{chatId}', [ChatController::class, 'readChat']);
//         Route::patch('update/{chatId}', [ChatController::class, 'updateGroupChat']);
//         Route::delete('delete/{chatId}', [ChatController::class, 'deleteGroupChat']);
//         Route::post('leave/{chatId}', [ChatController::class, 'leaveGroupChat']);

//         // Chat messages
//         Route::get('/{chatId}/messages', [ChatMessageController::class, 'getMessages']);
//         Route::post('/{chatId}/messages/create', [ChatMessageController::class, 'createMessage']);
//         Route::patch('/{chatId}/messages/update/{messageId}', [ChatMessageController::class, 'updateMessage']);
//         Route::delete('/{chatId}/messages/delete/{messageId}', [ChatMessageController::class, 'deleteMessage']);
//         Route::post('/{chatId}/messages/seen/{messageId}', [ChatMessageController::class, 'markMessageAsSeen']);
//         Route::post('/{chatId}/messages/seen-all', [ChatMessageController::class, 'markAllMessagesAsSeen']);

//         // Chat members
//         Route::get('/{chatId}/members', [ChatMemberController::class, 'getMembers']);
//         Route::post('/{chatId}/members/add', [ChatMemberController::class, 'addMember']);
//         Route::patch('/{chatId}/members/update/{memberId}', [ChatMemberController::class, 'updateMember']);
//         Route::delete('/{chatId}/members/remove/{memberId}', [ChatMemberController::class, 'removeMember']);
//     });
export function apiGetChats() {
  return axios.get(window.API_URL + '/api/chats/');
}
export function apiCreateChat(chatData) {
  return axios.post(window.API_URL + '/api/chats/create', chatData);
}
export function apiReadChat(chatId) {
  return axios.get(window.API_URL + `/api/chats/read/${chatId}`);
}
export function apiUpdateGroupChat(chatId, chatData) {
  return axios.patch(window.API_URL + `/api/chats/update/${chatId}`, chatData);
}
export function apiDeleteGroupChat(chatId) {
  return axios.delete(window.API_URL + `/api/chats/delete/${chatId}`);
}
export function apiLeaveGroupChat(chatId) {
  return axios.post(window.API_URL + `/api/chats/leave/${chatId}`);
}

export function apiGetMessages(chatId) {
  return axios.get(window.API_URL + `/api/chats/${chatId}/messages`);
}
export function apiCreateMessage(chatId, messageData) {
  return axios.post(window.API_URL + `/api/chats/${chatId}/messages/create`, messageData);
}   
export function apiUpdateMessage(chatId, messageId, messageData) {
  return axios.patch(window.API_URL + `/api/chats/${chatId}/messages/update/${messageId}`, messageData);
}
export function apiDeleteMessage(chatId, messageId) {
  return axios.delete(window.API_URL + `/api/chats/${chatId}/messages/delete/${messageId}`);
}

export function apiMarkMessageAsSeen(chatId, messageId) {
  return axios.post(window.API_URL + `/api/chats/${chatId}/messages/seen/${messageId}`);
}
export function apiMarkAllMessagesAsSeen(chatId) {
  return axios.post(window.API_URL + `/api/chats/${chatId}/messages/seen-all`);
}
export function apiGetMembers(chatId) {
  return axios.get(window.API_URL + `/api/chats/${chatId}/members`);
}
export function apiAddMember(chatId, memberData) {
  return axios.post(window.API_URL + `/api/chats/${chatId}/members/add`, memberData);
}
export function apiUpdateMember(chatId, memberId, updateData) {
  return axios.patch(window.API_URL + `/api/chats/${chatId}/members/update/${memberId}`, updateData);
}
export function apiRemoveMember(chatId, memberId) {
  return axios.delete(window.API_URL + `/api/chats/${chatId}/members/remove/${memberId}`);
}