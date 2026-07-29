<script setup>
import SectionEmptyState from '@/Components/Dashboard/SectionEmptyState.vue';
import { useUnreadMessages } from '@/composables/useUnreadMessages';
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    emptyTitle: {
        type: String,
        default: 'No conversations yet',
    },
    emptyMessage: {
        type: String,
        default: 'Once a care giver is assigned to one of your bookings, you can message them here.',
    },
    initialContactId: {
        type: Number,
        default: null,
    },
});

const page = usePage();
const userId = computed(() => page.props.auth.user.id);
const { refreshUnreadCount } = useUnreadMessages();

const contacts = ref([]);
const contactSearch = ref('');
const messages = ref([]);
const selectedContact = ref(null);
const isLoadingContacts = ref(true);
const isLoadingConversation = ref(false);
const loadError = ref(false);
const newMessage = ref('');
const isSending = ref(false);
const sendError = ref('');
const editingMessageId = ref(null);
const editingMessageBody = ref('');
const isUpdatingMessage = ref(false);
const deletingMessageId = ref(null);
const messageActionError = ref('');
const threadEl = ref(null);
let pollTimer = null;

const filteredContacts = computed(() => {
    const query = contactSearch.value.trim().toLocaleLowerCase();

    if (!query) {
        return contacts.value;
    }

    return contacts.value.filter((contact) =>
        contact.name.toLocaleLowerCase().includes(query),
    );
});

const scrollToBottom = async () => {
    await nextTick();
    if (threadEl.value) {
        threadEl.value.scrollTop = threadEl.value.scrollHeight;
    }
};

const loadContacts = async () => {
    loadError.value = false;

    try {
        const response = await axios.get('/api/messages/contacts');
        contacts.value = response.data.data || [];

        if (props.initialContactId) {
            const initialContact = contacts.value.find(
                (contact) => contact.id === props.initialContactId,
            );

            if (initialContact) {
                await selectContact(initialContact);
            }
        }
    } catch {
        loadError.value = true;
    } finally {
        isLoadingContacts.value = false;
    }
};

const loadConversation = async (silent = false) => {
    if (!selectedContact.value) {
        return;
    }

    if (!silent) {
        isLoadingConversation.value = true;
    }

    try {
        const response = await axios.get(`/api/messages/${selectedContact.value.id}`);
        const previousCount = messages.value.length;
        messages.value = response.data.data || [];

        if (!silent || messages.value.length !== previousCount) {
            await scrollToBottom();
        }

        refreshUnreadCount();
    } catch {
        if (!silent) {
            loadError.value = true;
        }
    } finally {
        isLoadingConversation.value = false;
    }
};

const selectContact = async (contact) => {
    selectedContact.value = contact;
    contact.unread_count = 0;
    messages.value = [];
    sendError.value = '';
    await loadConversation();
};

watch(
    () => props.initialContactId,
    async (contactId) => {
        if (!contactId || selectedContact.value?.id === contactId) {
            return;
        }

        const contact = contacts.value.find((item) => item.id === contactId);

        if (contact) {
            await selectContact(contact);
        }
    },
);

const backToContacts = () => {
    selectedContact.value = null;
    messages.value = [];
    editingMessageId.value = null;
    messageActionError.value = '';
};

const sendMessage = async () => {
    const body = newMessage.value.trim();

    if (!body || isSending.value) {
        return;
    }

    isSending.value = true;
    sendError.value = '';

    try {
        const response = await axios.post(`/api/messages/${selectedContact.value.id}`, { body });
        messages.value.push(response.data.data);
        newMessage.value = '';
        selectedContact.value.last_message = response.data.data;
        await scrollToBottom();
    } catch {
        sendError.value = "Your message couldn't be sent. Please try again.";
    } finally {
        isSending.value = false;
    }
};

const startEditingMessage = (message) => {
    editingMessageId.value = message.id;
    editingMessageBody.value = message.body;
    messageActionError.value = '';
};

const cancelEditingMessage = () => {
    editingMessageId.value = null;
    editingMessageBody.value = '';
};

const saveMessageEdit = async (message) => {
    const body = editingMessageBody.value.trim();

    if (!body || isUpdatingMessage.value) {
        messageActionError.value = 'A message cannot be empty.';
        return;
    }

    isUpdatingMessage.value = true;
    messageActionError.value = '';

    try {
        const response = await axios.patch(`/api/messages/${message.id}`, { body });
        Object.assign(message, response.data.data);

        if (selectedContact.value.last_message?.id === message.id) {
            selectedContact.value.last_message = { ...response.data.data };
        }

        cancelEditingMessage();
    } catch (error) {
        messageActionError.value = error.response?.status === 422
            ? 'A message is required and must not exceed 2,000 characters.'
            : 'Your message could not be updated. Please try again.';
    } finally {
        isUpdatingMessage.value = false;
    }
};

const deleteMessage = async (message) => {
    if (deletingMessageId.value || !window.confirm('Delete this message? This cannot be undone.')) {
        return;
    }

    deletingMessageId.value = message.id;
    messageActionError.value = '';

    try {
        await axios.delete(`/api/messages/${message.id}`);
        const deletedLastMessage = selectedContact.value.last_message?.id === message.id;
        messages.value = messages.value.filter((item) => item.id !== message.id);

        if (deletedLastMessage) {
            selectedContact.value.last_message = messages.value.length
                ? { ...messages.value[messages.value.length - 1] }
                : null;
        }

        if (editingMessageId.value === message.id) {
            cancelEditingMessage();
        }
    } catch {
        messageActionError.value = 'Your message could not be deleted. Please try again.';
    } finally {
        deletingMessageId.value = null;
    }
};

const formatMessageTime = (value) => {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
};

const previewText = (contact) => {
    if (!contact.last_message) {
        return 'Start the conversation';
    }

    const prefix = contact.last_message.sender_id === userId.value ? 'You: ' : '';

    return `${prefix}${contact.last_message.body}`;
};

onMounted(async () => {
    await loadContacts();
    pollTimer = setInterval(() => {
        loadConversation(true);
    }, 15000);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
});
</script>

<template>
    <div v-if="isLoadingContacts" class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center text-sm text-slate-500 shadow-sm">
        Loading conversations...
    </div>

    <div v-else-if="loadError && !contacts.length" class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
        <p class="text-sm text-slate-600">We couldn't load your messages.</p>
        <button
            type="button"
            class="mt-3 rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800"
            @click="loadContacts"
        >
            Try again
        </button>
    </div>

    <SectionEmptyState
        v-else-if="!contacts.length"
        icon="fa-message"
        :title="emptyTitle"
        :message="emptyMessage"
    />

    <section v-else class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="grid lg:grid-cols-[320px_minmax(0,1fr)]">
            <aside
                class="border-slate-100 lg:border-r"
                :class="{ 'hidden lg:block': selectedContact }"
            >
                <h2 class="border-b border-slate-100 px-5 py-4 text-base font-bold text-slate-950">Conversations</h2>
                <div class="border-b border-slate-100 px-4 py-3">
                    <label class="relative block">
                        <span class="sr-only">Search conversations by name</span>
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input
                            v-model="contactSearch"
                            type="search"
                            placeholder="Search by name"
                            class="block w-full rounded-md border-slate-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        />
                    </label>
                </div>
                <div class="max-h-[540px] overflow-y-auto">
                    <p
                        v-if="!filteredContacts.length"
                        class="px-5 py-8 text-center text-sm text-slate-500"
                    >
                        No conversations match “{{ contactSearch.trim() }}”.
                    </p>
                    <button
                        v-for="contact in filteredContacts"
                        :key="contact.id"
                        type="button"
                        class="flex w-full items-center gap-3 border-b border-slate-50 px-5 py-4 text-left transition hover:bg-slate-50"
                        :class="{ 'bg-teal-50/60': selectedContact?.id === contact.id }"
                        @click="selectContact(contact)"
                    >
                        <img
                            v-if="contact.avatar_url"
                            :src="contact.avatar_url"
                            :alt="`${contact.name} profile photo`"
                            class="h-11 w-11 shrink-0 rounded-full object-cover"
                        />
                        <span v-else class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">
                            {{ contact.name.charAt(0) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span class="block truncate text-sm font-bold text-slate-950">{{ contact.name }}</span>
                                <span
                                    v-if="contact.is_admin"
                                    class="shrink-0 rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-teal-800"
                                >
                                    Admin
                                </span>
                            </span>
                            <span class="mt-0.5 block truncate text-xs text-slate-500">{{ previewText(contact) }}</span>
                        </span>
                        <span
                            v-if="contact.unread_count"
                            class="flex h-6 min-w-6 shrink-0 items-center justify-center rounded-full bg-teal-700 px-1.5 text-xs font-bold text-white"
                        >
                            {{ contact.unread_count }}
                        </span>
                    </button>
                </div>
            </aside>

            <div
                class="flex min-h-[420px] flex-col lg:min-h-[540px]"
                :class="{ 'hidden lg:flex': !selectedContact }"
            >
                <template v-if="selectedContact">
                    <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-3">
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 lg:hidden"
                            aria-label="Back to conversations"
                            @click="backToContacts"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <img
                            v-if="selectedContact.avatar_url"
                            :src="selectedContact.avatar_url"
                            :alt="`${selectedContact.name} profile photo`"
                            class="h-10 w-10 rounded-full object-cover"
                        />
                        <span v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">
                            {{ selectedContact.name.charAt(0) }}
                        </span>
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-bold text-slate-950">{{ selectedContact.name }}</h3>
                            <p v-if="selectedContact.is_admin" class="text-xs font-semibold text-teal-700">Mauricare support</p>
                        </div>
                    </div>

                    <div ref="threadEl" class="flex-1 space-y-3 overflow-y-auto bg-slate-50/50 px-5 py-4">
                        <p v-if="isLoadingConversation" class="py-8 text-center text-sm text-slate-500">
                            Loading messages...
                        </p>
                        <p v-else-if="!messages.length" class="py-8 text-center text-sm text-slate-500">
                            No messages yet. Say hello!
                        </p>
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="flex"
                            :class="message.sender_id === userId ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="max-w-[85%] rounded-2xl px-4 py-2.5 sm:max-w-[70%]"
                                :class="message.sender_id === userId
                                    ? 'rounded-br-md bg-teal-700 text-white'
                                    : 'rounded-bl-md bg-white text-slate-800 shadow-sm'"
                            >
                                <template v-if="editingMessageId === message.id">
                                    <textarea
                                        v-model="editingMessageBody"
                                        rows="3"
                                        maxlength="2000"
                                        class="block min-w-56 w-full rounded-md border-teal-200 bg-white text-sm text-slate-900 shadow-sm focus:border-teal-400 focus:ring-teal-400"
                                        @keydown.enter.exact.prevent="saveMessageEdit(message)"
                                        @keydown.esc.prevent="cancelEditingMessage"
                                    ></textarea>
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded px-2 py-1 text-xs font-semibold text-teal-100 hover:bg-white/10"
                                            @click="cancelEditingMessage"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded bg-white px-2 py-1 text-xs font-semibold text-teal-800 disabled:opacity-60"
                                            :disabled="isUpdatingMessage || !editingMessageBody.trim()"
                                            @click="saveMessageEdit(message)"
                                        >
                                            {{ isUpdatingMessage ? 'Saving...' : 'Save' }}
                                        </button>
                                    </div>
                                </template>
                                <template v-else>
                                    <p class="whitespace-pre-line break-words text-sm">{{ message.body }}</p>
                                    <div class="mt-1 flex items-center justify-end gap-2">
                                        <div v-if="message.sender_id === userId" class="flex items-center gap-1">
                                            <button
                                                type="button"
                                                class="rounded p-1 text-teal-100 transition hover:bg-white/10 hover:text-white"
                                                aria-label="Edit message"
                                                title="Edit message"
                                                @click="startEditingMessage(message)"
                                            >
                                                <i class="fa-solid fa-pen text-[10px]"></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded p-1 text-teal-100 transition hover:bg-white/10 hover:text-white disabled:opacity-60"
                                                :disabled="deletingMessageId === message.id"
                                                aria-label="Delete message"
                                                title="Delete message"
                                                @click="deleteMessage(message)"
                                            >
                                                <i class="fa-solid fa-trash text-[10px]"></i>
                                            </button>
                                        </div>
                                        <span
                                            class="text-[11px]"
                                            :class="message.sender_id === userId ? 'text-teal-100' : 'text-slate-400'"
                                        >
                                            {{ formatMessageTime(message.created_at) }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <p v-if="messageActionError" class="border-t border-rose-100 bg-rose-50 px-5 py-2 text-sm font-medium text-rose-700">
                        {{ messageActionError }}
                    </p>

                    <p v-if="sendError" class="border-t border-rose-100 bg-rose-50 px-5 py-2 text-sm font-medium text-rose-700">
                        {{ sendError }}
                    </p>

                    <form class="flex items-end gap-3 border-t border-slate-100 px-4 py-3" @submit.prevent="sendMessage">
                        <textarea
                            v-model="newMessage"
                            rows="1"
                            maxlength="2000"
                            placeholder="Write a message..."
                            class="max-h-32 min-h-[44px] flex-1 resize-y rounded-md border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            @keydown.enter.exact.prevent="sendMessage"
                        ></textarea>
                        <button
                            type="submit"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-teal-700 text-white transition hover:bg-teal-800 disabled:opacity-60"
                            :disabled="isSending || !newMessage.trim()"
                            aria-label="Send message"
                        >
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </template>

                <div v-else class="flex flex-1 flex-col items-center justify-center px-6 py-12 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-teal-50 text-teal-700">
                        <i class="fa-regular fa-comments text-xl"></i>
                    </span>
                    <p class="mt-4 font-semibold text-slate-950">Select a conversation</p>
                    <p class="mt-1 text-sm text-slate-600">Choose someone on the left to see your messages.</p>
                </div>
            </div>
        </div>
    </section>
</template>
