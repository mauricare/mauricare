export const careTypes = [
    { value: 'personal_care', label: 'Personal Care Assistance' },
    { value: 'nursing_care', label: 'Nursing Care' },
    { value: 'physiotherapy', label: 'Physiotherapy Session' },
    { value: 'post_hospital_recovery', label: 'Post-Hospital Recovery' },
    { value: 'respite_care', label: 'Respite Care' },
    { value: 'companionship', label: 'Companionship' },
    { value: 'wound_care', label: 'Wound Care' },
    { value: 'home_icu_support', label: 'Home ICU Support' },
    { value: 'other', label: 'Other Care' },
];

export const carerTypes = [
    { value: 'doctor', label: 'Doctor' },
    { value: 'nurse', label: 'Nurse' },
    { value: 'carers', label: 'Carer' },
    { value: 'physiotherapist', label: 'Physiotherapist' },
    { value: 'other', label: 'Other' },
];

export const statusLabels = {
    open: 'Open',
    assigned: 'Assigned',
    awaiting_payment: 'Awaiting Payment',
    paid: 'Paid',
    closed: 'Closed',
    cancelled: 'Cancelled',
};

export const bookingFilters = [
    { value: 'all', label: 'All' },
    { value: 'open', label: statusLabels.open },
    { value: 'assigned', label: statusLabels.assigned },
    { value: 'awaiting_payment', label: statusLabels.awaiting_payment },
    { value: 'paid', label: statusLabels.paid },
    { value: 'closed', label: statusLabels.closed },
    { value: 'cancelled', label: statusLabels.cancelled },
];

export const statusClasses = {
    open: 'bg-amber-50 text-amber-700',
    assigned: 'bg-teal-50 text-teal-700',
    awaiting_payment: 'bg-violet-50 text-violet-700',
    paid: 'bg-sky-50 text-sky-700',
    closed: 'bg-emerald-50 text-emerald-700',
    cancelled: 'bg-rose-50 text-rose-700',
};

export const editableStatuses = ['open', 'assigned'];

export const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'juice', label: 'Juice' },
];
