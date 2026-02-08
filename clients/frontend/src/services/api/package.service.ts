import apiClient from './client';
import type {
    PostingPackage,
    UserPackage,
    CurrentPackage,
    PurchasePackageRequest,
    ApiResponse
} from '@/types';

export const packageService = {
    // Public endpoints
    getList: () =>
        apiClient.get<ApiResponse<PostingPackage[]>>('/posting-packages'),

    getById: (id: number) =>
        apiClient.get<ApiResponse<PostingPackage>>(`/posting-packages/${id}`),

    // Protected endpoints
    purchase: (data: PurchasePackageRequest) =>
        apiClient.post<ApiResponse<{ user_package: UserPackage; wallet_balance: number }>>('/posting-packages/purchase', data),

    getMyPackages: () =>
        apiClient.get<ApiResponse<{
            packages: UserPackage[];
            active_package: UserPackage | null;
            has_active_package: boolean;
        }>>('/my-packages'),

    getCurrentPackage: () =>
        apiClient.get<ApiResponse<CurrentPackage | null>>('/my-packages/current'),
};

export default packageService;
