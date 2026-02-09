import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('admin_user')) || null,
        token: localStorage.getItem('admin_token') || null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        userRole: (state) => {
            // Check user roles array from backend
            const roles = state.user?.roles || []
            if (roles.some(r => r.name === 'admin')) return 'admin'
            if (roles.some(r => r.name === 'moderator')) return 'moderator'
            if (roles.some(r => r.name === 'publisher')) return 'publisher'
            // Fallback to email check for legacy support
            if (state.user?.email === 'admin@khodat.com') return 'admin'
            if (state.user?.email?.includes('moderator')) return 'moderator'
            if (state.user?.email?.includes('publisher')) return 'publisher'
            return 'user'
        },
        isAdmin: (state) => {
            const roles = state.user?.roles || []
            return roles.some(r => r.name === 'admin') || state.user?.email === 'admin@khodat.com'
        },
        isModerator: (state) => {
            const roles = state.user?.roles || []
            return roles.some(r => r.name === 'moderator') || state.user?.email?.includes('moderator')
        },
        isPublisher: (state) => {
            const roles = state.user?.roles || []
            return roles.some(r => r.name === 'publisher') || state.user?.email?.includes('publisher')
        },
        isIT: (state) => {
            // IT account only has access to Settings
            return state.user?.email === 'it@khodat.com'
        },
        userId: (state) => state.user?.id

    },

    actions: {
        login(user, token) {
            this.user = user
            this.token = token
            localStorage.setItem('admin_user', JSON.stringify(user))
            localStorage.setItem('admin_token', token)
        },

        logout() {
            this.user = null
            this.token = null
            localStorage.removeItem('admin_user')
            localStorage.removeItem('admin_token')
        }
    }
})
