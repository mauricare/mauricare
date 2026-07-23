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

export const bookingFilters = [
    { value: 'all', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

export const statusClasses = {
    pending: 'bg-amber-50 text-amber-700',
    confirmed: 'bg-teal-50 text-teal-700',
    cancelled: 'bg-rose-50 text-rose-700',
    completed: 'bg-emerald-50 text-emerald-700',
};

export const readOnlyStatuses = ['completed', 'cancelled'];
