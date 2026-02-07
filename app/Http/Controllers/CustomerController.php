<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        $customers = Customer::where('store_id', $admin->store_id)
                             ->where('is_active', true)
                             ->get();
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $admin = Auth::user();

        $customer = Customer::create([
            'store_id' => $admin->store_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        return response()->json($customer, 201);
    }

    public function update(Request $request, $id)
    {
        $admin = Auth::user();
        $customer = Customer::where('id', $id)
                            ->where('store_id', $admin->store_id)
                            ->firstOrFail();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:customers,email,'.$customer->id,
            'phone' => 'sometimes|string|max:20',
        ]);

        $customer->update($request->only(['name', 'email', 'phone']));

        return response()->json($customer);
    }

    public function destroy($id)
    {
        $admin = Auth::user();
        $customer = Customer::where('id', $id)
                            ->where('store_id', $admin->store_id)
                            ->firstOrFail();

        $customer->update(['is_active' => false]);

        return response()->json(['message' => 'Customer deactivated successfully']);
    }
}
