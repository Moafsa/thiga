<div class="bg-white rounded-lg shadow-md p-4 sticky top-4">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>

    <form method="GET" action="{{ route('fiscal.documents.index') }}" class="space-y-4">
        {{-- Document Type --}}
        <div>
            <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1">
                Document Type
            </label>
            <select name="document_type" id="document_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Types</option>
                <option value="cte" @selected(request('document_type') === 'cte')>CT-e</option>
                <option value="mdfe" @selected(request('document_type') === 'mdfe')>MDF-e</option>
            </select>
        </div>

        {{-- Status --}}
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                Status
            </label>
            <select name="status" id="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>
                    🟡 Pending
                </option>
                <option value="validating" @selected(request('status') === 'validating')>
                    🔵 Validating
                </option>
                <option value="processing" @selected(request('status') === 'processing')>
                    🟣 Processing
                </option>
                <option value="authorized" @selected(request('status') === 'authorized')>
                    🟢 Authorized
                </option>
                <option value="rejected" @selected(request('status') === 'rejected')>
                    🔴 Rejected
                </option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>
                    ⚪ Cancelled
                </option>
                <option value="error" @selected(request('status') === 'error')>
                    🔴 Error
                </option>
            </select>
        </div>

        {{-- Date From --}}
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                Date From
            </label>
            <input type="date" name="date_from" id="date_from"
                   value="{{ request('date_from') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        {{-- Date To --}}
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                Date To
            </label>
            <input type="date" name="date_to" id="date_to"
                   value="{{ request('date_to') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        {{-- Search --}}
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                Search
            </label>
            <input type="text" name="search" id="search" placeholder="Access key or MITT number"
                   value="{{ request('search') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Search by access key or MITT number</p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-2 pt-2">
            <button type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                Apply
            </button>
            <a href="{{ route('fiscal.documents.index') }}"
               class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium text-center">
                Reset
            </a>
        </div>
    </form>

    {{-- Quick Links --}}
    <div class="mt-6 pt-4 border-t border-gray-200">
        <p class="text-xs font-semibold text-gray-700 mb-2">Quick Links</p>
        <div class="space-y-2">
            <a href="{{ route('fiscal.documents.index', ['status' => 'pending']) }}"
               class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded transition">
                Pending Documents
            </a>
            <a href="{{ route('fiscal.documents.index', ['status' => 'authorized']) }}"
               class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded transition">
                Authorized Documents
            </a>
            <a href="{{ route('fiscal.documents.index', ['status' => 'error']) }}"
               class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded transition">
                Documents with Errors
            </a>
        </div>
    </div>
</div>
