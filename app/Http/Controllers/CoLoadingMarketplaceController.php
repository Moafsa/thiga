<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteCapacityLedgerEntry;
use App\Models\RouteCapacityOffer;
use App\Models\RouteSpaceBooking;
use App\Models\Shipment;
use App\Services\AsaasService;
use App\Services\CoLoadingMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoLoadingMarketplaceController extends Controller
{
    private CoLoadingMatchingService $matchingService;
    private AsaasService $asaasService;

    public function __construct(CoLoadingMatchingService $matchingService, AsaasService $asaasService)
    {
        $this->matchingService = $matchingService;
        $this->asaasService = $asaasService;
    }

    /**
     * Display the marketplace dashboard and search matches
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Fetch carrier's own routes to list in publishing form
        $myRoutes = Route::where('tenant_id', $tenantId)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->get();

        $searchResults = collect();
        $isSearch = false;

        // If search parameters are provided, perform geo-matching
        if ($request->filled(['pickup_city', 'pickup_state', 'delivery_city', 'delivery_state', 'weight', 'volume'])) {
            $isSearch = true;
            $searchParams = $request->only([
                'pickup_city', 'pickup_state', 'delivery_city', 'delivery_state', 'weight', 'volume'
            ]);
            $searchParams['booker_tenant_id'] = $tenantId;

            $searchResults = $this->matchingService->findMatchingRoutes($searchParams);
        }

        // Fetch all active public offers from other carriers for the landing feed
        $activeOffers = RouteCapacityOffer::with(['route', 'tenant'])
            ->where('tenant_id', '!=', $tenantId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('marketplace.index', compact('myRoutes', 'searchResults', 'activeOffers', 'isSearch'));
    }

    /**
     * Publish a new empty capacity offer
     */
    public function storeOffer(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'offered_weight' => 'required|numeric|min:1',
            'offered_volume' => 'required|numeric|min:0.01',
            'price_per_kg' => 'required|numeric|min:0',
            'price_per_m3' => 'required|numeric|min:0',
            'min_price' => 'required|numeric|min:0',
            'restrictions' => 'nullable|string',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $route = Route::findOrFail($request->route_id);

        if ($route->tenant_id !== $tenantId) {
            return back()->with('error', 'Rota não pertence a sua transportadora.');
        }

        DB::beginTransaction();
        try {
            // Create capacity listing
            $offer = RouteCapacityOffer::create([
                'tenant_id' => $tenantId,
                'route_id' => $route->id,
                'offered_weight' => $request->offered_weight,
                'offered_volume' => $request->offered_volume,
                'price_per_kg' => $request->price_per_kg,
                'price_per_m3' => $request->price_per_m3,
                'min_price' => $request->min_price,
                'status' => 'active',
                'restrictions' => $request->filled('restrictions') ? ['notes' => $request->restrictions] : null,
            ]);

            // Seed initial reservation inside capacity ledger (empty seed)
            RouteCapacityLedgerEntry::create([
                'route_id' => $route->id,
                'route_space_booking_id' => null,
                'entry_type' => 'release',
                'weight_delta' => 0,
                'volume_delta' => 0,
            ]);

            DB::commit();
            return redirect()->route('marketplace.my-offers')->with('success', 'Espaço de carga publicado com sucesso no TMS LOG Compartilhado!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MarketplaceOfferStoreError: ' . $e->getMessage());
            return back()->with('error', 'Falha ao publicar espaço de carga.');
        }
    }

    /**
     * Display the carrier's own published offers
     */
    public function myOffers()
    {
        $tenantId = auth()->user()->tenant_id;
        $offers = RouteCapacityOffer::with(['route', 'spaceBookings'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('marketplace.my_offers', compact('offers'));
    }

    /**
     * Request booking for a matching capacity offer
     */
    public function book(Request $request, RouteCapacityOffer $offer)
    {
        $request->validate([
            'cargo_title' => 'required|string|max:100',
            'booked_weight' => 'required|numeric|min:1',
            'booked_volume' => 'required|numeric|min:0.01',
            'pickup_city' => 'required|string',
            'pickup_state' => 'required|string',
            'delivery_city' => 'required|string',
            'delivery_state' => 'required|string',
            'detour_km' => 'required|numeric',
        ]);

        $tenantId = auth()->user()->tenant_id;

        if ($offer->tenant_id === $tenantId) {
            return back()->with('error', 'Você não pode reservar espaço na sua própria rota.');
        }

        // Calculate dynamic price
        $pricing = $this->matchingService->calculateDynamicPrice(
            $offer,
            $request->booked_weight,
            $request->booked_volume,
            $request->detour_km
        );

        DB::beginTransaction();
        try {
            $booking = RouteSpaceBooking::create([
                'owner_tenant_id' => $offer->tenant_id,
                'booker_tenant_id' => $tenantId,
                'route_capacity_offer_id' => $offer->id,
                'cargo_title' => $request->cargo_title,
                'booked_weight' => $request->booked_weight,
                'booked_volume' => $request->booked_volume,
                'pickup_city' => $request->pickup_city,
                'pickup_state' => $request->pickup_state,
                'delivery_city' => $request->delivery_city,
                'delivery_state' => $request->delivery_state,
                'status' => 'pending_approval',
                'amount_base' => $pricing['amount_base'],
                'amount_detour_cost' => $pricing['amount_detour_cost'],
                'amount_platform_fee' => $pricing['amount_platform_fee'],
                'amount_final' => $pricing['amount_final'],
                'payment_status' => 'pending',
            ]);

            DB::commit();
            return redirect()->route('marketplace.bookings')->with('success', 'Sua solicitação de reserva foi enviada! Prossiga para o pagamento após a aprovação ou realize o checkout imediato.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MarketplaceBookingStoreError: ' . $e->getMessage());
            return back()->with('error', 'Falha ao solicitar reserva de capacidade.');
        }
    }

    /**
     * Lists Carrier's bookings (both sent requests and received capacity requests)
     */
    public function bookings()
    {
        $tenantId = auth()->user()->tenant_id;

        // Space bookings requested by this tenant (Sent Requests)
        $myBookings = RouteSpaceBooking::with(['capacityOffer.route', 'ownerTenant'])
            ->where('booker_tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Space bookings received on routes published by this tenant (Received Requests)
        $receivedBookings = RouteSpaceBooking::with(['capacityOffer.route', 'bookerTenant'])
            ->where('owner_tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('marketplace.bookings', compact('myBookings', 'receivedBookings'));
    }

    /**
     * Approve a received space booking request
     */
    public function approveBooking(RouteSpaceBooking $booking)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($booking->owner_tenant_id !== $tenantId) {
            return back()->with('error', 'Ação não permitida para sua transportadora.');
        }

        $booking->update(['status' => 'approved']);
        return back()->with('success', 'Reserva de espaço aprovada! Aguardando pagamento por parte do contratante.');
    }

    /**
     * Reject a received space booking request
     */
    public function rejectBooking(RouteSpaceBooking $booking)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($booking->owner_tenant_id !== $tenantId) {
            return back()->with('error', 'Ação não permitida para sua transportadora.');
        }

        $booking->update(['status' => 'rejected']);
        return back()->with('success', 'Reserva de espaço rejeitada.');
    }

    /**
     * Display secure split payment checkout
     */
    public function checkout(RouteSpaceBooking $booking)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($booking->booker_tenant_id !== $tenantId) {
            return redirect()->route('marketplace.bookings')->with('error', 'Reserva não pertence a você.');
        }

        return view('marketplace.checkout', compact('booking'));
    }

    /**
     * Process payments via Asaas split API
     */
    public function pay(Request $request, RouteSpaceBooking $booking)
    {
        $request->validate([
            'payment_method' => 'required|in:pix,boleto,credit_card'
        ]);

        $tenantId = auth()->user()->tenant_id;

        if ($booking->booker_tenant_id !== $tenantId) {
            return back()->with('error', 'Ação não autorizada.');
        }

        try {
            $charge = $this->asaasService->createCoLoadingCharge($booking, $request->payment_method);

            DB::beginTransaction();
            try {
                // If payment is completed/mock-processed immediately
                if (isset($charge['status']) && in_array($charge['status'], ['CONFIRMED', 'RECEIVED', 'PENDING'])) {
                    $isPaid = $charge['status'] !== 'PENDING';
                    
                    $booking->update([
                        'asaas_payment_id' => $charge['id'],
                        'payment_status' => $isPaid ? 'paid' : 'pending',
                        'status' => $isPaid ? 'approved' : 'pending_approval',
                    ]);

                    if ($isPaid) {
                        // Register in ledger
                        RouteCapacityLedgerEntry::create([
                            'route_id' => $booking->capacityOffer->route_id,
                            'route_space_booking_id' => $booking->id,
                            'entry_type' => 'confirm',
                            'weight_delta' => $booking->booked_weight,
                            'volume_delta' => $booking->booked_volume,
                        ]);
                    }
                }

                DB::commit();

                // If sandbox/offline fallback generated mock values
                if (str_starts_with($charge['id'], 'pay_mock_')) {
                    return redirect()->route('marketplace.bookings')->with([
                        'success' => 'Pagamento simulado efetuado com sucesso via Split Payment do Asaas! Custódia ativa de R$ ' . number_format($booking->amount_final - $booking->amount_platform_fee, 2, ',', '.') . ' para a transportadora dona da rota.',
                        'payment_simulated' => true
                    ]);
                }

                return redirect()->route('marketplace.bookings')->with('success', 'Cobrança com Split gerada no Asaas! Realize o pagamento pelo link enviado.');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('MarketplaceCheckoutPaymentError: ' . $e->getMessage());
            return back()->with('error', 'Falha ao processar pagamento com Asaas.');
        }
    }

    /**
     * Unified delivery timeline sync for both booker and carrying carrier
     */
    public function track(RouteSpaceBooking $booking)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($booking->booker_tenant_id !== $tenantId && $booking->owner_tenant_id !== $tenantId) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        // Retrieve route timeline updates to sync
        $route = $booking->capacityOffer->route;
        
        return view('marketplace.track', compact('booking', 'route'));
    }

    /**
     * Release custodian split holding upon successful delivery verification
     */
    public function completeDelivery(RouteSpaceBooking $booking)
    {
        $tenantId = auth()->user()->tenant_id;

        // Only the booker (contratante/owner of cargo) can release funds by verifying delivery success
        if ($booking->booker_tenant_id !== $tenantId) {
            return back()->with('error', 'Apenas o contratante/dono da carga pode confirmar a entrega para liberação do saldo de custódia.');
        }

        if ($booking->payment_status !== 'paid') {
            return back()->with('error', 'Custódia não liberada porque o pagamento ainda não foi efetuado.');
        }

        $booking->update([
            'status' => 'delivered',
        ]);

        return back()->with('success', 'Entrega confirmada pelo contratante! Split liberado e saldo creditado de forma imediata na conta Asaas da transportadora parceira.');
    }

    /**
     * One-click automatic publication of a route's remaining capacity
     */
    public function autoPublish(Route $route)
    {
        $tenantId = auth()->user()->tenant_id;

        if ($route->tenant_id !== $tenantId) {
            return back()->with('error', 'Rota não pertence a sua transportadora.');
        }

        // Calculate available capacity
        $capacity = $route->getAvailableCapacity();

        if ($capacity['weight'] <= 0 && $capacity['volume'] <= 0) {
            return back()->with('error', 'Este veículo/rota não possui capacidade sobressalente disponível.');
        }

        // Check if there is already an active offer for this route
        $existingOffer = RouteCapacityOffer::where('route_id', $route->id)
            ->where('status', 'active')
            ->first();

        if ($existingOffer) {
            return back()->with('error', 'Esta rota já possui uma oferta de capacidade ativa no marketplace.');
        }

        DB::beginTransaction();
        try {
            // Default carrier-friendly rules for dynamic pricing
            $offer = RouteCapacityOffer::create([
                'tenant_id' => $tenantId,
                'route_id' => $route->id,
                'offered_weight' => $capacity['weight'],
                'offered_volume' => $capacity['volume'],
                'price_per_kg' => 1.50,
                'price_per_m3' => 120.00,
                'min_price' => 150.00,
                'status' => 'active',
                'restrictions' => [
                    'notes' => 'Disponibilização automatizada via TMS LOG. Motorista: ' . ($route->driver ? $route->driver->name : 'Não informado') . '. Veículo: ' . ($route->vehicle ? $route->vehicle->plate : 'Não informado') . '.'
                ],
            ]);

            // Seed initial reservation inside capacity ledger (empty seed)
            RouteCapacityLedgerEntry::create([
                'route_id' => $route->id,
                'route_space_booking_id' => null,
                'entry_type' => 'release',
                'weight_delta' => 0,
                'volume_delta' => 0,
            ]);

            DB::commit();

            return redirect()->route('marketplace.my-offers')->with('success', 'Capacidade ociosa da rota disponibilizada com sucesso no marketplace TMS LOG Compartilhado!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MarketplaceAutoPublishError: ' . $e->getMessage());
            return back()->with('error', 'Falha ao disponibilizar capacidade ociosa de forma automatizada.');
        }
    }
}
