export const formatOption = (options, value) =>
    options.find((option) => option.value === value)?.label || value;

export const formatStatus = (status) =>
    status
        ? status.replace('_', ' ').replace(/^\w/, (character) => character.toUpperCase())
        : 'Pending';

export const formatDateParts = (dateValue) => {
    const date = new Date(`${dateValue}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return { day: '--', month: '---', full: dateValue };
    }

    return {
        day: new Intl.DateTimeFormat('en-GB', { day: '2-digit' }).format(date),
        month: new Intl.DateTimeFormat('en-GB', { month: 'short' }).format(date).toUpperCase(),
        full: new Intl.DateTimeFormat('en-GB', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(date),
    };
};

export const formatTime = (timeValue) => {
    if (!timeValue) {
        return '';
    }

    const [hours, minutes] = timeValue.split(':');
    const date = new Date();
    date.setHours(Number(hours), Number(minutes), 0, 0);

    return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
};

export const providerName = (booking) => booking.provider || 'Provider pending';
