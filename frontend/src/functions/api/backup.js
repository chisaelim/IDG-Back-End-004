export async function apiGetBackups() {
    return await axios.get(window.API_URL + '/backups');
}
export async function apiCreateBackup(flag) {
    return await axios.post(window.API_URL + '/backups/create', flag);
}
export async function apiDeleteBackup(filename) {
    return await axios.delete(window.API_URL + `/backups/delete/${filename}`);
}
export async function apiDownloadBackup(filename) {
    return await axios.get(window.API_URL + `/backups/download/${filename}`, {
        responseType: 'blob',
    });
}