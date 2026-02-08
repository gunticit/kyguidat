<?php

namespace App\Services;

use App\Models\User;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class SupportService
{
    /**
     * Get list of support tickets
     */
    public function getList(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->supportTickets();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('subject', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('ticket_number', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create new support ticket
     */
    public function create(User $user, array $data): SupportTicket
    {
        $ticket = $user->supportTickets()->create([
            'ticket_number' => $this->generateTicketNumber(),
            'subject' => $data['subject'],
            'category' => $data['category'] ?? SupportTicket::CATEGORY_GENERAL,
            'priority' => $data['priority'] ?? SupportTicket::PRIORITY_MEDIUM,
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        // Create first message
        if (isset($data['message'])) {
            $ticket->messages()->create([
                'user_id' => $user->id,
                'message' => $data['message'],
                'attachments' => $data['attachments'] ?? [],
                'is_admin' => false,
            ]);
        }

        return $ticket->load('messages');
    }

    /**
     * Get support ticket by ID
     */
    public function getById(User $user, int $id): ?SupportTicket
    {
        return $user->supportTickets()->with(['messages.user:id,name,avatar'])->find($id);
    }

    /**
     * Update support ticket
     */
    public function update(User $user, int $id, array $data): ?SupportTicket
    {
        $ticket = $user->supportTickets()
            ->whereNotIn('status', [SupportTicket::STATUS_CLOSED])
            ->find($id);

        if (!$ticket) {
            return null;
        }

        $ticket->update([
            'subject' => $data['subject'] ?? $ticket->subject,
            'category' => $data['category'] ?? $ticket->category,
            'priority' => $data['priority'] ?? $ticket->priority,
        ]);

        return $ticket->fresh();
    }

    /**
     * Delete support ticket
     */
    public function delete(User $user, int $id): bool
    {
        $ticket = $user->supportTickets()
            ->whereIn('status', [SupportTicket::STATUS_OPEN])
            ->find($id);

        if (!$ticket) {
            return false;
        }

        return $ticket->delete();
    }

    /**
     * Add message to ticket
     */
    public function addMessage(User $user, int $id, array $data): ?SupportMessage
    {
        $ticket = $user->supportTickets()
            ->whereNotIn('status', [SupportTicket::STATUS_CLOSED])
            ->find($id);

        if (!$ticket) {
            return null;
        }

        $message = $ticket->messages()->create([
            'user_id' => $user->id,
            'message' => $data['message'],
            'attachments' => $data['attachments'] ?? [],
            'is_admin' => false,
        ]);

        // Update ticket status
        if ($ticket->status === SupportTicket::STATUS_WAITING_REPLY) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        return $message;
    }

    /**
     * Get messages for a ticket
     */
    public function getMessages(User $user, int $id): array
    {
        $ticket = $user->supportTickets()->find($id);

        if (!$ticket) {
            return [];
        }

        return $ticket->messages()
            ->with('user:id,name,avatar')
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Close support ticket
     */
    public function close(User $user, int $id): bool
    {
        $ticket = $user->supportTickets()
            ->whereNotIn('status', [SupportTicket::STATUS_CLOSED])
            ->find($id);

        if (!$ticket) {
            return false;
        }

        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return true;
    }

    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber(): string
    {
        $ticketNumber = 'TK' . date('Ymd') . strtoupper(Str::random(4));
        
        while (SupportTicket::where('ticket_number', $ticketNumber)->exists()) {
            $ticketNumber = 'TK' . date('Ymd') . strtoupper(Str::random(4));
        }

        return $ticketNumber;
    }
}
