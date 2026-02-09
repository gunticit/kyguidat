import { defineStore } from 'pinia'
import { adminApi } from '@/services/api'

export const useConsignmentStore = defineStore('consignment', {
    state: () => ({
        consignments: [],
        currentConsignment: null,
        total: 0,
        loading: false,
        error: null
    }),

    actions: {
        async fetchConsignments(params = {}) {
            this.loading = true
            this.error = null
            try {
                const response = await adminApi.getConsignments(params)
                this.consignments = response.data.data || []
                this.total = response.data.meta?.total || 0
            } catch (error) {
                this.error = error.message
            } finally {
                this.loading = false
            }
        },

        async fetchConsignment(id) {
            this.loading = true
            this.error = null
            try {
                const response = await adminApi.getConsignment(id)
                this.currentConsignment = response.data.data
                return this.currentConsignment
            } catch (error) {
                this.error = error.message
                return null
            } finally {
                this.loading = false
            }
        },

        async createConsignment(data) {
            this.loading = true
            this.error = null
            try {
                const response = await adminApi.createConsignment(data)
                this.consignments.unshift(response.data.data)
                return response.data.data
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return null
            } finally {
                this.loading = false
            }
        },

        async updateConsignment(id, data) {
            this.loading = true
            this.error = null
            try {
                const response = await adminApi.updateConsignment(id, data)
                const index = this.consignments.findIndex(c => c.id === id)
                if (index !== -1) {
                    this.consignments[index] = response.data.data
                }
                this.currentConsignment = response.data.data
                return response.data.data
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return null
            } finally {
                this.loading = false
            }
        },

        async deleteConsignment(id) {
            this.loading = true
            this.error = null
            try {
                await adminApi.deleteConsignment(id)
                this.consignments = this.consignments.filter(c => c.id !== id)
                return true
            } catch (error) {
                this.error = error.response?.data?.message || error.message
                return false
            } finally {
                this.loading = false
            }
        },

        async approveConsignment(id) {
            try {
                await adminApi.approveConsignment(id)
                const item = this.consignments.find(c => c.id === id)
                if (item) item.status = 'approved'
                return true
            } catch (error) {
                this.error = error.message
                return false
            }
        },

        async rejectConsignment(id, reason = '') {
            try {
                await adminApi.rejectConsignment(id, reason)
                const item = this.consignments.find(c => c.id === id)
                if (item) {
                    item.status = 'rejected'
                    item.reject_reason = reason
                }
                return true
            } catch (error) {
                this.error = error.message
                return false
            }
        }
    }
})

