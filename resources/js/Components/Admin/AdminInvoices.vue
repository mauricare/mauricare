<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';

const careGivers = ref([]);
const invoices = ref([]);
const selectedInvoice = ref(null);
const isLoading = ref(true);
const isGenerating = ref(false);
const isSendingInvoice = ref(false);
const showSendConfirmation = ref(false);
const sendRecipientEmail = ref('');
const sendRecipientError = ref('');
const invoiceSendMessage = ref('');
const invoiceSendError = ref('');
const error = ref('');
const formErrors = ref({});
const estimate = ref(null);
const isLoadingEstimate = ref(false);
const estimateError = ref('');
let estimateRequest = 0;
const careGiverSearch = ref('');
const isCareGiverListOpen = ref(false);
const careGiverCombobox = ref(null);
const today = new Date();
const localDateValue = (value) => [
    value.getFullYear(),
    String(value.getMonth() + 1).padStart(2, '0'),
    String(value.getDate()).padStart(2, '0'),
].join('-');
const invoiceSearch = ref('');
const invoiceCareGiver = ref('');
const invoicePeriodStart = ref('');
const invoicePeriodEnd = ref('');
const form = reactive({
    care_giver_id: '',
    period_start: localDateValue(new Date(today.getFullYear(), today.getMonth(), 1)),
    period_end: localDateValue(today),
    rate: '10',
});

const money = (value) => new Intl.NumberFormat('en-MU', {
    style: 'currency',
    currency: 'MUR',
    minimumFractionDigits: 2,
}).format(Number(value || 0));

const date = (value) => value
    ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' }).format(new Date(`${value}T00:00:00`))
    : '';

const dateTime = (value) => value
    ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    : '';

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const canGenerate = computed(() =>
    form.care_giver_id && form.period_start && form.period_end && Number(form.rate) > 0,
);

const matchingCareGivers = computed(() => {
    const search = careGiverSearch.value.trim().toLocaleLowerCase();

    if (!search) return careGivers.value;

    return careGivers.value.filter((careGiver) =>
        careGiver.name.toLocaleLowerCase().includes(search)
        || careGiver.email.toLocaleLowerCase().includes(search),
    );
});

const selectCareGiver = (careGiver) => {
    form.care_giver_id = careGiver.id;
    careGiverSearch.value = `${careGiver.name} (${careGiver.email})`;
    isCareGiverListOpen.value = false;
};

const searchCareGivers = () => {
    form.care_giver_id = '';
    isCareGiverListOpen.value = true;
};

const closeCareGiverList = (event) => {
    if (!careGiverCombobox.value?.contains(event.target)) {
        isCareGiverListOpen.value = false;
    }
};

const filteredInvoices = computed(() => {
    const search = invoiceSearch.value.trim().toLocaleLowerCase();

    return invoices.value.filter((invoice) => {
        const matchesSearch = !search
            || invoice.invoice_number.toLocaleLowerCase().includes(search)
            || invoice.care_giver.name.toLocaleLowerCase().includes(search)
            || invoice.care_giver.email.toLocaleLowerCase().includes(search);
        const matchesCareGiver = !invoiceCareGiver.value
            || invoice.care_giver.id === Number(invoiceCareGiver.value);
        const matchesStart = !invoicePeriodStart.value
            || invoice.period_end >= invoicePeriodStart.value;
        const matchesEnd = !invoicePeriodEnd.value
            || invoice.period_start <= invoicePeriodEnd.value;

        return matchesSearch && matchesCareGiver && matchesStart && matchesEnd;
    });
});

const hasInvoiceFilters = computed(() =>
    invoiceSearch.value || invoiceCareGiver.value || invoicePeriodStart.value || invoicePeriodEnd.value,
);

const clearInvoiceFilters = () => {
    invoiceSearch.value = '';
    invoiceCareGiver.value = '';
    invoicePeriodStart.value = '';
    invoicePeriodEnd.value = '';
};

const loadEstimate = async () => {
    estimate.value = null;
    estimateError.value = '';

    if (!form.care_giver_id || !form.period_start || !form.period_end) return;
    if (form.period_end < form.period_start) {
        estimateError.value = 'The end date must be on or after the start date.';
        return;
    }

    const requestId = ++estimateRequest;
    isLoadingEstimate.value = true;

    try {
        const response = await axios.get('/api/admin/invoices/estimate', {
            params: {
                care_giver_id: form.care_giver_id,
                period_start: form.period_start,
                period_end: form.period_end,
            },
        });

        if (requestId === estimateRequest) estimate.value = response.data.data;
    } catch (requestError) {
        if (requestId === estimateRequest) {
            estimateError.value = requestError.response?.data?.message || 'The booking summary could not be loaded.';
        }
    } finally {
        if (requestId === estimateRequest) isLoadingEstimate.value = false;
    }
};

const loadData = async () => {
    isLoading.value = true;
    error.value = '';

    try {
        const [careGiversResponse, invoicesResponse] = await Promise.all([
            axios.get('/api/admin/invoices/care-givers'),
            axios.get('/api/admin/invoices'),
        ]);
        careGivers.value = careGiversResponse.data.data || [];
        invoices.value = invoicesResponse.data.data || [];
    } catch {
        error.value = 'Invoice data could not be loaded.';
    } finally {
        isLoading.value = false;
    }
};

const generateInvoice = async () => {
    if (!canGenerate.value || isGenerating.value) return;

    isGenerating.value = true;
    error.value = '';
    formErrors.value = {};

    try {
        const response = await axios.post('/api/admin/invoices', form);
        selectedInvoice.value = response.data.data;
        invoices.value.unshift(response.data.data);
    } catch (requestError) {
        formErrors.value = requestError.response?.data?.errors || {};
        error.value = requestError.response?.data?.message || 'The invoice could not be generated.';
    } finally {
        isGenerating.value = false;
    }
};

const viewInvoice = async (invoice) => {
    error.value = '';
    invoiceSendMessage.value = '';
    invoiceSendError.value = '';

    try {
        const response = await axios.get(`/api/admin/invoices/${invoice.id}`);
        selectedInvoice.value = response.data.data;
    } catch {
        error.value = 'The invoice preview could not be loaded.';
    }
};

const openSendConfirmation = () => {
    invoiceSendMessage.value = '';
    invoiceSendError.value = '';
    sendRecipientEmail.value = selectedInvoice.value?.care_giver.email || '';
    sendRecipientError.value = '';
    showSendConfirmation.value = true;
};

const closeSendConfirmation = () => {
    if (!isSendingInvoice.value) showSendConfirmation.value = false;
};

const sendInvoice = async () => {
    const invoice = selectedInvoice.value;

    if (!invoice || isSendingInvoice.value) return;

    isSendingInvoice.value = true;
    invoiceSendMessage.value = '';
    invoiceSendError.value = '';
    sendRecipientError.value = '';

    try {
        const response = await axios.post(`/api/admin/invoices/${invoice.id}/send`, {
            email: sendRecipientEmail.value.trim(),
        });
        invoiceSendMessage.value = response.data.message;
        Object.assign(invoice, response.data.data);
        const listedInvoice = invoices.value.find((item) => item.id === invoice.id);
        if (listedInvoice) Object.assign(listedInvoice, response.data.data);
        showSendConfirmation.value = false;
    } catch (requestError) {
        sendRecipientError.value = requestError.response?.data?.errors?.email?.[0] || '';
        invoiceSendError.value = requestError.response?.data?.message || 'The invoice email could not be sent.';
    } finally {
        isSendingInvoice.value = false;
    }
};

const invoiceMarkup = (invoice) => `
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>${escapeHtml(invoice.invoice_number)}</title>
<style>
body{font-family:Arial,sans-serif;color:#0f172a;margin:0;padding:40px} .page{max-width:900px;margin:auto}
h1{color:#117d73;margin:0} .top{display:flex;justify-content:space-between;border-bottom:3px solid #117d73;padding-bottom:24px}
.meta{text-align:right;line-height:1.6} table{width:100%;border-collapse:collapse;margin-top:32px}
th,td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left} th{background:#f1f5f9}
.number{text-align:right}.summary{margin:28px 0 0 auto;width:340px}.summary div{display:flex;justify-content:space-between;padding:8px}
.due{font-size:20px;font-weight:bold;border-top:2px solid #117d73;color:#117d73}
@media print{body{padding:0}.no-print{display:none}}
</style>
</head>
<body><div class="page">
<div class="top"><div><h1>MAURICARE</h1><p>Care giver commission invoice</p></div>
<div class="meta"><strong>${escapeHtml(invoice.invoice_number)}</strong><br>Generated ${date(invoice.created_at.slice(0, 10))}<br>
Period: ${date(invoice.period_start)} – ${date(invoice.period_end)}</div></div>
<h2>${escapeHtml(invoice.care_giver.name)}</h2><p>${escapeHtml(invoice.care_giver.email)}</p>
<table><thead><tr><th>Booking</th><th>Date</th><th>Care seeker</th><th>Care type</th><th class="number">Paid amount</th></tr></thead>
<tbody>${invoice.bookings.map((booking) => `<tr><td>#${booking.id}</td><td>${date(booking.scheduled_date)}</td><td>${escapeHtml(booking.care_seeker || '—')}</td><td>${escapeHtml(booking.care_type.replaceAll('_', ' '))}</td><td class="number">${money(booking.amount)}</td></tr>`).join('')}</tbody></table>
<div class="summary"><div><span>Closed booking total</span><strong>${money(invoice.booking_total)}</strong></div>
<div><span>Rate</span><strong>${Number(invoice.rate).toFixed(2)}%</strong></div>
<div class="due"><span>Total amount due</span><span>${money(invoice.amount_due)}</span></div></div>
</div></body></html>`;

const printInvoice = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        error.value = 'Please allow pop-ups to print or save the invoice as PDF.';
        return;
    }
    printWindow.document.write(invoiceMarkup(selectedInvoice.value));
    printWindow.document.close();
    printWindow.onload = () => {
        printWindow.focus();
        printWindow.print();
    };
};

const downloadInvoice = () => {
    const blob = new Blob([invoiceMarkup(selectedInvoice.value)], { type: 'text/html;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${selectedInvoice.value.invoice_number}.html`;
    link.click();
    URL.revokeObjectURL(url);
};

watch(
    () => [form.care_giver_id, form.period_start, form.period_end],
    loadEstimate,
);

onMounted(() => {
    loadData();
    document.addEventListener('click', closeCareGiverList);
});

onUnmounted(() => {
    document.removeEventListener('click', closeCareGiverList);
});
</script>

<template>
    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Generate an invoice</h2>
                <p class="mt-1 text-sm text-slate-500">Only closed bookings that have not previously been invoiced will be included.</p>
            </div>

            <form class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="generateInvoice">
                <label ref="careGiverCombobox" class="relative block text-sm font-semibold text-slate-700">
                    Care giver
                    <span class="relative mt-1 block">
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input
                            v-model="careGiverSearch"
                            type="search"
                            role="combobox"
                            autocomplete="off"
                            placeholder="Search by name or email"
                            class="block w-full rounded-md border-slate-300 py-2 pl-9 pr-9 text-sm font-normal focus:border-teal-500 focus:ring-teal-500"
                            :aria-expanded="isCareGiverListOpen"
                            aria-controls="care-giver-options"
                            required
                            @focus="isCareGiverListOpen = true"
                            @input="searchCareGivers"
                            @keydown.escape="isCareGiverListOpen = false"
                        />
                        <button
                            v-if="careGiverSearch"
                            type="button"
                            aria-label="Clear selected care giver"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-slate-400 hover:text-slate-700"
                            @click="careGiverSearch = ''; searchCareGivers()"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </span>
                    <span
                        v-if="isCareGiverListOpen"
                        id="care-giver-options"
                        class="absolute z-30 mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-xl"
                        role="listbox"
                    >
                        <button
                            v-for="careGiver in matchingCareGivers"
                            :key="careGiver.id"
                            type="button"
                            role="option"
                            class="block w-full px-3 py-2 text-left font-normal hover:bg-teal-50"
                            :class="{ 'bg-teal-50 text-teal-800': form.care_giver_id === careGiver.id }"
                            @click="selectCareGiver(careGiver)"
                        >
                            <span class="block font-semibold text-slate-900">{{ careGiver.name }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ careGiver.email }}</span>
                        </button>
                        <span v-if="!matchingCareGivers.length" class="block px-3 py-4 text-center text-sm font-normal text-slate-500">
                            No care giver found.
                        </span>
                    </span>
                    <span v-if="formErrors.care_giver_id" class="mt-1 block text-xs text-rose-600">{{ formErrors.care_giver_id[0] }}</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Start date
                    <input v-model="form.period_start" type="date" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" required />
                    <span v-if="formErrors.period_start" class="mt-1 block text-xs text-rose-600">{{ formErrors.period_start[0] }}</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    End date
                    <input v-model="form.period_end" type="date" :min="form.period_start" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" required />
                    <span v-if="formErrors.period_end" class="mt-1 block text-xs text-rose-600">{{ formErrors.period_end[0] }}</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Rate (%)
                    <input v-model="form.rate" type="number" min="0.01" max="100" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" required />
                    <span v-if="formErrors.rate" class="mt-1 block text-xs text-rose-600">{{ formErrors.rate[0] }}</span>
                </label>
                <div
                    v-if="isLoadingEstimate || estimate || estimateError"
                    class="md:col-span-2 xl:col-span-4"
                >
                    <div v-if="isLoadingEstimate" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>Checking eligible bookings…
                    </div>
                    <div v-else-if="estimate" class="grid gap-3 rounded-lg border border-teal-200 bg-teal-50 p-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Eligible closed bookings</p>
                            <p class="mt-1 text-2xl font-black text-slate-950">{{ estimate.bookings_count }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Combined paid amount</p>
                            <p class="mt-1 text-2xl font-black text-slate-950">{{ money(estimate.booking_total) }}</p>
                        </div>
                        <p v-if="!estimate.bookings_count" class="text-sm text-amber-700 sm:col-span-2">
                            There are no uninvoiced closed bookings for this selection.
                        </p>
                    </div>
                    <p v-else class="rounded-md bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ estimateError }}</p>
                </div>
                <div class="md:col-span-2 xl:col-span-4">
                    <button type="submit" :disabled="!canGenerate || isGenerating || estimate?.bookings_count === 0" class="rounded-md bg-[#117d73] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#0d665e] disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fa-solid fa-file-invoice mr-2"></i>{{ isGenerating ? 'Generating…' : 'Generate invoice' }}
                    </button>
                </div>
            </form>
            <p v-if="error" class="mt-4 rounded-md bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ error }}</p>
        </section>

        <section v-if="selectedInvoice" class="rounded-xl border border-teal-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Invoice preview</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-950">{{ selectedInvoice.invoice_number }}</h2>
                        <span v-if="selectedInvoice.sent_at" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                            <i class="fa-solid fa-circle-check"></i>
                            Sent {{ dateTime(selectedInvoice.sent_at) }}
                            <span v-if="selectedInvoice.sent_count > 1">({{ selectedInvoice.sent_count }} times)</span>
                        </span>
                        <span v-else class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Not sent</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="selectedInvoice = null">
                        <i class="fa-solid fa-xmark mr-2"></i>Close
                    </button>
                    <button type="button" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="downloadInvoice">
                        <i class="fa-solid fa-download mr-2"></i>Download
                    </button>
                    <button
                        type="button"
                        class="rounded-md border border-teal-600 px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isSendingInvoice"
                        @click="openSendConfirmation"
                    >
                        <i class="fa-solid mr-2" :class="isSendingInvoice ? 'fa-spinner fa-spin' : 'fa-envelope'"></i>
                        {{ isSendingInvoice ? 'Sending…' : selectedInvoice.sent_at ? 'Send again' : 'Send by email' }}
                    </button>
                    <button type="button" class="rounded-md bg-[#117d73] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0d665e]" @click="printInvoice">
                        <i class="fa-solid fa-print mr-2"></i>Print / Save PDF
                    </button>
                </div>
            </div>
            <p v-if="invoiceSendMessage" class="border-b border-emerald-200 bg-emerald-50 px-6 py-3 text-sm font-semibold text-emerald-700">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ invoiceSendMessage }}
            </p>
            <p v-if="invoiceSendError" class="border-b border-rose-200 bg-rose-50 px-6 py-3 text-sm font-semibold text-rose-700">
                {{ invoiceSendError }}
            </p>
            <div class="p-6 md:p-8">
                <div class="flex flex-col justify-between gap-4 border-b-2 border-teal-700 pb-6 sm:flex-row">
                    <div><h3 class="text-2xl font-black text-teal-700">MAURICARE</h3><p class="text-sm text-slate-500">Care giver commission invoice</p></div>
                    <div class="text-sm sm:text-right"><strong>{{ selectedInvoice.invoice_number }}</strong><p class="text-slate-500">{{ date(selectedInvoice.period_start) }} – {{ date(selectedInvoice.period_end) }}</p></div>
                </div>
                <div class="py-5"><h3 class="font-bold text-slate-950">{{ selectedInvoice.care_giver.name }}</h3><p class="text-sm text-slate-500">{{ selectedInvoice.care_giver.email }}</p></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-3 py-3">Booking</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Care seeker</th><th class="px-3 py-3">Care type</th><th class="px-3 py-3 text-right">Paid amount</th></tr></thead>
                        <tbody class="divide-y divide-slate-100"><tr v-for="booking in selectedInvoice.bookings" :key="booking.id"><td class="px-3 py-3 font-semibold">#{{ booking.id }}</td><td class="px-3 py-3">{{ date(booking.scheduled_date) }}</td><td class="px-3 py-3">{{ booking.care_seeker || '—' }}</td><td class="px-3 py-3 capitalize">{{ booking.care_type.replaceAll('_', ' ') }}</td><td class="px-3 py-3 text-right">{{ money(booking.amount) }}</td></tr></tbody>
                    </table>
                </div>
                <div class="ml-auto mt-6 max-w-sm space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Closed booking total</span><strong>{{ money(selectedInvoice.booking_total) }}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Rate</span><strong>{{ Number(selectedInvoice.rate).toFixed(2) }}%</strong></div>
                    <div class="flex justify-between border-t-2 border-teal-700 pt-3 text-lg font-bold text-teal-700"><span>Total amount due</span><span>{{ money(selectedInvoice.amount_due) }}</span></div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <h2 class="border-b border-slate-100 px-6 py-4 text-base font-bold text-slate-950">Generated invoices</h2>
            <p v-if="isLoading" class="px-6 py-10 text-center text-sm text-slate-500">Loading invoices…</p>
            <p v-else-if="!invoices.length" class="px-6 py-10 text-center text-sm text-slate-500">No invoices have been generated yet.</p>
            <div v-else>
                <div class="grid gap-3 border-b border-slate-100 bg-slate-50/60 p-4 md:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_minmax(180px,0.8fr)_160px_160px_auto]">
                    <label class="relative block">
                        <span class="sr-only">Search generated invoices</span>
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input v-model="invoiceSearch" type="search" placeholder="Invoice, name or email" class="block w-full rounded-md border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-teal-500 focus:ring-teal-500" />
                    </label>
                    <label>
                        <span class="sr-only">Filter by care giver</span>
                        <select v-model="invoiceCareGiver" class="block w-full rounded-md border-slate-300 py-2 text-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">All care givers</option>
                            <option v-for="careGiver in careGivers" :key="careGiver.id" :value="careGiver.id">{{ careGiver.name }}</option>
                        </select>
                    </label>
                    <label>
                        <span class="mb-1 block text-xs font-semibold text-slate-500 xl:hidden">Period from</span>
                        <input v-model="invoicePeriodStart" type="date" title="Period from" class="block w-full rounded-md border-slate-300 py-2 text-sm focus:border-teal-500 focus:ring-teal-500" />
                    </label>
                    <label>
                        <span class="mb-1 block text-xs font-semibold text-slate-500 xl:hidden">Period to</span>
                        <input v-model="invoicePeriodEnd" type="date" :min="invoicePeriodStart" title="Period to" class="block w-full rounded-md border-slate-300 py-2 text-sm focus:border-teal-500 focus:ring-teal-500" />
                    </label>
                    <button v-if="hasInvoiceFilters" type="button" class="rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900" @click="clearInvoiceFilters">
                        Clear
                    </button>
                </div>
                <p v-if="!filteredInvoices.length" class="px-6 py-10 text-center text-sm text-slate-500">No invoices match these filters.</p>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Invoice</th><th class="px-5 py-3">Care giver</th><th class="px-5 py-3">Period</th><th class="px-5 py-3">Bookings</th><th class="px-5 py-3">Email status</th><th class="px-5 py-3 text-right">Amount due</th><th class="px-5 py-3"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100"><tr v-for="invoice in filteredInvoices" :key="invoice.id"><td class="px-5 py-4 font-semibold">{{ invoice.invoice_number }}</td><td class="px-5 py-4">{{ invoice.care_giver.name }}</td><td class="px-5 py-4">{{ date(invoice.period_start) }} – {{ date(invoice.period_end) }}</td><td class="px-5 py-4">{{ invoice.bookings_count }}</td><td class="px-5 py-4"><span v-if="invoice.sent_at" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700"><i class="fa-solid fa-circle-check"></i>Sent</span><span v-else class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Not sent</span></td><td class="px-5 py-4 text-right font-semibold">{{ money(invoice.amount_due) }}</td><td class="px-5 py-4 text-right"><button type="button" class="font-semibold text-teal-700 hover:text-teal-900" @click="viewInvoice(invoice)">Preview</button></td></tr></tbody>
                    </table>
                </div>
            </div>
        </section>

        <div
            v-if="showSendConfirmation && selectedInvoice"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/70 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="send-invoice-title"
            @click.self="closeSendConfirmation"
        >
            <div class="flex max-h-[calc(100vh-2rem)] w-full max-w-xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Confirm email</p>
                        <h2 id="send-invoice-title" class="mt-1 text-xl font-bold text-slate-950">
                            {{ selectedInvoice.sent_at ? 'Send invoice again?' : 'Send invoice to care giver?' }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50"
                        aria-label="Close confirmation"
                        :disabled="isSendingInvoice"
                        @click="closeSendConfirmation"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-6">
                    <div class="rounded-lg border border-teal-200 bg-teal-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Recipient</p>
                        <p class="mt-1 font-bold text-slate-950">{{ selectedInvoice.care_giver.name }}</p>
                        <label class="mt-3 block">
                            <span class="mb-1 block text-xs font-semibold text-slate-600">Send to email address</span>
                            <input
                                v-model="sendRecipientEmail"
                                type="email"
                                autocomplete="email"
                                class="block w-full rounded-md border-slate-300 bg-white text-sm focus:border-teal-500 focus:ring-teal-500"
                                :class="{ 'border-rose-400': sendRecipientError }"
                                required
                                @input="sendRecipientError = ''"
                            />
                            <span v-if="sendRecipientError" class="mt-1 block text-xs font-semibold text-rose-600">{{ sendRecipientError }}</span>
                            <span class="mt-1 block text-xs font-normal text-slate-500">
                                This changes the recipient for this email only.
                            </span>
                        </label>
                    </div>

                    <p v-if="selectedInvoice.sent_at" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        This invoice was last sent {{ dateTime(selectedInvoice.sent_at) }}. You can send it again if needed.
                    </p>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Subject</p>
                        <p class="mt-1 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">
                            Mauricare invoice {{ selectedInvoice.invoice_number }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Message preview</p>
                        <div class="mt-1 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                            <p>Hello {{ selectedInvoice.care_giver.name }},</p>
                            <p class="mt-3">
                                Please find attached your Mauricare invoice
                                <strong>{{ selectedInvoice.invoice_number }}</strong>
                                for the period {{ date(selectedInvoice.period_start) }} to {{ date(selectedInvoice.period_end) }}.
                            </p>
                            <p class="mt-3">
                                Total amount due:
                                <strong>{{ money(selectedInvoice.amount_due) }}</strong>
                            </p>
                            <p class="mt-3">Kind regards,<br>Mauricare</p>
                        </div>
                    </div>

                    <p class="flex items-center gap-2 text-sm text-slate-500">
                        <i class="fa-solid fa-paperclip text-teal-700"></i>
                        {{ selectedInvoice.invoice_number }}.pdf will be attached.
                    </p>
                </div>

                <div class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-md border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white disabled:opacity-50"
                        :disabled="isSendingInvoice"
                        @click="closeSendConfirmation"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-[#117d73] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#0d665e] disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isSendingInvoice || !sendRecipientEmail.trim()"
                        @click="sendInvoice"
                    >
                        <i class="fa-solid mr-2" :class="isSendingInvoice ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                        {{ isSendingInvoice ? 'Sending…' : 'Confirm and send' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
