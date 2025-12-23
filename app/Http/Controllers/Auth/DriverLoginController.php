<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DriverTenantAssignment;
use App\Services\DriverAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DriverLoginController extends Controller
{
    public function __construct(
        protected DriverAuthService $driverAuthService
    ) {
    }

    /**
     * Display the phone input form for drivers.
     */
    public function showPhoneForm()
    {
        $tenantOptions = session('tenantOptions', []);
        return view('auth.driver-login-phone', compact('tenantOptions'));
    }

    /**
     * Send a verification code by WhatsApp.
     */
    public function requestCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'tenant_id' => ['nullable', 'integer'],
        ]);

        $tenantId = $validated['tenant_id'] ?? null;
        // Pass raw phone to service - it will handle normalization (DDI, DDD, digit 9, etc.)
        $assignments = $this->driverAuthService->getAssignmentsByPhone($validated['phone']);

        if ($assignments->isEmpty()) {
            return back()
                ->withErrors(['phone' => 'Não encontramos um motorista com este telefone.'])
                ->withInput();
        }

        if ($assignments->count() > 1 && !$tenantId) {
            $tenantOptions = $assignments->map(fn ($assignment) => [
                'tenant_id' => $assignment->tenant->id,
                'tenant_name' => $assignment->tenant->name,
            ]);

            return back()
                ->withErrors(['tenant_id' => 'Selecione a empresa para continuar.'])
                ->withInput()
                ->with('tenantOptions', $tenantOptions);
        }

        $assignment = $tenantId
            ? $assignments->firstWhere('tenant_id', $tenantId)
            : $assignments->first();

        if (!$assignment) {
            return back()
                ->withErrors(['tenant_id' => 'A empresa selecionada não corresponde ao telefone informado.'])
                ->withInput()
                ->with('tenantOptions', $assignments->map(fn ($assignment) => [
                    'tenant_id' => $assignment->tenant->id,
                    'tenant_name' => $assignment->tenant->name,
                ]));
        }

        try {
            $this->driverAuthService->requestLoginCode(
                $validated['phone'],
                $request->header('X-Device-ID'),
                $assignment
            );

            $request->session()->put('driver_login_phone', $validated['phone']);
            $request->session()->put('driver_login_assignment_id', $assignment->id);
            $request->session()->put('driver_login_tenant_name', $assignment->tenant->name);
            $request->session()->forget('tenantOptions');

            return redirect()->route('driver.login.code')
                ->with('success', 'Verification code sent via WhatsApp. Check your messages.')
                ->with('code_sent', true)
                ->with('phone', $validated['phone']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Driver login code request failed', [
                'phone' => $validated['phone'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'phone' => 'Unable to send the verification code right now. Please double-check the number and try again.',
            ])->withInput();
        }
    }

    /**
     * Show the code verification form.
     */
    public function showCodeForm(Request $request)
    {
        $phone = $request->session()->get('driver_login_phone');

        if (!$phone) {
            return redirect()->route('driver.login.phone')
                ->withErrors(['phone' => 'Please re-enter your phone number before requesting the code again.']);
        }

        $tenantName = $request->session()->get('driver_login_tenant_name');

        return view('auth.driver-login-code', compact('phone', 'tenantName'));
    }

    /**
     * Validate the code and authenticate the driver.
     */
    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $assignmentId = $request->session()->get('driver_login_assignment_id');
            $assignment = $assignmentId
                ? DriverTenantAssignment::with(['driver', 'tenant', 'user'])->find($assignmentId)
                : null;

            if (!$assignment) {
                return redirect()->route('driver.login.phone')
                    ->withErrors(['phone' => 'Please request a new code for your phone number.']);
            }

            $driver = $this->driverAuthService->verifyLoginCode(
                $validated['phone'],
                $validated['code'],
                $request->header('X-Device-ID'),
                $assignment
            );

            if (!$assignment->user) {
                throw ValidationException::withMessages([
                    'code' => 'Driver access profile is not configured yet. Contact support.',
                ]);
            }

            auth()->login($assignment->user);
            $request->session()->forget('driver_login_phone');
            $request->session()->forget('driver_login_assignment_id');
            $request->session()->forget('driver_login_tenant_name');

            return redirect()->route('driver.dashboard')->with('success', 'Driver logged in successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Driver login code verification failed', [
                'phone' => $validated['phone'] ?? null,
                'assignment_id' => $assignment->id ?? null,
                'tenant_id' => $assignment->tenant_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'code' => 'Invalid or expired code. Request a new one to continue.',
            ])->withInput();
        }
    }
}

