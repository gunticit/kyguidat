import { defineStore } from 'pinia'
import { adminApi } from '@/services/api'

export const useSupportStore = defineStore('support', {
    state: () => ({
        tickets: [],
        currentTicket: null,
        messages: [],
        counts: { open: 0, in_progress: 0, waiting_reply: 0, closed: 0, total: 0 },
        pagination: { current_page: 1, last_page: 1, total: 0 },
        loading: false,
        error: null,
        unreadCount: 0,
    }),

    actions: {
        async fetchTickets(params = {}) {
            this.loading = true
            this.error = null
            try {
                const response = await adminApi.getSupportTickets(params)
                this.tickets = response.data.data.data || []
                this.counts = response.data.counts || this.counts
                this.pagination = {
                    current_page: response.data.data.current_page,
                    last_page: response.data.data.last_page,
                    total: response.data.data.total,
                }
                return true
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return false
            } finally {
                this.loading = false
            }
        },

        async fetchTicket(id) {
            this.loading = true
            this.error = null
            try {
                const response = await adminApi.getSupportTicket(id)
                this.currentTicket = response.data.data
                this.messages = response.data.data.messages || []
                return response.data.data
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return null
            } finally {
                this.loading = false
            }
        },

        async sendReply(ticketId, message, attachments = []) {
            try {
                const response = await adminApi.replySupportTicket(ticketId, { message, attachments })
                if (response.data.data) {
                    this.messages.push(response.data.data)
                }
                return response.data.data
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return null
            }
        },

        async updateStatus(ticketId, status) {
            try {
                const response = await adminApi.updateTicketStatus(ticketId, { status })
                if (this.currentTicket && this.currentTicket.id === ticketId) {
                    this.currentTicket.status = status
                }
                // Update in list
                const idx = this.tickets.findIndex(t => t.id === ticketId)
                if (idx !== -1) {
                    this.tickets[idx].status = status
                }
                return response.data.data
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return null
            }
        },

        async closeTicket(ticketId) {
            try {
                await adminApi.closeSupportTicket(ticketId)
                if (this.currentTicket && this.currentTicket.id === ticketId) {
                    this.currentTicket.status = 'closed'
                }
                const idx = this.tickets.findIndex(t => t.id === ticketId)
                if (idx !== -1) {
                    this.tickets[idx].status = 'closed'
                }
                return true
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return false
            }
        },

        addSocketMessage(messageData) {
            if (this.currentTicket && this.currentTicket.id === messageData.ticketId) {
                // Avoid duplicates
                const exists = this.messages.find(m =>
                    m.tempId && m.tempId === messageData.tempId
                )
                if (!exists) {
                    this.messages.push({
                        id: Date.now(),
                        message: messageData.message,
                        user_id: messageData.userId,
                        is_admin: messageData.isAdmin,
                        created_at: messageData.timestamp,
                        user: { id: messageData.userId, name: messageData.userName },
                        attachments: messageData.attachments || [],
                    })
                }
            }
        },

        incrementUnread() {
            this.unreadCount++
        },

        resetUnread() {
            this.unreadCount = 0
        }
    }
})
