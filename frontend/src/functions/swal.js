
export const LoadingModal = () => {
    Swal.fire({
        text: 'Loading...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        width: '200px',
    }).then(Swal.showLoading());
}
export const MessageModal = async (icon, title, text, callback) => {
    await Swal.fire({
        icon: icon,
        title: title,
        html: text,
        showConfirmButton: false,
    }).then(async () => {
        if (typeof callback === "function") {
            await callback();
        }
    })
}
export const CloseModal = () => {
    Swal.close();
}