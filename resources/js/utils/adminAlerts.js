import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const brandColor = '#117d73';

export const confirmAdminAction = async ({
    title,
    text,
    confirmText,
    icon = 'warning',
    destructive = false,
}) => {
    const result = await Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Keep unchanged',
        confirmButtonColor: destructive ? '#dc2626' : brandColor,
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-lg px-5 py-2.5 text-sm font-semibold',
            cancelButton: 'rounded-lg px-5 py-2.5 text-sm font-semibold',
        },
    });

    return result.isConfirmed;
};

export const showAdminSuccess = (title) => Swal.fire({
    title,
    icon: 'success',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    iconColor: brandColor,
});

export const showAdminError = (message) => Swal.fire({
    title: 'Action could not be completed',
    text: message,
    icon: 'error',
    confirmButtonText: 'Close',
    confirmButtonColor: brandColor,
    customClass: {
        popup: 'rounded-2xl',
        confirmButton: 'rounded-lg px-5 py-2.5 text-sm font-semibold',
    },
});
