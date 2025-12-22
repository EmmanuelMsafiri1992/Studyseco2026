/**
 * StudySeco Chatbot Integration
 * Connects the landing page chatbot to the live support system
 */

class StudySecoChat {
    constructor(options = {}) {
        this.apiBase = options.apiBase || '/api/chatbot';
        this.sessionId = localStorage.getItem('studyseco_chat_session');
        this.isConnected = false;
        this.messageQueue = [];
        this.pollInterval = null;
        
        // Initialize chat if session exists
        if (this.sessionId) {
            this.reconnectToExistingChat();
        }
    }

    /**
     * Start a new chat session
     */
    async startChat(message, userInfo = {}) {
        try {
            const response = await fetch(`${this.apiBase}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    initial_message: message,
                    name: userInfo.name || null,
                    email: userInfo.email || null,
                    priority: userInfo.priority || 'normal',
                    user_agent: navigator.userAgent,
                    ip_address: await this.getClientIP(),
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.sessionId = data.session_id;
                localStorage.setItem('studyseco_chat_session', this.sessionId);
                this.isConnected = true;
                
                // Start polling for new messages
                this.startPolling();
                
                return {
                    success: true,
                    sessionId: this.sessionId,
                    queuePosition: data.queue_position,
                    message: 'Connected to StudySeco Support'
                };
            } else {
                throw new Error(data.message || 'Failed to start chat');
            }
        } catch (error) {
            console.error('Error starting chat:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Send a message in the current chat session
     */
    async sendMessage(message) {
        if (!this.sessionId) {
            return { success: false, error: 'No active chat session' };
        }

        try {
            const response = await fetch(`${this.apiBase}/chat/${this.sessionId}/message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            
            if (data.success) {
                return {
                    success: true,
                    message: data.message,
                    chatStatus: data.chat_status,
                    queuePosition: data.queue_position
                };
            } else {
                throw new Error(data.error || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Get all messages for the current chat session
     */
    async getMessages() {
        if (!this.sessionId) {
            return { success: false, error: 'No active chat session' };
        }

        try {
            const response = await fetch(`${this.apiBase}/chat/${this.sessionId}/messages`, {
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                return {
                    success: true,
                    chat: data.chat,
                    messages: data.messages
                };
            } else {
                throw new Error(data.error || 'Failed to get messages');
            }
        } catch (error) {
            console.error('Error getting messages:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Get chat status and queue position
     */
    async getStatus() {
        if (!this.sessionId) {
            return { success: false, error: 'No active chat session' };
        }

        try {
            const response = await fetch(`${this.apiBase}/chat/${this.sessionId}/status`, {
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                return {
                    success: true,
                    status: data.status,
                    queuePosition: data.queue_position,
                    agentName: data.agent_name,
                    estimatedWait: data.estimated_wait
                };
            } else {
                throw new Error(data.error || 'Failed to get status');
            }
        } catch (error) {
            console.error('Error getting status:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * End the current chat session
     */
    async endChat() {
        if (!this.sessionId) {
            return { success: false, error: 'No active chat session' };
        }

        try {
            const response = await fetch(`${this.apiBase}/chat/${this.sessionId}/end`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.cleanup();
                return {
                    success: true,
                    message: 'Chat ended successfully'
                };
            } else {
                throw new Error(data.error || 'Failed to end chat');
            }
        } catch (error) {
            console.error('Error ending chat:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Start polling for new messages
     */
    startPolling() {
        this.stopPolling(); // Clear any existing intervals
        
        this.pollInterval = setInterval(async () => {
            const result = await this.getMessages();
            if (result.success && this.onNewMessages) {
                this.onNewMessages(result.messages, result.chat);
            }
        }, 2000); // Poll every 2 seconds
    }

    /**
     * Stop polling for messages
     */
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }

    /**
     * Reconnect to existing chat session
     */
    async reconnectToExistingChat() {
        const result = await this.getMessages();
        if (result.success) {
            this.isConnected = true;
            this.startPolling();
            
            if (this.onReconnected) {
                this.onReconnected(result.messages, result.chat);
            }
        } else {
            // Session might be invalid, clear it
            this.cleanup();
        }
    }

    /**
     * Clean up chat session data
     */
    cleanup() {
        this.sessionId = null;
        this.isConnected = false;
        localStorage.removeItem('studyseco_chat_session');
        this.stopPolling();
    }

    /**
     * Get CSRF token from meta tag or cookie
     */
    getCSRFToken() {
        // Try to get from meta tag first
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Fallback to cookie
        const cookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='));
        
        return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
    }

    /**
     * Get client IP address (approximation)
     */
    async getClientIP() {
        try {
            const response = await fetch('https://api.ipify.org?format=json');
            const data = await response.json();
            return data.ip;
        } catch (error) {
            return 'unknown';
        }
    }

    /**
     * Event handlers
     */
    onNewMessages = null; // Set this to handle new messages
    onReconnected = null; // Set this to handle reconnection
    onStatusChange = null; // Set this to handle status changes
}

// Example usage for your chatbot
window.StudySecoChat = StudySecoChat;

// Example integration with existing chatbot
document.addEventListener('DOMContentLoaded', function() {
    const chatbot = new StudySecoChat();
    
    // Handle new messages from agents
    chatbot.onNewMessages = function(messages, chat) {
        messages.forEach(message => {
            if (message.sender_type === 'agent' || message.sender_type === 'system') {
                // Add agent message to your chatbot UI
                addMessageToChatbot(message.message, message.sender_type, message.sender_name);
            }
        });
        
        // Update chat status display
        updateChatStatus(chat.status, chat.queue_position, chat.agent_name);
    };
    
    // Handle reconnection to existing session
    chatbot.onReconnected = function(messages, chat) {
        console.log('Reconnected to existing chat session');
        // Restore chat history in your UI
        messages.forEach(message => {
            addMessageToChatbot(message.message, message.sender_type, message.sender_name);
        });
    };
    
    // Make chatbot globally available
    window.studySecoChat = chatbot;
});

// Helper functions (implement these based on your chatbot UI)
function addMessageToChatbot(message, senderType, senderName) {
    // Implement based on your chatbot's UI structure
    console.log(`${senderName}: ${message}`);
}

function updateChatStatus(status, queuePosition, agentName) {
    // Update your chatbot UI to show current status
    if (status === 'waiting') {
        console.log(`Queue position: #${queuePosition}`);
    } else if (status === 'active') {
        console.log(`Connected to agent: ${agentName}`);
    }
}