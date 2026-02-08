frontend/src/
├── app/                          # Next.js App Router (pages)
│   ├── dashboard/                # Dashboard pages
│   ├── login/                    # Login page
│   ├── register/                 # Register page
│   ├── globals.css               # Global styles
│   ├── layout.tsx                # Root layout
│   └── page.tsx                  # Home page
│
├── components/                   # Shared components
│   ├── ui/                       # UI components
│   │   ├── Loading/              # Loading spinner
│   │   │   ├── Loading.tsx
│   │   │   ├── Loading.module.css
│   │   │   └── index.ts
│   │   └── Badge/                # Status badges
│   │       ├── Badge.tsx
│   │       ├── Badge.module.css
│   │       └── index.ts
│   └── index.ts                  # Export all components
│
├── constants/                    # Constants & Config
│   ├── config.ts                 # API URL, routes, storage keys
│   ├── status.ts                 # Status labels & colors
│   └── index.ts
│
├── hooks/                        # Custom React Hooks
│   ├── useAuth.tsx               # Authentication hook + context
│   ├── useDashboard.ts           # Dashboard data hook
│   ├── useLoading.ts             # Loading state hook
│   ├── useToast.ts               # Toast notification hook
│   └── index.ts
│
├── services/                     # API Services
│   ├── api/
│   │   ├── client.ts             # Axios client with interceptors
│   │   ├── auth.service.ts       # Auth APIs
│   │   ├── dashboard.service.ts  # Dashboard APIs
│   │   ├── consignment.service.ts
│   │   ├── payment.service.ts
│   │   ├── support.service.ts
│   │   ├── package.service.ts
│   │   └── user.service.ts
│   └── index.ts
│
├── types/                        # TypeScript Types
│   ├── api.ts                    # API response types
│   ├── auth.ts                   # User, Auth types
│   ├── consignment.ts            # Consignment types
│   ├── dashboard.ts              # Dashboard types
│   ├── package.ts                # Package types
│   ├── payment.ts                # Payment types
│   ├── support.ts                # Support types
│   └── index.ts                  # Export all types
│
├── utils/                        # Utility Functions
│   ├── format.ts                 # Number/currency formatters
│   ├── date.ts                   # Date/time helpers
│   ├── validation.ts             # Form validators
│   ├── storage.ts                # localStorage helpers
│   └── index.ts
│
└── lib/                          # Legacy (có thể xóa sau)
    └── api.ts


// Import types
import type { User, Consignment, ApiResponse } from '@/types';

// Import services
import { authService, dashboardService } from '@/services';

// Import hooks
import { useAuth, useDashboard, useLoading } from '@/hooks';

// Import components
import { Loading, Badge } from '@/components';

// Import utils
import { formatCurrency, formatTimeAgo, isValidEmail } from '@/utils';

// Import constants
import { ROUTES, CONSIGNMENT_STATUS } from '@/constants';