<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();
        
        if (!user_can('customer.manage', $business)) {
            abort(403, 'You do not have permission to access customers.');
        }
        
        $query = Customer::query()
            ->forBusiness($business->id)
            ->select([
                'id',
                'business_id',
                'name',
                'email',
                'phone',
                'company_name',
                'tags',
                'total_requests_sent',
                'total_feedbacks',
                'opted_out',
                'created_at'
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('opted_out')) {
            $query->where('opted_out', $request->boolean('opted_out'));
        }

        $customers = $query->latest()->paginate(15);

        $stats = [
            'total' => Customer::forBusiness($business->id)->count(),
            'active' => Customer::forBusiness($business->id)->where('opted_out', false)->count(),
            'opted_out' => Customer::forBusiness($business->id)->where('opted_out', true)->count(),
        ];

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'opted_out']),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('customer.create', $business)) {
            abort(403, 'You do not have permission to create customers.');
        }

        return Inertia::render('Customers/Create');
    }

    public function store(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('customer.create', $business)) {
            return redirect()->back()->with("error", "You do not have permission to create customers.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,NULL,id,business_id,' . $business->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $customer = $business->customers()->create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $business = Auth::user()->getCurrentBusiness();

        if ($customer->business_id !== $business->id) {
            abort(403,  'You do not have permission to view this customer.');
        }

        if (!user_can('customer.view', $business)) {
            abort(403, 'You do not have permission to view customers.');
        }

        $customer->load([
            'feedback' => function($query) {
                $query->select(['id', 'customer_id', 'rating', 'comment', 'status', 'created_at'])
                      ->latest()
                      ->limit(10);
            },
            'reviewRequests' => function($query) {
                $query->select(['id', 'customer_id', 'status', 'subject', 'sent_at', 'opened_at', 'completed_at', 'expires_at'])
                      ->latest()
                      ->limit(10);
            }
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'recentFeedback' => $customer->feedback,
            'recentReviewRequests' => $customer->reviewRequests,
        ]);
    }

    public function edit(Customer $customer)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('customer.edit', $business)) {
            abort(403, 'You do not have permission to edit customers.');
        }

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('customer.edit', $business)) {
            return redirect()->back()->with("error", "You do not have permission to update customers.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id . ',id,business_id,' . $customer->business_id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'opted_out' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!user_can('customer.delete', $business)) {
            return redirect()->back()->with("error", "You do not have permission to delete customers.");
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
