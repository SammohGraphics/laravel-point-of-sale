<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        return view('employees.index', [
            'employees' => Employee::filter(request(['search']))->sortable()->paginate($row)->appends(request()->query()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $this->validateEmployee($request);

        $validatedData['photo'] = $this->handleImageUpload($request);

        Employee::create($validatedData);

        return Redirect::route('employees.index')->with('success', 'Employee has been created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validatedData = $this->validateEmployee($request, $employee->id);

        if ($request->hasFile('photo')) {
            // Delete the existing photo
            if ($employee->photo) {
                Storage::delete('public/employees/' . $employee->photo);
            }
            $validatedData['photo'] = $this->handleImageUpload($request);
        }

        $employee->update($validatedData);

        return Redirect::route('employees.index')->with('success', 'Employee has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        if ($employee->photo) {
            Storage::delete('public/employees/' . $employee->photo);
        }

        $employee->delete();

        return Redirect::route('employees.index')->with('success', 'Employee has been deleted!');
    }

    /**
     * Validate employee data.
     */
    private function validateEmployee(Request $request, $employeeId = null)
    {
        return $request->validate([
            'photo' => 'image|file|max:1024',
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:employees,email' . ($employeeId ? ",$employeeId" : ''),
            'phone' => 'required|string|max:15|unique:employees,phone' . ($employeeId ? ",$employeeId" : ''),
            'experience' => 'nullable|string|max:6',
            'salary' => 'required|numeric',
            'vacation' => 'nullable|string|max:50',
            'city' => 'required|string|max:50',  // Corrected 'requried' to 'required'
            'address' => 'required|string|max:100',
        ]);
    }

    /**
     * Handle image upload.
     */
    private function handleImageUpload(Request $request)
    {
        $file = $request->file('photo');
        $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
        $path = 'public/employees/';

        $file->storeAs($path, $fileName);

        return $fileName;
    }
}
