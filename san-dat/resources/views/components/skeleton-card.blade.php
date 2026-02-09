<!-- Skeleton Card for Loading State -->
<div class="skeleton-card block bg-navy-700 rounded-lg shadow-md overflow-hidden animate-pulse border border-navy-600">
    <!-- Image Skeleton -->
    <div class="aspect-video bg-navy-600 relative">
        <div class="w-full h-full bg-gradient-to-r from-navy-600 via-navy-500 to-navy-600 animate-shimmer"></div>
        <!-- Status Badge Skeleton -->
        <div class="absolute top-2 right-2 w-16 h-5 bg-navy-500 rounded-full"></div>
    </div>

    <!-- Content Skeleton -->
    <div class="p-4 space-y-3">
        <!-- Title Skeleton -->
        <div class="space-y-2">
            <div class="h-4 bg-navy-600 rounded w-full"></div>
            <div class="h-4 bg-navy-600 rounded w-3/4"></div>
        </div>

        <!-- Price Skeleton -->
        <div class="h-6 bg-navy-600 rounded w-2/3"></div>

        <!-- Details Skeleton -->
        <div class="flex items-center space-x-4">
            <div class="h-4 bg-navy-600 rounded w-16"></div>
            <div class="h-4 bg-navy-600 rounded w-20"></div>
        </div>
    </div>
</div>

<style>
    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    .animate-shimmer {
        background: linear-gradient(90deg, #1a2332 0%, #263248 50%, #1a2332 100%);
        background-size: 200% 100%;
        animation: shimmer 1.5s ease-in-out infinite;
    }
</style>