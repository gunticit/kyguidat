<!-- Skeleton Card for Loading State -->
<div class="skeleton-card block bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
    <!-- Image Skeleton -->
    <div class="aspect-video bg-gray-200 relative">
        <div class="w-full h-full bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 animate-shimmer"></div>
        <!-- Status Badge Skeleton -->
        <div class="absolute top-2 right-2 w-16 h-5 bg-gray-300 rounded-full"></div>
    </div>

    <!-- Content Skeleton -->
    <div class="p-4 space-y-3">
        <!-- Title Skeleton -->
        <div class="space-y-2">
            <div class="h-4 bg-gray-200 rounded w-full"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>

        <!-- Price Skeleton -->
        <div class="h-6 bg-gray-200 rounded w-2/3"></div>

        <!-- Details Skeleton -->
        <div class="flex items-center space-x-4">
            <div class="h-4 bg-gray-200 rounded w-16"></div>
            <div class="h-4 bg-gray-200 rounded w-20"></div>
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
        background: linear-gradient(90deg, #e5e7eb 0%, #f3f4f6 50%, #e5e7eb 100%);
        background-size: 200% 100%;
        animation: shimmer 1.5s ease-in-out infinite;
    }
</style>