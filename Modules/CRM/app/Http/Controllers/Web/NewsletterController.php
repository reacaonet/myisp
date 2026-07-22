<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Client;

class NewsletterController extends Controller
{
    public function index()
    {
        return view('crm::newsletter.index');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:all,active,overdue',
        ]);

        $query = Client::query();

        switch ($validated['recipient_type']) {
            case 'active':
                $query->where('status', 'active');
                break;
            case 'overdue':
                $query->whereHas('invoices', function ($q) {
                    $q->where('status', 'pending')
                      ->where('due_date', '<', now()->subDays(10));
                });
                break;
        }

        $clients = $query->whereNotNull('email')->where('email', '!=', '')->get();

        $sent = 0;
        $errors = 0;

        foreach ($clients as $client) {
            try {
                \Mail::raw($validated['message'], function ($mail) use ($client, $validated) {
                    $mail->to($client->email)
                         ->subject($validated['subject']);
                });
                $sent++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        return back()->with('success', "Mala direta enviada: {$sent} emails enviados, {$errors} erros.");
    }
}
