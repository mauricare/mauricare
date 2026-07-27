import { ref } from 'vue';

const unreadCount = ref(0);

const refreshUnreadCount = async () => {
    try {
        const response = await axios.get('/api/messages/unread-count');
        unreadCount.value = response.data.count || 0;
    } catch {
        // Keep the last known count when the request fails.
    }
};

export const useUnreadMessages = () => ({ unreadCount, refreshUnreadCount });
