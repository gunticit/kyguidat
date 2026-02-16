import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

// Views
import Dashboard from '@/views/Dashboard.vue'
import Login from '@/views/auth/Login.vue'
import ConsignmentList from '@/views/consignments/List.vue'
import ConsignmentDetail from '@/views/consignments/Detail.vue'
import UserList from '@/views/users/List.vue'
import TransactionList from '@/views/transactions/List.vue'
import Reports from '@/views/reports/Index.vue'
import Settings from '@/views/settings/Settings.vue'
import Support from '@/views/support/Support.vue'
import Customers from '@/views/customers/Customers.vue'
import ArticleList from '@/views/articles/List.vue'
import LocationList from '@/views/locations/List.vue'

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/consignments',
        name: 'consignments',
        component: ConsignmentList,
        meta: { requiresAuth: true }
    },
    {
        path: '/consignments/:id',
        name: 'consignment-detail',
        component: ConsignmentDetail,
        meta: { requiresAuth: true }
    },
    {
        path: '/users',
        name: 'users',
        component: UserList,
        meta: { requiresAuth: true }
    },
    {
        path: '/customers',
        name: 'customers',
        component: Customers,
        meta: { requiresAuth: true }
    },
    {
        path: '/transactions',
        name: 'transactions',
        component: TransactionList,
        meta: { requiresAuth: true }
    },
    {
        path: '/reports',
        name: 'reports',
        component: Reports,
        meta: { requiresAuth: true }
    },
    {
        path: '/support',
        name: 'support',
        component: Support,
        meta: { requiresAuth: true }
    },
    {
        path: '/settings',
        name: 'settings',
        component: Settings,
        meta: { requiresAuth: true, allowIT: true }
    },
    {
        path: '/articles',
        name: 'articles',
        component: ArticleList,
        meta: { requiresAuth: true }
    },
    {
        path: '/locations',
        name: 'locations',
        component: LocationList,
        meta: { requiresAuth: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login')
    } else if (to.meta.guest && authStore.isAuthenticated) {
        // IT account should redirect to /settings instead of /
        if (authStore.isIT) {
            next('/settings')
        } else {
            next('/')
        }
    } else if (authStore.isIT && !to.meta.allowIT && to.path !== '/login') {
        // IT account can only access /settings
        next('/settings')
    } else {
        next()
    }
})

export default router
